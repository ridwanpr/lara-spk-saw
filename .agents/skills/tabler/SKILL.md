---
name: tabler
description: "Build admin dashboards and web application UIs with Tabler, the open source UI kit built on Bootstrap 5. Use when a project uses Tabler or @tabler/core, or when asked for a dashboard, admin panel, settings page, data table, or form styled with Tabler. Covers installation, Laravel integration, page layouts, components, theming, and rules to keep markup consistent."
license: MIT
metadata:
  author: tabler
---

# Tabler Skill

Tabler is a free, MIT-licensed UI kit for admin dashboards and web applications built on Bootstrap 5. It ships compiled CSS and JavaScript in the `@tabler/core` package, and every Bootstrap 5 class, utility, and JavaScript behavior works underneath it.

---

## Local Documentation & References

This skill includes full offline documentation in the `references/` directory:
- [llms.txt](file:///.agents/skills/tabler/references/llms.txt): Index of all Tabler documentation pages and exhaustive list of component class names.
- [llms-full.txt](file:///.agents/skills/tabler/references/llms-full.txt): Complete Tabler documentation (~1.2MB) with prose, guides, and plain HTML markup for every component.
- [laravel.md](file:///.agents/skills/tabler/references/laravel.md): Official Tabler guide for Laravel and Vite integration.

When working on Tabler components, consult `references/llms.txt` and `references/llms-full.txt` instead of guessing class names.

---

## Key Principle: Do Not Guess Class Names

- Tabler builds on Bootstrap 5, but provides custom classes and component patterns.
- Only use classes documented in Tabler or Bootstrap 5. **Do not invent `tabler-*` classes.**
- Reach for Tabler components before writing custom CSS.
- Spacing, colors, and typography use Bootstrap utilities (`mb-3`, `text-secondary`, `fw-bold`, `g-2`, etc.).

---

## Installation & Setup

### 1. In Laravel with Vite

Install the `@tabler/core` package:

```bash
npm install @tabler/core
```

Import Tabler CSS in `resources/css/app.css` (or SCSS):

```css
@import '@tabler/core/dist/css/tabler.min.css';
```

Import Tabler JS in `resources/js/app.js`:

```js
import '@tabler/core/dist/js/tabler.min.js';
```

*(Optional plugin stylesheets: `tabler-flags.min.css`, `tabler-socials.min.css`, `tabler-payments.min.css`, `tabler-vendors.min.css`, `tabler-themes.min.css`)*

### 2. Plain HTML / CDN

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css" integrity="sha384-tT2UAGE9hxG/p5d0iGIvZ/s8El3nWWG3tfG02i8iOY5Pbf8cZZRVmcrrVs+JG5Vw" crossorigin="anonymous" />
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js" integrity="sha384-1yCHfhyU8+V33urXkAlLLpNYd9jdzXkxdafCg0+auIIEtYFnOuM/DwJQgk5sXY98" crossorigin="anonymous"></script>
```

---

## Page Layout & Skeleton

Every Tabler application page follows a consistent hierarchy:

```html
<div class="page">
  <!-- Horizontal Navbar or Vertical Aside -->
  <header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
      <!-- Navbar brand, nav items, user menu -->
    </div>
  </header>

  <div class="page-wrapper">
    <!-- Page Header -->
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <div class="page-pretitle">Overview</div>
            <h2 class="page-title">Dashboard</h2>
          </div>
          <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
              <a href="#" class="btn btn-primary d-none d-sm-inline-block">
                <!-- SVG Icon -->
                Create new
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Body -->
    <div class="page-body">
      <div class="container-xl">
        <div class="row row-deck row-cards">
          <!-- Cards and grid contents go here -->
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer footer-transparent d-print-none">
      <div class="container-xl">
        <!-- Footer content -->
      </div>
    </footer>
  </div>
</div>
```

### Layout Variations:
- **Sidebar (Vertical Navbar)**: Use `<aside class="navbar navbar-vertical navbar-expand-lg">` placed immediately inside `.page` before `.page-wrapper`.
- **Containers**: Use `.container-xl` for boxed layout (default) or `.container-fluid` for full-width layout.
- **Card Rows**: Always wrap dashboard cards in `<div class="row row-deck row-cards">` so cards in the same row match in height.

---

## Common Component Patterns

### 1. Cards
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
    <div class="card-actions">
      <a href="#" class="btn btn-sm btn-primary">Action</a>
    </div>
  </div>
  <div class="card-body">
    <p class="text-secondary">Content goes here...</p>
  </div>
  <div class="card-footer text-end">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</div>
```

### 2. Card Tables
For full-bleed tables inside cards, use `.card-table`:
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Users</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Role</th>
          <th>Status</th>
          <th class="w-1"></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Paweł Kuna</td>
          <td class="text-secondary">UI Designer</td>
          <td><span class="badge bg-success me-1"></span> Active</td>
          <td><a href="#">Edit</a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
```

### 3. Button Lists & Grouping
Never use ad-hoc margins between buttons or badges; use `.btn-list`, `.badge-list`, or `.avatar-list`:
```html
<div class="btn-list">
  <a href="#" class="btn btn-secondary">Cancel</a>
  <button type="submit" class="btn btn-primary">Submit</button>
</div>
```

### 4. Forms
Tabler builds on Bootstrap forms:
- Form group wrapper: `<div class="mb-3">`
- Label: `<label class="form-label required">Label</label>`
- Input: `<input type="text" class="form-control" name="example" />`
- Form hint: `<small class="form-hint">Help text goes here.</small>`
- Select: `<select class="form-select">`
- Checkbox/Radio: `<label class="form-check"><input class="form-check-input" type="checkbox"><span class="form-check-label">Option</span></label>`

### 5. Datagrid (Details / Key-Value List)
```html
<div class="datagrid">
  <div class="datagrid-item">
    <div class="datagrid-title">Registrar</div>
    <div class="datagrid-content">Third Party</div>
  </div>
  <div class="datagrid-item">
    <div class="datagrid-title">Nameservers</div>
    <div class="datagrid-content">ns1.example.com</div>
  </div>
</div>
```

### 6. Empty States
```html
<div class="empty">
  <div class="empty-img">
    <img src="..." height="128" alt="">
  </div>
  <p class="empty-title">No results found</p>
  <p class="empty-subtitle text-secondary">
    Try adjusting your search or filter to find what you're looking for.
  </p>
  <div class="empty-action">
    <a href="#" class="btn btn-primary">Add your first item</a>
  </div>
</div>
```

### 7. Badges and Status Dots
```html
<!-- Status Badge -->
<span class="badge bg-green text-green-fg">Active</span>

<!-- Status Dot -->
<span class="status status-green"><span class="status-dot status-dot-animated"></span> Online</span>
```

---

## Icons (Tabler Icons)

Use Tabler Icons as inline SVGs with the `.icon` class:
```html
<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <path d="M12 5l0 14" />
  <path d="M5 12l14 0" />
</svg>
```
- Sizing modifiers: `icon-sm`, `icon-md`, `icon-lg`.
- Place the icon before button text: `<a href="#" class="btn btn-primary"><svg class="icon">...</svg> Button</a>`.
- Add `aria-hidden="true"` to decorative icons.

---

## Theming & Color Modes

Set theme attributes on `<html>`:
- Color mode: `data-bs-theme="light"` | `"dark"` | `"auto"`
- Base gray shade: `data-bs-theme-base="slate"` | `"gray"` | `"zinc"` | `"neutral"` | `"stone"` (requires `tabler-themes.min.css`)
- Primary color: `data-bs-theme-primary="blue"` | `"azure"` | `"indigo"` | etc.
- Corner radius: `data-bs-theme-radius="0"` | `"0.25rem"` | `"0.5rem"` | etc.

Palette colors: `primary`, `secondary`, `success`, `warning`, `danger`, `info`, `blue`, `azure`, `indigo`, `purple`, `pink`, `red`, `orange`, `yellow`, `lime`, `green`, `teal`, `cyan`.
Use with standard prefixes: `bg-{color}`, `text-{color}`, `btn-{color}`, `badge bg-{color}`, `alert-{color}`.

---

## Bootstrap 5 JavaScript Data API

Interactive components (dropdowns, modals, offcanvas, tooltips, popovers, collapse, tabs) use Bootstrap 5's data attributes:
- `data-bs-toggle="dropdown"`
- `data-bs-toggle="modal" data-bs-target="#my-modal"`
- `data-bs-dismiss="modal"`
- `data-bs-toggle="tooltip" data-bs-placement="top" title="..."` (automatically initialized by `tabler.min.js`)
- `data-bs-toggle="collapse" data-bs-target="#my-collapse"`

