# Admin Dashboard Theme Customization Guide

## Overview

The Smart Library admin dashboard now features **two modern color themes** with consistent design:

1. **Orange Gradient Theme** (Default) - Modern, vibrant orange
2. **Maroon Gradient Theme** - Deep, refined maroon tones

Both themes maintain:
- **Exact layout and spacing** - No structural changes
- **Clean card designs** - White/light backgrounds with soft shadows
- **Smooth gradients** - Left-to-right gradient direction
- **Consistent contrast** - Readable text and clear visual hierarchy
- **Gold accent preservation** - Maintains accent colors for highlights

---

## Color Palettes

### Orange Gradient Theme (Default)

**Primary Gradient (Navbar, Heroes, Headers):**
```
linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500)
```

**Solid Colors:**
- Primary Button: `#FF8C00`
- Button Hover: `#FF6A00`
- Analytics Dot: `#FF8C00`
- Accent Background: `#FFA500`

**Usage:**
- Navbar background
- Dashboard hero sections
- Page headers and banners
- Analytics title indicators
- Button backgrounds
- Card borders and shadows

---

### Maroon Gradient Theme

**Primary Gradient (Navbar, Heroes, Headers):**
```
linear-gradient(90deg, #5A0000, #7A0000, #A00000)
```

**Solid Colors:**
- Primary Button: `#7A0000`
- Button Hover: `#5A0000`
- Analytics Dot: `#7A0000`
- Accent Background: `#A00000`

**Usage:**
- All same components as orange theme
- Deep maroon tones for professional appearance
- Maintains design intensity and hierarchy

---

## Switching Between Themes

### Method 1: CSS File Approach (Recommended)

#### Step 1: Verify Orange Theme (Default)

The orange theme is currently active. Verify by checking that this line is **NOT** present in `includes/admin_layout.php`:

```html
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/theme-maroon-gradient.css">
```

#### Step 2: Switch to Maroon Theme

1. Open `includes/admin_layout.php`
2. Locate the `</head>` closing tag
3. Add this line before `</head>`:

```html
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/theme-maroon-gradient.css">
```

Full example in context:
```html
</head>
<style>
    :root {
        --sl-primary: <?php echo COLOR_PRIMARY; ?>;
        --sl-accent: <?php echo COLOR_ACCENT; ?>;
        --sl-light: <?php echo COLOR_LIGHT; ?>;
    }
    <!-- ... existing styles ... -->
</style>
<!-- Add this line to enable maroon theme: -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/theme-maroon-gradient.css">
<body>
```

#### Step 3: Back to Orange Theme

Simply remove or comment out the maroon theme CSS link:

```html
<!-- <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/theme-maroon-gradient.css"> -->
```

---

## Theme Components Reference

### All Themed Components

Each theme applies consistent colors across:

1. **Navigation Bar** - Gradient background, link colors
2. **Dashboard Hero Section** - Welcome banner with gradient
3. **Cards** - Border colors, shadows, background gradients
4. **Analytics Cards** - Title indicators, tab states
5. **Buttons** - Primary and hover states
6. **Badges** - Notification badges with animation
7. **Form Elements** - Focus states, borders
8. **Tables** - Header backgrounds, borders
9. **Status Indicators** - Colored dots and chips
10. **Shadows & Glows** - Color-matched shadows

---

## Accessibility & Contrast

Both themes maintain WCAG AA compliance:
- **Text on gradients** - Uses white or dark text for sufficient contrast
- **Button text** - Maintains 4.5:1 contrast ratio minimum
- **Interactive elements** - Clear visual feedback on hover/focus

---

## File Locations

```
smartlibrary/
├── css/
│   ├── theme-orange-gradient.css      (Reference/Optional)
│   └── theme-maroon-gradient.css      (Override file for maroon)
├── includes/
│   └── admin_layout.php               (Main layout - add CSS link here)
└── admin/
    ├── dashboard.php
    ├── approved.php
    ├── books.php
    ├── borrow_requests.php
    ├── returns.php
    ├── qr_scan.php
    └── ... (other admin pages)
```

---

## Color Mapping Reference

### Navbar & Buttons

| Element | Orange Theme | Maroon Theme |
|---------|--------------|--------------|
| Navbar BG | `linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500)` | `linear-gradient(90deg, #5A0000, #7A0000, #A00000)` |
| Button Primary | `#FF8C00` | `#7A0000` |
| Button Hover | `#FF6A00` | `#5A0000` |
| Badge BG | `#FF6A00` | `#A00000` |

### Cards & Backgrounds

| Element | Orange Theme | Maroon Theme |
|---------|--------------|--------------|
| Card Border | `rgba(255, 106, 0, 0.12)` | `rgba(160, 0, 0, 0.12)` |
| Card BG | `linear-gradient(145deg, #ffffff, #fff8f3)` | `linear-gradient(145deg, #ffffff, #faf5f5)` |
| Shadow | `rgba(255, 106, 0, 0.08)` | `rgba(160, 0, 0, 0.08)` |

### Indicators & Accents

| Element | Orange Theme | Maroon Theme |
|---------|--------------|--------------|
| Analytics Dot | `#FF8C00` | `#7A0000` |
| Accent Highlight | `#FFA500` | `#A00000` |
| Focus State | `#FF8C00` | `#7A0000` |

---

## Creating Custom Themes

To create additional themes:

1. **Duplicate** `css/theme-maroon-gradient.css`
2. **Replace** all color values with your desired palette
3. **Keep** the CSS selector names unchanged
4. **Link** the new CSS file in `admin_layout.php`

Example:
```css
/* Custom theme - emerald */
.sl-navbar {
    background: linear-gradient(90deg, #00664d, #008866, #00b388) !important;
}

.btn-sl-primary {
    background-color: #008866 !important;
}
/* ... continue for all components ... */
```

---

## Testing After Theme Switch

After switching themes, verify:

1. ✓ Navbar displays correct gradient
2. ✓ Dashboard hero banner shows new colors
3. ✓ Buttons display and hover correctly
4. ✓ Cards show proper borders and shadows
5. ✓ Analytics indicators use correct colors
6. ✓ Page navigation links maintain contrast
7. ✓ Forms show proper focus states
8. ✓ Badges display correctly

---

## Gradient Direction

Both themes use **left-to-right (90deg) gradients** for:
- Smooth visual flow
- Professional appearance
- Consistent transitions

This replaces the previous diagonal (135deg) gradients for a more modern aesthetic.

---

## Summary

- **Orange Theme**: Modern, energetic, highly visible
- **Maroon Theme**: Professional, refined, sophisticated
- **Layout**: Completely unchanged - focus on color only
- **Switching**: Simple CSS file link addition/removal
- **Both**: Maintain design quality, contrast, and accessibility

For questions or additional customizations, refer to the color palettes and file locations above.
