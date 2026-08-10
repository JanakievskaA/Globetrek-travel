# GlobeTrek — travel booking platform

A working travel booking platform built with **Laravel 13 + Blade**, using the
GlobeTrek HTML template as its design system. The static template pages have
been replaced with database-driven views, real filtering, a booking flow and a
full admin panel.

Templates used, as specified:

| Section     | Template                              | Now lives at                        |
| ----------- | ------------------------------------- | ----------------------------------- |
| Home        | Homepage Template 5                   | `/`                                 |
| Destination | Destination Template 1                | `/destinations`                     |
| Tour list   | List Style – Sidebar Left             | `/tours`                            |
| Tour detail | Tour Detail Style 01                  | `/tours/{slug}`                     |

The tour list template was chosen because it is the only one that ships the
complete filter sidebar (search, tour type, date, people, price, duration,
review score, amenities) alongside sort and pagination — everything the brief
asks for, with no layout invented from scratch.

---

## Running it with Sail

[Laravel Sail](https://laravel.com/docs/sail) is the standard local
environment. It needs Composer on the host once, to fetch Sail itself:

```bash
git clone git@github.com:JanakievskaA/Globetrek-travel.git
cd Globetrek-travel

composer install
cp .env.example .env
php artisan key:generate

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link --relative
```

Then open <http://localhost:8123> — the port comes from `APP_PORT` in `.env`.
Log in with the [demo accounts](#demo-accounts) further down.

| Task | Command |
| ---- | ------- |
| Start / stop | `sail up -d` · `sail down` |
| Logs | `sail logs -f` |
| Tests | `sail artisan test` |
| Artisan / Composer | `sail artisan …` · `sail composer …` |
| A shell inside it | `sail shell` |
| Reset the data | `sail artisan migrate:fresh --seed` |

Add `alias sail='./vendor/bin/sail'` to your shell to drop the `./vendor/bin/`.

The container is named **`globetrek`** — `compose.yaml` pins both the project
and container name, because Compose otherwise derives them from whatever the
checkout directory happens to be called:

```
$ docker ps
NAMES        IMAGE                 PORTS
globetrek    globetrek/sail-8.4    0.0.0.0:8123->80/tcp
```

Sail bind-mounts the project, so the container reads the same
`database/database.sqlite` and the same files you edit — changes show up without
a rebuild. Two consequences worth knowing:

- `sail artisan migrate:fresh --seed` rewrites your real database file, not a
  throwaway copy.
- Use `--relative` on `storage:link`. The default link is absolute, so one made
  on the host resolves there but dangles at `/var/www/html` inside the
  container, and every uploaded image 403s.

### Why only one service

Sail's stack is usually app + MySQL + Redis, but nothing here would use them.
The database is SQLite, cache, queue and session all sit on that same database,
and both notifications are `via() => ['database']` — in-app only, no mail.
There is no `Redis::` or `Mail::` call in the codebase. Extra containers would
idle, so `sail:install --with=none` is the honest stack. Moving to MySQL would
be a real change, not a config flag, and worth doing only if the deployment
target needs it.

Sail is a local development environment: inside the container `php artisan serve`
does the serving, under supervisord. That is fine for development and for a
demo, but put a real web server in front of it before production.

---

## Installation without Docker

Requires **PHP 8.3+** and Composer. The database is SQLite, so there is no
database server to install and no credentials to configure.

**1. Clone the repository**

```bash
git clone git@github.com:JanakievskaA/Globetrek-travel.git
cd Globetrek-travel
```

**2. Install dependencies**

```bash
composer install
```

**3. Set up the environment**

```bash
cp .env.example .env
php artisan key:generate
```

No database credentials to edit — `.env.example` ships with
`DB_CONNECTION=sqlite`, which needs no host, port, user or password.

**4. Create the database file**

```bash
touch database/database.sqlite
```

**5. Run migrations and seed**

```bash
php artisan migrate --seed
```

This creates 35 tours across 18 destinations, with categories, reviews and a
booking history, plus the demo accounts below.

**6. Link storage**

```bash
php artisan storage:link --relative
```

Not optional. Images uploaded through the content editor are written to the
`public` disk and served from `storage/uploads/…`, so without this symlink
every uploaded image fails to load (a 403 under `artisan serve`). The bundled
theme photography keeps working either way, which makes the breakage look
intermittent rather than obvious.

`--relative` rather than a bare `storage:link`, so the same checkout also works
when a container mounts it at a different path.

**7. Start the server**

```bash
php artisan serve --port=8123
```

Then open <http://localhost:8123>.

The port is given explicitly because `php artisan serve` would otherwise
default to 8000, which is where the original static template was served from —
worth keeping the two apart. Any free port works; `--port` and `APP_URL` in
`.env` just need to agree.

### Demo accounts

Seeded by every install path — Sail, Docker or local. Password is `password`
for all three:

| Role     | Email                     | Lands on            |
| -------- | ------------------------- | ------------------- |
| Admin    | `admin@globetrek.test`    | `/admin`            |
| Manager  | `manager@globetrek.test`  | `/admin`            |
| Customer | `customer@globetrek.test` | `/account/bookings` |

There is no front-end build step: the site's CSS and JavaScript are committed
under `public/assets/`, so `npm install` is not needed. The only file that
references Vite is Laravel's default `welcome.blade.php`, which this app does
not route.

To reset the data later, `php artisan migrate:fresh --seed` rebuilds from
scratch — it drops every table, so anything you created while clicking around
goes with it.

### Photography

Every placeholder in the original template was a 580×386, ~9 KB stock image.
They are replaced by 52 verified Unsplash photographs (1600 px and 800 px
variants) matched to their destination. Re-fetch them with:

```bash
python3 tools/fetch_photos.py          # --force to redownload
```

The catalogue lives in that script, so the image set is reproducible rather
than a pile of binaries with unknown provenance.

---

## What is in the box

### Public site

- **Home** — hero slider driven by featured destinations, live catalogue stats,
  tour-type carousel, category-tabbed best sellers, destination spotlight,
  trending carousel and testimonials sourced from real approved reviews.
- **Tour list** — nine filters that all genuinely filter, seven sort orders,
  list/grid toggle, removable filter chips and pagination. Filters submit on
  change; the search box debounces.
- **Tour detail** — lightbox gallery, key facts, overview, highlights,
  includes/excludes, accordion itinerary, map, FAQs, rating breakdown, paginated
  reviews, review form, and a booking widget with a live total.
- **Booking** — checkout, server-side pricing, confirmation page with reference.
- Wishlist (localStorage + cookie mirror), destinations index and detail,
  about, contact, auth, and a customer booking list.

### Admin panel (`/admin`)

Dashboard with revenue chart, KPI tiles, recent bookings and a moderation
queue, plus full CRUD for **tours, destinations, categories, bookings, users
and reviews**. Every resource has a searchable, filterable, paginated table
with row actions; bookings and reviews additionally support inline status
changes.

### Content editor (`/admin/pages`)

The homepage, about and contact pages are editable without touching Blade.
Every section of those three pages is described as data in
`app/Support/PageSections.php` — that one registry drives the admin form, the
validation rules and the defaults the front end falls back to, so adding an
editable field is a change in one file plus the Blade line that reads it.
Sections support plain fields, image pickers, repeaters (e.g. the hero slides)
and a destination picker; each one can also be hidden, which removes it from
the live page. A section that has never been edited renders its registry
default, so the site is never blank.

Images come from a **media library** (`app/Support/MediaLibrary.php`) that lists
both uploads and the existing theme photography, with uploads validated as
images and restricted to staff.

### Notifications

In-app notifications via a bell in the header. A new booking notifies every
admin and manager (suspended staff are skipped); confirming a booking notifies
the customer exactly once — re-saving an already-confirmed booking does not
notify again, and seeding historical data does not ring the bell. Guest
bookings confirm without error, and one user cannot open another's
notification.

### Roles

Three roles, enforced by `EnsureUserIsStaff` and `EnsureUserIsAdmin`.
**Managers** reach the panel, manage the catalogue and upload images, but
cannot touch staff accounts, promote themselves, or edit page content.
**Admins** additionally manage staff and the content editor.

---

## Architecture

```
app/
  Enums/            TourStatus, BookingStatus, ReviewStatus, UserRole,
                    DurationBucket, TourSort — each knows its own label,
                    badge colour and, where relevant, how to constrain a query
  Support/
    TourFilters     immutable value object built from the request; applies
                    every filter, renders its own chips, and rebuilds its
                    query string minus any one filter
    PageSections    the registry of editable page sections — drives the admin
                    form, the validation and the front-end defaults
    MediaLibrary    lists uploads alongside the bundled theme photography
  Services/
    BookingPricer   the single source of truth for what a booking costs
  Models/           thin models with scopes + cached aggregates
                    (incl. PageSection, Media, TourImage, TourItinerary)
  Notifications/    NewBookingReceived (to staff), BookingConfirmed (to the
                    customer)
  Http/
    Controllers/    controllers stay thin; Admin/* mirror the public ones
    Requests/       validation, including TourRequest::payload() which turns
                    the newline list editors back into JSON columns
    Middleware/     EnsureUserIsStaff, EnsureUserIsAdmin
  View/Composers/   NavigationComposer feeds the header and footer

resources/views/
  components/
    layouts/        app (public) and admin shells
    layout/         header, footer, search modal
    ui/             rating, price, badge, toast, empty-state, section/page title
    tour/           card (grid|list|wide), filters, gallery, itinerary, faqs,
                    reviews, review-form, booking-widget
    destination/    card
    home/           hero, search-form, categories, benefits, spotlight,
                    tour-tabs, video-banner, trending, destinations,
                    testimonials, stats-bar
    admin/          sidebar, topbar, page-header, stat-card, data-table,
                    field, row-actions
  pages/            public pages
  admin/            admin screens
```

### Notes on a few decisions

- **Prices.** `BookingPricer` computes every total server-side; the JavaScript
  in the booking widget only mirrors it for instant feedback. Extras are
  matched against the tour's own list, so a tampered request cannot invent a
  discount or a free upgrade — both cases are covered by tests.
- **Ratings.** `tours.rating_avg` / `reviews_count` are cached columns kept in
  step by a model hook whenever a review is saved or deleted, so sorting and
  filtering by rating stay index-friendly. Only approved reviews count.
- **Rating filter bands.** Averages rarely land on a clean 5.0, so the sidebar
  offers 4.5+/4+/3.5+/3+ rather than star counts that would return nothing.
- **Price filter.** Always evaluated against `COALESCE(sale_price, price)`, so
  a discounted tour appears in the bracket it actually sells in.
- **Front-end JS.** The template's `main.js` was dropped: 71 KB of demo code
  with hard-coded tour data and an unguarded `#loginForm` handler that throws
  on every page without that form. `public/assets/js/globetrek.js` replaces it
  with roughly 430 lines of dependency-free ES2020. jQuery went with it.
- **Resilience.** `animate.css` sets `.wow { visibility: hidden }`, which would
  leave the page blank if the animation library failed to load; both the
  preloader and the reveal now have CSS and JS fallbacks, and the whole site
  honours `prefers-reduced-motion`.

---

## Tests

```bash
php artisan test          # 92 tests, 277 assertions
./vendor/bin/pint         # code style
```

Eight feature suites, aimed at behaviour that would be embarrassing to get
wrong:

| Suite                     | Covers                                                    |
| ------------------------- | --------------------------------------------------------- |
| `TourCatalogueTest`       | each filter narrowing results, sale-price brackets, sorts  |
| `BookingFlowTest`         | server-side totals, unknown extras, capacity limits        |
| `BookingNotificationTest` | who gets notified, once, and who cannot read whose         |
| `ReviewTest`              | moderation gating publication, the cached rating           |
| `AdminPanelTest`          | CRUD, authorisation, referential guards on deletes         |
| `StaffPermissionsTest`    | manager vs admin boundaries, no self-promotion             |
| `HomeContentTest`         | section defaults, hidden sections, uploads, media access   |
| `PageContentTest`         | the about and contact pages render and edit                |
