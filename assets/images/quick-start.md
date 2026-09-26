# Quick Start: Adding Images to 4 TO 9 Restaurant

## Fast Track Implementation

### Option 1: Replace External URLs with Local Images

If you generate the images using the prompts, simply:

1. **Generate all 10 images** using your preferred AI tool
2. **Rename them** according to the naming convention in `image-guide.md`
3. **Place them** in `assets/images/` directory
4. **Update the PHP files** to reference local images

### Example Updates:

#### In `index.php` (Line ~36):
**Current:**
```php
background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920');
```

**Update to:**
```php
background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo SITE_URL; ?>/assets/images/hero-banner.jpg');
```

#### In `reservation.php` (Line ~47):
**Current:**
```php
background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1920');
```

**Update to:**
```php
background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?php echo SITE_URL; ?>/assets/images/reservation-table.jpg');
```

### Option 2: Use Admin Panel for Uploadable Images

For menu items, categories, and gallery:

1. **Login to admin panel**: `/admin/login.php`
2. **Navigate to the respective section**:
   - Menu Items: `/admin/menu-items.php`
   - Categories: `/admin/categories.php`
   - Gallery: `/admin/gallery.php`
3. **Upload images** using the forms provided
4. **Images will be automatically** placed in the correct `uploads/` directories

## Recommended Image Generation Settings

When using AI image generators, use these settings for best results:

### Midjourney:
```
--ar 16:9 --style raw --v 6.0
```
For 4:3 images:
```
--ar 4:3 --style raw --v 6.0
```

### DALL-E 3:
- Use the exact prompts provided
- Specify the aspect ratio in the prompt
- Set quality to "HD" if available

### Stable Diffusion:
- Resolution: 1024x576 for 16:9, 768x576 for 4:3
- Steps: 30-50
- CFG Scale: 7-9
- Sampler: DPM++ 2M Karras

## Testing Your Images

After adding images:

1. **Clear browser cache** to ensure you see the new images
2. **Test on different devices** (desktop, tablet, mobile)
3. **Check image loading speed** - should load in under 2 seconds
4. **Verify alt text** for accessibility
5. **Test image paths** - right-click and "Open image in new tab"

## Placeholder Strategy

While you're generating images, the current setup uses:
- Unsplash URLs for main images (high quality, reliable)
- Placeholder text for missing upload images
- Graceful fallbacks in the code

This means the site is fully functional even before you add custom images.

## Next Steps

1. ✅ Generate images using the provided prompts
2. ✅ Organize them according to the guide
3. ✅ Update PHP file references
4. ✅ Test the website with new images
5. ✅ Optimize image sizes for performance

Your restaurant website will look fantastic with these custom, branded images!