# Image Guide for 4 TO 9 Restaurant Website

## Image Placement Guide

Based on the 10 image generation prompts, here's where each image should be placed in the project:

### 1. Homepage Hero Banner
- **Prompt #1**: Homepage Hero Banner
- **Placement**: Use as hero background in `index.php`
- **File Location**: `assets/images/hero-banner.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Currently using Unsplash URL in `index.php` line ~36

### 2. Restaurant Exterior Image  
- **Prompt #2**: Restaurant Exterior Image
- **Placement**: About page exterior shot
- **File Location**: `assets/images/restaurant-exterior.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Could be added to `about.php`

### 3. Main Dining Area Image
- **Prompt #3**: Main Dining Area Image
- **Placement**: About page interior shot
- **File Location**: `assets/images/dining-area.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Could be added to `about.php`

### 4. Table Booking / Reservation Image
- **Prompt #4**: Table Booking / Reservation Image
- **Placement**: Reservation page hero/section
- **File Location**: `assets/images/reservation-table.jpg`
- **Size**: 1200x900px (4:3)
- **Current Code**: Currently using background in `reservation.php`

### 5. Signature Dish – Burger
- **Prompt #5**: Signature Dish Image – Burger
- **Placement**: Menu item example (upload via admin)
- **File Location**: `uploads/menu/chicken-burger.jpg`
- **Size**: 800x600px (4:3)
- **Current Code**: Upload via admin at `/admin/menu-items.php`

### 6. Signature Dish – Pizza
- **Prompt #6**: Signature Dish Image – Pizza
- **Placement**: Menu item example (upload via admin)
- **File Location**: `uploads/menu/margherita-pizza.jpg`
- **Size**: 800x600px (4:3)
- **Current Code**: Upload via admin at `/admin/menu-items.php`

### 7. Drinks and Desserts
- **Prompt #7**: Drinks and Desserts Image
- **Placement**: Menu item example (upload via admin)
- **File Location**: `uploads/menu/desserts.jpg`
- **Size**: 800x600px (4:3)
- **Current Code**: Upload via admin at `/admin/menu-items.php`

### 8. About Us Section Image
- **Prompt #8**: About Us Section Image
- **Placement**: About page section
- **File Location**: `assets/images/about-section.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Could be added to `about.php`

### 9. Gallery Image
- **Prompt #9**: Gallery Image
- **Placement**: Gallery section (upload via admin)
- **File Location**: `uploads/gallery/restaurant-interior.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Upload via admin at `/admin/gallery.php`

### 10. Contact Section Image
- **Prompt #10**: Contact / Footer Section Image
- **Placement**: Contact page background
- **File Location**: `assets/images/contact-section.jpg`
- **Size**: 1920x1080px (16:9)
- **Current Code**: Could be added to `contact.php`

## Implementation Steps

### Step 1: Generate Images
Use the provided prompts with your preferred AI image generation tool to create all 10 images.

### Step 2: Organize Images
Place generated images in the appropriate directories:
- Static images → `assets/images/`
- Uploadable images → Use admin panel to upload to `uploads/` directories

### Step 3: Update Code References
Update the PHP files to use local images instead of Unsplash URLs.

### Step 4: Test
Verify all images display correctly across different pages and devices.

## Current Image References in Code

The following files currently reference external images that should be replaced:

### index.php
- Line ~36: Hero banner (Unsplash URL)
- Line ~76: About section image (Unsplash URL)
- Line ~112: Category images (uploads/categories/)

### menu.php
- Line ~42: Menu item images (uploads/menu/)

### about.php
- Line ~36: About image (Unsplash URL)
- Line ~52: Chef image (Unsplash URL)

### gallery.php
- Line ~24: Gallery images (uploads/gallery/)

### contact.php
- Line ~67: Google Maps embed (no image needed)

## Recommended File Naming Convention

Use descriptive, lowercase filenames with hyphens:
- `hero-banner.jpg`
- `restaurant-exterior.jpg`
- `dining-area.jpg`
- `reservation-table.jpg`
- `chicken-burger.jpg`
- `margherita-pizza.jpg`
- `desserts-drinks.jpg`
- `about-section.jpg`
- `restaurant-interior.jpg`
- `contact-section.jpg`

## Image Optimization Tips

1. **File Size**: Keep images under 500KB for web performance
2. **Format**: Use JPG for photos, PNG for graphics with transparency
3. **Compression**: Use tools like TinyPNG or ImageOptim
4. **Responsive**: Consider creating multiple sizes for different devices
5. **Alt Text**: Add descriptive alt text for accessibility

## Alternative: Free Stock Images

If you prefer not to use AI-generated images, consider these free stock photo resources:
- Unsplash (https://unsplash.com)
- Pexels (https://pexels.com)
- Pixabay (https://pixabay.com)
- Freepik (https://freepik.com)

Search for terms like: "luxury restaurant", "fine dining", "gourmet food", "restaurant interior"