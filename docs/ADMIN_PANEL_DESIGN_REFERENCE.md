# MetaSeat Admin Panel — Complete UI/UX Design Reference

This document is the single source of truth for the admin panel’s design system. Nothing is omitted.

---

## 1. Design direction & target feel

- **Goal:** Premium SaaS dashboard; modern, clean, breathable.
- **Principles:**
  - Layered depth (surfaces, shadows, elevation).
  - Smooth motion and hover states.
  - Minimal borders; soft separators.
  - Premium typography.
  - Purple gradient brand identity.
- **Avoid:** Flat surfaces, dense tables, “developer dashboard” look.

---

## 2. Color & theme system

### 2.1 CSS variables (`:root`)

All tokens live in `resources/css/admin-modern.css`. Use these names in CSS and, where needed, in Blade via `var(--name)`.

**Base dark layers**

| Variable | Value | Usage |
|----------|--------|--------|
| `--bg-main` | `#0B0F1A` | Page/body background |
| `--bg-surface` | `#111827` | Sidebar, input backgrounds, soft surfaces |
| `--bg-card` | `#151B2F` | Card background |
| `--bg-card-hover` | `#182042` | Card hover state |

**Legacy (same values; do not remove)**

| Variable | Value |
|----------|--------|
| `--meta-bg` | `#0B0F1A` |
| `--meta-sidebar` | `#111827` |
| `--meta-card` | `#151B2F` |
| `--meta-card-hover` | `#182042` |
| `--meta-border` | `rgba(255, 255, 255, 0.06)` |
| `--meta-accent-start` | `#6C5CE7` |
| `--meta-accent-end` | `#8E7CFF` |
| `--meta-text` | `#FFFFFF` |
| `--meta-text-secondary` | `#9CA3AF` |
| `--meta-text-muted` | `#6B7280` |
| `--meta-live` | `#EF4444` (live indicator) |

**New semantic tokens**

| Variable | Value | Usage |
|----------|--------|--------|
| `--text-primary` | `#FFFFFF` | Headings, primary text |
| `--text-secondary` | `#9CA3AF` | Labels, secondary text |
| `--accent-gradient` | `linear-gradient(135deg, #6C5CE7, #8E7CFF)` | Primary buttons, accents |
| `--accent-glow` | `rgba(142, 124, 255, 0.25)` | Focus rings, active states |
| `--accent-glow-soft` | `rgba(142, 124, 255, 0.15)` | Soft hover glow |
| `--shadow-card` | `0 10px 30px rgba(0, 0, 0, 0.35)` | Default card shadow |
| `--shadow-card-hover` | `0 18px 40px rgba(0, 0, 0, 0.45)` | Card hover shadow |
| `--shadow-glow` | `0 6px 20px rgba(108, 92, 231, 0.35)` | Primary button shadow |
| `--shadow-glow-hover` | `0 8px 28px rgba(108, 92, 231, 0.45)` | Primary button hover shadow |

---

## 3. Typography

- **Font family:** `Inter`, sans-serif.  
  Loaded via:  
  `https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap`
- **Body:** `background: var(--bg-main); color: var(--text-primary); font-family: 'Inter', sans-serif;`
- **Page title (`.admin-page-title`):** `1.5rem`, `font-weight: 700`, `color: var(--text-primary)`, `letter-spacing: -0.02em`
- **Page description (`.admin-page-desc`):** `0.875rem`, `color: var(--text-secondary)`, `margin-top: 2px`
- **Empty state title (`.admin-empty-title`):** `font-weight: 600`, `color: var(--text-primary)`, `margin-bottom: 4px`
- **Empty state description (`.admin-empty-desc`):** `0.875rem`, `color: var(--text-secondary)`
- **Table header:** `font-size: 0.8125rem`, `font-weight: 500`, `color: var(--text-secondary)`

---

## 4. Spacing system

Use this scale consistently:

| Element | Spacing | Tailwind / implementation |
|---------|---------|----------------------------|
| Page padding (main content) | 24px | `p-6` on `<main>` |
| Card padding | 20–24px | `p-5` or `p-6` |
| Grid gap (stat cards) | 20px | `gap-5` |
| Section gap | 32px | `mb-8` |
| Table cell padding | 14px vertical, 20px horizontal | In `.admin-table th, .admin-table td` |
| Section gap (smaller) | 24px | `mb-6` |
| Grid gap (charts / lists) | 24px | `gap-6` |
| Helper class | 2rem bottom margin | `.section-gap` → `margin-bottom: 2rem` |
| Helper class | 1.5rem gap | `.grid-gap-modern` → `gap: 1.5rem` |

---

## 5. Layout structure

### 5.1 Shell (authenticated)

- **File:** `resources/views/admin/layouts/app.blade.php`
- **Body:** `class="min-h-screen surface-main"`
- **Structure:** Flex container; fixed sidebar + main.
- **Sidebar:** `w-64 min-h-screen admin-sidebar flex flex-col fixed z-30`
  - Header: `p-5 border-b border-[rgba(255,255,255,0.06)]`
  - Nav: `flex-1 p-3 space-y-0.5 overflow-y-auto`
  - Footer (logout): `p-3 border-t border-[rgba(255,255,255,0.06)]`
- **Main:** `flex-1 ml-64 p-6 min-h-screen` (content area).

### 5.2 Login page (standalone)

- **File:** `resources/views/admin/auth/login.blade.php`
- **Body:** `class="min-h-screen flex items-center justify-center p-4 surface-main"`
- **Card wrapper:** `w-full max-w-md`; inner card uses `admin-card p-8`.
- **Uses:** Same `@vite(['resources/css/admin-modern.css'])`; no sidebar.

---

## 6. Sidebar

### 6.1 Container

- **Class:** `admin-sidebar`
- **Styles:**  
  `background: var(--meta-sidebar);`  
  `border-right: 1px solid rgba(255, 255, 255, 0.06);`

### 6.2 Nav links

- **Class:** `admin-sidebar-link`
- **Default:**  
  `display: flex; align-items: center; gap: 12px;`  
  `padding: 10px 14px; border-radius: 12px;`  
  `color: var(--text-secondary); transition: all 0.2s ease;`
- **Hover:**  
  `background: rgba(255, 255, 255, 0.04); color: var(--text-primary);`
- **Active (current route):**  
  Add class `active`.  
  `background: linear-gradient(90deg, rgba(108, 92, 231, 0.15), transparent);`  
  `box-shadow: inset 3px 0 0 #8E7CFF;`  
  `color: var(--text-primary); border-left: none;`
- **Icons:** `.admin-sidebar-link .lucide` → `width: 20px; height: 20px; flex-shrink: 0;`

### 6.3 Logout button

- Uses `admin-sidebar-link` plus: `w-full text-left hover:!text-red-400 hover:!bg-red-500/10 !border-0`

---

## 7. Cards

### 7.1 Standard admin card

- **Class:** `admin-card`
- **Styles:**  
  `background: var(--bg-card);`  
  `border: 1px solid rgba(255, 255, 255, 0.05);`  
  `border-radius: 14px;`  
  `box-shadow: var(--shadow-card);`  
  `transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;`
- **Hover:**  
  `transform: translateY(-2px);`  
  `box-shadow: var(--shadow-card-hover);`  
  `background: var(--bg-card-hover);`

### 7.2 Utility card (alternative)

- **Class:** `card-modern`
- **Styles:**  
  `border-radius: 14px; padding: 1.5rem;`  
  `box-shadow: var(--shadow-card);`  
  `border: 1px solid rgba(255, 255, 255, 0.05);`  
  Same hover as above (translateY(-2px), stronger shadow).

### 7.3 Surface helpers (no hover)

- **`.surface-main`:** `background-color: var(--bg-main);`
- **`.surface-soft`:** `background-color: var(--bg-surface);`
- **`.surface-card`:** `background-color: var(--bg-card); border-radius: 14px; box-shadow: var(--shadow-card);`

---

## 8. Buttons

### 8.1 Primary

- **Classes:** `admin-btn-primary` or `btn-primary`
- **Styles:**  
  `display: inline-flex; align-items: center; gap: 8px;`  
  `padding: 10px 20px; border-radius: 12px; font-weight: 500;`  
  `background: var(--accent-gradient); color: #fff;`  
  `box-shadow: var(--shadow-glow); transition: all 0.2s ease;`
- **Hover:**  
  `box-shadow: var(--shadow-glow-hover); transform: translateY(-1px);`
- **Full width (e.g. login):** Add `w-full`.

### 8.2 Ghost / secondary

- **Classes:** `admin-btn-ghost` or `btn-ghost`
- **Styles:**  
  `padding: 8px 16px; border-radius: 10px;`  
  `background: rgba(255, 255, 255, 0.06);` (admin-btn-ghost only)  
  `color: var(--text-secondary); transition: all 0.2s ease;`
- **Hover:**  
  `background: rgba(255, 255, 255, 0.08);` (admin) or `rgba(255, 255, 255, 0.05)` (btn-ghost)  
  `color: var(--text-primary);`

### 8.3 Focus (accessibility)

- **Focus-visible:**  
  `outline: none; box-shadow: 0 0 0 2px var(--accent-glow);`  
  Applied to `.admin-btn-primary`, `.admin-btn-ghost`, `.admin-sidebar-link`.

---

## 9. Tables

- **Class:** `admin-table` on `<table>`; typically inside `.admin-card.overflow-hidden`.

### 9.1 Header

- **thead:**  
  `background: rgba(255, 255, 255, 0.03);`  
  `color: var(--text-secondary); font-size: 0.8125rem; font-weight: 500;`
- **th:**  
  `padding: 14px 20px; text-align: left;`  
  `border-bottom: 1px solid rgba(255, 255, 255, 0.05);`

### 9.2 Body

- **td:**  
  `padding: 14px 20px;`  
  `border-bottom: 1px solid rgba(255, 255, 255, 0.05);`
- **tr:**  
  `transition: background 0.15s ease;`
- **tr:hover:**  
  `background: rgba(142, 124, 255, 0.06);`

### 9.3 Responsive

- **Max-width 768px:**  
  `.admin-card.overflow-hidden` → `overflow-x: auto; -webkit-overflow-scrolling: touch;`  
  `.admin-table` → `min-width: 600px;` so table scrolls horizontally.

---

## 10. Form inputs

### 10.1 Text-style inputs

- **Classes:** `admin-input` or `input-modern`
- **Styles:**  
  `width: 100%; padding: 10px 14px; border-radius: 10px;`  
  `background: var(--bg-surface);`  
  `border: 1px solid rgba(255, 255, 255, 0.1);`  
  `color: var(--text-primary);`  
  `transition: border-color 0.2s ease, box-shadow 0.2s ease;`
- **Placeholder:** `color: var(--meta-text-muted);`
- **Focus:**  
  `outline: none; border-color: var(--meta-accent-end);`  
  `box-shadow: 0 0 0 3px var(--accent-glow);`
- **Inline width (e.g. filters):** Add `w-auto min-w-[140px]` or similar.

---

## 11. Stat card icons (dashboard)

- **Class:** `admin-stat-icon`
- **Styles:**  
  `width: 48px; height: 48px; border-radius: 12px;`  
  `display: flex; align-items: center; justify-content: center;`
- **Icon size:** `.admin-stat-icon .lucide` → `width: 24px; height: 24px;`
- **Background/color:** Use Tailwind, e.g. `bg-[var(--meta-accent-start)]/20 text-[var(--meta-accent-end)]`, `bg-blue-500/20 text-blue-400`, `bg-emerald-500/20 text-emerald-400`, etc.

---

## 12. Empty state

- **Partial:** `admin.partials.empty`
- **Props:** `icon` (Lucide name), `title`, `description`
- **Container:** `admin-empty`  
  `padding: 48px 24px; text-align: center;`
- **Icon:** Lucide, `w-12 h-12`; `.admin-empty .lucide` → `color: var(--meta-text-muted); margin: 0 auto 16px;`
- **Title:** `admin-empty-title`
- **Description:** `admin-empty-desc`

---

## 13. Page header pattern

At top of each page content:

```html
<div class="mb-8 animate-fade-in">   <!-- or mb-6 for tighter -->
    <h1 class="admin-page-title">Page Title</h1>
    <p class="admin-page-desc">Short description.</p>
</div>
```

With primary action:

```html
<div class="flex justify-between items-center mb-6 animate-fade-in">
    <div>
        <h1 class="admin-page-title">Events</h1>
        <p class="admin-page-desc">Manage events and seats</p>
    </div>
    <a href="..." class="admin-btn-primary"><i data-lucide="plus"></i> Add Event</a>
</div>
```

---

## 14. Alerts (success / error)

- **Success:**  
  `class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm animate-fade-in"`
- **Error:**  
  `class="mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm"` (e.g. login validation)

---

## 15. Status badges (e.g. event status)

- **Active:** `bg-emerald-500/20 text-emerald-400`
- **Completed:** `bg-slate-500/20 text-slate-400`
- **Inactive/other:** `bg-amber-500/20 text-amber-400`
- **Common:** `inline-flex px-2.5 py-1 rounded-lg text-xs font-medium`

---

## 16. Live indicator

- **Class:** `live-dot`
- **Style:** `background-color: var(--meta-live);`
- **Usage:** e.g. `live-dot w-2.5 h-2.5 rounded-full animate-pulse` next to “Live” stats.

---

## 17. Pagination

- **Partial:** `admin.partials.pagination`
- **Disabled prev/next:**  
  `px-3 py-2 rounded-lg bg-white/5 text-[var(--meta-text-muted)] text-sm cursor-not-allowed`
- **Enabled links:** `px-3 py-2 rounded-lg admin-btn-ghost text-sm transition`
- **Current page:**  
  `px-3 py-2 rounded-lg text-sm font-medium text-white` with  
  `style="background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);"`

---

## 18. Motion & transitions

- **Card hover:** `transform: translateY(-2px)`; `transition: transform 0.2s ease, box-shadow 0.2s ease` (and background where used).
- **Button hover:** `transform: translateY(-1px)`; `transition: all 0.2s ease`.
- **Table row:** `transition: background 0.15s ease`.
- **Sidebar link:** `transition: all 0.2s ease`.
- **Page enter:** `animate-fade-in` → `@keyframes fadeIn`: from `opacity: 0; transform: translateY(6px);` to `opacity: 1; transform: translateY(0);`; `animation: fadeIn 0.35s ease forwards;`
- **Login card:** Optional local `fadeIn` with `translateY(8px)` and `0.4s` in login blade.

---

## 19. Purple glow & accents

- **Active nav:** Gradient + inset bar (see Sidebar).
- **Focus ring:** `box-shadow: 0 0 0 3px var(--accent-glow);` on inputs; `0 0 0 2px var(--accent-glow)` on buttons/sidebar (focus-visible).
- **Primary button:** `box-shadow: var(--shadow-glow)` / `var(--shadow-glow-hover)`.
- **Table row hover:** `background: rgba(142, 124, 255, 0.06);`
- Use glow sparingly for emphasis only.

---

## 20. Legacy / compatibility classes (do not remove)

These keep existing Blade/JS class names working:

- **`.bg-meta-dark`:** `background-color: var(--meta-bg) !important;`
- **`.bg-meta-card`:** `background-color: var(--meta-card) !important;`
- **`.text-meta-secondary`:** `color: var(--meta-text-secondary) !important;`
- **`.gradient-primary`:** accent gradient + shadow; hover uses `--shadow-glow-hover`.
- **`.live-dot`:** `background-color: var(--meta-live);`

---

## 21. Assets & loading

- **Tailwind:** CDN `https://cdn.tailwindcss.com`
- **Fonts:** Google Fonts Inter 400, 500, 600, 700; preconnect to googleapis.com and gstatic.com.
- **Icons:** Lucide via `https://unpkg.com/lucide@latest`; `lucide.createIcons()` after DOM ready.
- **Design system CSS:** `@vite(['resources/css/admin-modern.css'])` in both:
  - `admin/layouts/app.blade.php`
  - `admin/auth/login.blade.php`
- **Custom styles:** `@stack('styles')` in layout head; `@stack('scripts')` before `</body>`.

---

## 22. Grid & content patterns

- **Stat cards (dashboard):**  
  `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8`
- **Chart/widget row:**  
  `grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8`
- **Two-column lists (e.g. recent bookings):**  
  `grid grid-cols-1 lg:grid-cols-2 gap-6`
- **Card with list header:**  
  `admin-card overflow-hidden`; header `font-semibold text-white px-5 py-4 border-b border-[var(--meta-border)] text-[15px]`; list `divide-y divide-[var(--meta-border)] max-h-72 overflow-y-auto`; row `px-5 py-3 ... hover:bg-[var(--meta-card-hover)]`

---

## 23. Checklist (no omissions)

- [x] Colors: all `:root` variables and usage
- [x] Typography: font, sizes, weights for title, desc, empty, table
- [x] Spacing: page, card, grid, section, table padding
- [x] Layout: shell, sidebar, main, login
- [x] Sidebar: container, link default/hover/active, icons, logout
- [x] Cards: admin-card, card-modern, surface-*
- [x] Buttons: primary, ghost, focus-visible
- [x] Tables: thead, th, td, tr hover, responsive scroll
- [x] Inputs: admin-input / input-modern, focus
- [x] Stat icons: admin-stat-icon, lucide size
- [x] Empty state: partial, classes
- [x] Page header: title + desc + optional CTA
- [x] Alerts, status badges, live dot
- [x] Pagination partial and styles
- [x] Motion: card, button, table, sidebar, fadeIn
- [x] Purple glow: nav active, focus, button shadow, table hover
- [x] Legacy classes
- [x] Assets: Tailwind, fonts, Lucide, Vite, stacks
- [x] Grid and content patterns

---

**Document version:** 1.0  
**Design system file:** `resources/css/admin-modern.css`  
**Layout:** `resources/views/admin/layouts/app.blade.php`  
**Login:** `resources/views/admin/auth/login.blade.php`
