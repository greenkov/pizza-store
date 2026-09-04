# PizzaStore

A small pizza web store built on Laravel and Inertia/Vue. It covers the parts a real
storefront needs — a topping catalogue, a menu of presets, a session cart, checkout with
several payment methods, and an order lifecycle that advances in the background — plus an
AI agent that reads recent sales and turns them into featured items and menu suggestions.

It is a working skeleton rather than a finished product: the domain is deliberately narrow
so the moving parts stay readable.

## What it does

- **Toppings** — a fixed catalogue of 11 codes. Calories and prices come from
  `config/calories.php`.
- **Pizza presets** — the menu. Each preset is a name plus a list of topping codes, with a
  `hot` flag for the ones currently featured. Presets are soft-deleted so historical orders
  keep resolving.
- **Cart** — session-based. Customers order a preset as-is or build a custom pizza from the
  catalogue. A code may repeat within one pizza (double cheese is a real order), and repeats
  are counted, never collapsed.
- **Checkout** — several payment methods, each with its own required details and validation.
- **Order lifecycle** — `pending → paid → delivering → completed`, or `canceled`. Paid and
  delivering orders are advanced by scheduled commands that dispatch queued jobs.
- **Orders analysis agent** — an OpenAI-backed agent that reads aggregated sales for a
  period, updates which presets are featured, proposes new recipes, and stores a written
  report.

## Stack

| | |
| --- | --- |
| PHP | 8.3+ |
| Laravel | 13 |
| Inertia | 3 |
| Vue | 3.5 |
| Tailwind CSS | 4 |
| Vite | 8 |
| Database | SQLite by default |
| Tests | Pest |

## Running it locally

```bash
git clone <repository-url> pizza-store
cd pizza-store
composer setup
```

`composer setup` installs PHP and JS dependencies, copies `.env.example` to `.env`,
generates an app key, runs the migrations and builds the frontend.

To do it by hand instead:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

Then start everything with one command:

```bash
composer dev
```

That runs the web server, Vite, a log tailer and a queue listener together. The app is at
<http://localhost:8000>.

> The queue listener started by `composer dev` only handles the **default** queue. The order
> lifecycle uses named queues, so it needs the dedicated workers described below.

## Seeding the database

`DatabaseSeeder` is intentionally empty, so `php artisan db:seed` does nothing on its own.
Run the seeders individually, **in this order** — presets need toppings, and orders need
presets:

```bash
php artisan db:seed --class=ToppingSeeder       # the 11-topping catalogue
php artisan db:seed --class=PizzaPresetSeeder   # 20 random presets
php artisan db:seed --class=UsersSeeder         # test@mail.com / qwer1234
php artisan db:seed --class=OrdersSeeder        # 10 orders, 1-10 pizzas each
```

Optionally, attach images to any preset that has none. It reads from
`storage/test_images` and does nothing if that directory is empty:

```bash
php artisan db:seed --class=SeedTestPizzaImages
```

A few things worth knowing about the seeded data:

- `OrdersSeeder` gives each order a **random status**, so some are `pending` or `canceled`.
  Those two are excluded from every sales figure, which is why "10 orders seeded" and "8
  orders sold" is normal and correct.
- Orders are created with the current timestamp, so they all land inside the `week` window.
- Each seeded order also creates its own user, so the user count grows past what
  `UsersSeeder` adds.

To start over:

```bash
php artisan migrate:fresh
```

> `migrate:fresh` drops every table in whichever database `.env` points at. It does not
> touch the test database, which is always in-memory.

## Background processing

The order lifecycle does not advance on its own. Three processes need to be running.

### Scheduler

```bash
php artisan schedule:work
```

Runs the scheduled commands in the foreground. Two are registered in `routes/console.php`:

| Command | Frequency | What it does |
| --- | --- | --- |
| `order:process-paid` | every 2 minutes | finds paid orders and dispatches jobs to move them to delivering |
| `order:process-delivering` | every 5 minutes | finds delivering orders and dispatches jobs to complete them |

Both use `withoutOverlapping()` and `onOneServer()`, so a slow run will not stack up behind
itself.

Those commands only *dispatch* jobs — the workers below do the actual work.

### Queue workers

Each job class targets its own named queue, so a stuck batch of one kind cannot block the
other. Run each in its own terminal:

```bash
php artisan queue:work --queue={ProcessPaidOrderQueue}
php artisan queue:work --queue={ProcessDeliveringOrderQueue}
```

The braces are part of the queue name, not shell syntax. They survive unquoted in bash and
zsh because there is no comma or range inside, but quoting them is safer if you use another
shell:

```bash
php artisan queue:work --queue='{ProcessPaidOrderQueue}'
```

`--timeout=3600` gives a batch an hour before the worker kills it. The default 60 seconds is
not enough when a run picks up a large backlog.

Nothing moves through the lifecycle unless the scheduler **and** both workers are running.

## Orders analysis agent

Reads aggregated sales for a period and, in a single run:

1. loads the topping catalogue and the current menu,
2. loads pre-aggregated sales figures for the period,
3. replaces the set of featured (`hot`) presets with the best sellers,
4. proposes new pizzas in three categories — lighter recipes, indulgent ones, and whatever
   the order data suggests is trending,
5. stores a written report for a shop manager to read.

```bash
php artisan ai:orders-analysis          # defaults to week
php artisan ai:orders-analysis week     # today plus the 7 days before it
php artisan ai:orders-analysis month    # today plus the calendar month before it
```

Any other period is rejected. Cancelled and pending orders are excluded from every figure.

If nothing sold in the period, the agent leaves the current featured presets alone rather
than choosing on no evidence.

### Configuration

Add your API key to `.env` — it is not in `.env.example`:

```dotenv
OPENAI_API_KEY=sk-...
```

Optional overrides, with their defaults from `config/ai.php`:

```dotenv
OPENAI_API_URL=https://api.openai.com/v1/responses
OPENAI_DEFAULT_MODEL=gpt-5.6-luna
AGENT_DAILY_TOKEN_QUOTA=200000
```

Every run records its token usage in `agent_usages`, and a run is refused once the daily
quota is spent. Full request and response payloads are logged to
`storage/logs/ai.log`.

Reports are saved to `ai_orders_analyses` and are readable in the admin UI.

## Tests

```bash
php artisan test                 # everything
php artisan test --compact       # quieter output
php artisan test --filter=OrderFactory
```

Tests run against an in-memory SQLite database, configured in `phpunit.xml`. They never
touch your development data.

## Code style and static analysis

```bash
vendor/bin/pint --dirty     # format changed files
composer lint               # format everything
composer types:check        # PHPStan
composer test               # lint check + PHPStan + tests
```

Pint runs the `laravel` preset with a few overrides in `pint.json` — notably
`no_superfluous_phpdoc_tags` is disabled, because this project keeps full `@param`/`@return`
docblocks even where native types already say the same thing.
