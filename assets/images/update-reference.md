# Image Update Reference - Line by Line

## Files to Update with Local Images

### 1. index.php

**Line ~36 (Hero Section):**
```php
// FIND:
background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920');

// REPLACE WITH:
background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo SITE_URL; ?>/assets/images/hero-banner.jpg');
```

**Line ~76 (About Section Image):**
```php
// FIND:
<img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600" alt="Restaurant Interior">

// REPLACE WITH:
<img src="<?php echo SITE_URL; ?>/assets/images/dining-area.jpg" alt="4 TO 9 Restaurant Interior">
```

### 2. reservation.php

**Line ~47 (Reservation Background):**
```php
// FIND:
background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1920');

// REPLACE WITH:
background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?php echo SITE_URL; ?>/assets/images/reservation-table.jpg');
```

### 3. about.php

**Line ~36 (About Image 1):**
```php
// FIND:
<img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600" alt="Restaurant Story">

// REPLACE WITH:
<img src="<?php echo SITE_URL; ?>/assets/images/restaurant-exterior.jpg" alt="4 TO 9 Restaurant Exterior">
```

**Line ~52 (About Image 2):**
```php
// FIND:
<img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600" alt="Our Chef">

// REPLACE WITH:
<img src="<?php echo SITE_URL; ?>/assets/images/about-section.jpg" alt="4 TO 9 Restaurant Details">
```

### 4. contact.php

**Add New Hero Section (after line ~20):**
```php
// ADD AFTER <section class="section contact-section">:
<div class="contact-hero" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo SITE_URL; ?>/assets/images/contact-section.jpg'); background-size: cover; background-position: center; padding: 4rem 0; margin-bottom: 2rem;">
    <div class="container text-center">
        <h2 style="color: var(--gold-accent);">Contact Us</h2>
        <p style="color: var(--text-secondary);">Get in touch with 4 TO 9</p>
    </div>
</div>
```

## CSS Updates Needed

### In assets/css/style.css

**Add contact hero styles (after line ~650):**
```css
.contact-hero {
    border-radius: 10px;
    border: 2px solid var(--gold-accent);
}
```

## Database Images (No Code Changes Needed)

These images are uploaded via the admin panel:

### Menu Items (Prompts 5, 6, 7)
- **Burger**: Upload via `/admin/menu-items.php` → Chicken Burger item
- **Pizza**: Upload via `/admin/menu-items.php` → Margherita Pizza item  
- **Desserts**: Upload via `/admin/menu-items.php` → Create dessert item

### Gallery Images (Prompt 9)
- **Gallery**: Upload via `/admin/gallery.php` → Add restaurant interior image

### Category Images
- Upload via `/admin/categories.php` for each category

## Quick Update Script

If you want to batch update these references, you can use this approach:

1. **Open each file** in a text editor
2. **Use Find & Replace** (Ctrl+H or Cmd+H)
3. **Replace the Unsplash URLs** with the local paths shown above
4. **Save and test** each file

## Verification Checklist

After updating images:

- [ ] Hero banner displays correctly on homepage
- [ ] About section images load properly
- [ ] Reservation page background shows custom image
- [ ] Contact page has hero section (if added)
- [ ] All uploaded images appear in admin panel
- [ ] Images display on mobile devices
- [ ] Image loading time is acceptable
- [ ] Alt text is descriptive for accessibility

## Rollback Plan

If something goes wrong:

1. **Keep backup** of original files
2. **Test one file at a time**
3. **Use version control** if available
4. **Can revert to Unsplash URLs** if needed

The Unsplash URLs currently in the code are reliable and high-quality, so your site will remain functional even during the image update process.