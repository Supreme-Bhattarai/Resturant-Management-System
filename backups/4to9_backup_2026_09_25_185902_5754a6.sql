-- Database Backup: restaurant_4to9
-- Generated: 2026-09-25 18:59:02
-- ----------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for table `backups`
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `backups`

-- Table structure for table `bookings`
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `guest_count` int(11) NOT NULL,
  `special_request` text DEFAULT NULL,
  `status` enum('pending','confirmed','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `bookings`
INSERT INTO `bookings` VALUES ('1', 'BK6AB624DE9E971', '3', '2', '2026-09-26', '15:23:00', '2', 'sddsddff', 'pending', NULL, '2026-09-25 13:23:06', '2026-09-25 13:23:06');
INSERT INTO `bookings` VALUES ('3', 'QAADMINB260925', '3', '1', '2030-01-21', '13:00:00', '2', 'Temporary admin workflow test', 'pending', 'reset for conflict test', '2026-09-25 19:11:22', '2026-09-25 19:13:40');
INSERT INTO `bookings` VALUES ('4', 'QAADMINC260925', '3', NULL, '2030-01-21', '13:00:00', '2', 'Temporary conflict test', 'rejected', 'reject test', '2026-09-25 19:12:36', '2026-09-25 19:13:40');

-- Table structure for table `gallery`
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `gallery`
INSERT INTO `gallery` VALUES ('1', 'Restaurant Interior', 'restaurant_interior.jpg', 'active', '2026-09-24 19:57:31');
INSERT INTO `gallery` VALUES ('2', 'Delicious Food', 'delicious_food.jpg', 'active', '2026-09-24 19:57:31');
INSERT INTO `gallery` VALUES ('3', 'Ambience', 'restaurant_ambience.jpg', 'active', '2026-09-24 19:57:31');
INSERT INTO `gallery` VALUES ('4', 'Chef Cooking', 'chef_cooking.jpg', 'active', '2026-09-24 19:57:31');
INSERT INTO `gallery` VALUES ('5', 'Window-side Dining', 'table_window.jpg', 'active', '2026-09-25 12:46:03');
INSERT INTO `gallery` VALUES ('6', 'Outdoor Dining', 'outdoor_dining.jpg', 'active', '2026-09-25 12:46:03');
INSERT INTO `gallery` VALUES ('7', 'A Table for the Occasion', 'dining_table.jpg', 'active', '2026-09-25 12:46:03');
INSERT INTO `gallery` VALUES ('8', 'Bar Seating', 'bar_seating.jpg', 'active', '2026-09-25 12:46:03');

-- Table structure for table `menu_categories`
DROP TABLE IF EXISTS `menu_categories`;
CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `menu_categories`
INSERT INTO `menu_categories` VALUES ('1', 'Starters', 'Appetizers and finger foods', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('2', 'Main Course', 'Main dishes and entrees', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('3', 'Pizza', 'Various types of pizza', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('4', 'Burger', 'Gourmet burgers', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('5', 'Pasta', 'Italian pasta dishes', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('6', 'Drinks', 'Beverages and cocktails', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('7', 'Desserts', 'Sweet treats and desserts', NULL, 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_categories` VALUES ('8', 'Kids\' Favorites', 'Smaller portions of familiar family favourites', NULL, 'active', '2026-09-25 13:29:44', '2026-09-25 13:29:44');
INSERT INTO `menu_categories` VALUES ('9', 'Momo', 'Steamed and fried dumplings served with house dipping sauce', NULL, 'active', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_categories` VALUES ('10', 'Fast Food', 'Freshly prepared favourites for a quick bite', NULL, 'active', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_categories` VALUES ('13', 'QA Temp Catalog Revised 6925', 'Updated temporary category', 'f8991bb229f013ed8661aba875441a09.jpg', 'inactive', '2026-09-25 19:11:18', '2026-09-25 19:12:50');

-- Table structure for table `menu_items`
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `menu_items`
INSERT INTO `menu_items` VALUES ('1', '1', 'Spring Rolls', 'Crispy vegetable spring rolls with sweet chili sauce', '150.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('2', '1', 'Garlic Bread', 'Toasted bread with garlic butter and herbs', '120.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('3', '2', 'Grilled Chicken', 'Tender grilled chicken with herbs and vegetables', '450.00', NULL, '1', '1', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('4', '2', 'Fish Curry', 'Traditional fish curry with rice', '380.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('5', '3', 'Margherita Pizza', 'Classic pizza with tomato sauce and mozzarella', '350.00', NULL, '1', '1', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('6', '3', 'Pepperoni Pizza', 'Pizza with pepperoni and cheese', '420.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('7', '4', 'Chicken Burger', 'Grilled chicken patty with cheese and vegetables', '450.00', NULL, '1', '1', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('8', '4', 'Veggie Burger', 'Vegetarian patty with fresh vegetables', '380.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('9', '5', 'Spaghetti Carbonara', 'Creamy pasta with bacon and parmesan', '420.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('10', '5', 'Penne Arrabiata', 'Spicy tomato pasta with garlic and chili', '350.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('11', '6', 'Fresh Lime Soda', 'Refreshing lime soda with mint', '80.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('12', '6', 'Mango Smoothie', 'Fresh mango smoothie', '120.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('13', '7', 'Chocolate Brownie', 'Warm chocolate brownie with ice cream', '180.00', NULL, '1', '1', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('14', '7', 'Ice Cream Sundae', 'Vanilla ice cream with toppings', '150.00', NULL, '1', '0', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `menu_items` VALUES ('15', '8', 'Little Margherita Pizza', 'A smaller mozzarella and tomato pizza with basil, baked until golden.', '250.00', NULL, '1', '0', '2026-09-25 13:29:44', '2026-09-25 13:29:44');
INSERT INTO `menu_items` VALUES ('16', '8', 'Mini Cheeseburger', 'A mini cheese burger with lettuce and a side of fries.', '260.00', NULL, '1', '0', '2026-09-25 13:29:44', '2026-09-25 13:29:44');
INSERT INTO `menu_items` VALUES ('17', '8', 'Creamy Mac & Cheese', 'Shell pasta in a creamy cheese sauce with a mild, comforting flavour.', '230.00', NULL, '1', '0', '2026-09-25 13:29:44', '2026-09-25 13:29:44');
INSERT INTO `menu_items` VALUES ('18', '8', 'Mini Sundae', 'A smaller vanilla sundae with chocolate sauce and a crisp wafer.', '150.00', NULL, '1', '0', '2026-09-25 13:29:44', '2026-09-25 13:29:44');
INSERT INTO `menu_items` VALUES ('19', '9', 'Veg Steamed Momo', 'Tender steamed dumplings filled with seasoned vegetables, served with dipping sauce.', '180.00', NULL, '1', '0', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_items` VALUES ('20', '9', 'Crispy Fried Momo', 'Golden fried dumplings with a crisp shell and a spicy dipping sauce.', '220.00', NULL, '1', '0', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_items` VALUES ('21', '9', 'Momo Platter', 'A shareable assortment of steamed, pan-seared and fried dumplings.', '320.00', NULL, '1', '0', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_items` VALUES ('22', '10', 'Loaded Fries', 'Crisp fries covered with cheese sauce, savoury bits and fresh herbs.', '220.00', NULL, '1', '0', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_items` VALUES ('23', '10', 'Chicken Wrap', 'Soft tortilla filled with chicken, crunchy vegetables and creamy sauce.', '290.00', NULL, '1', '0', '2026-09-25 13:42:35', '2026-09-25 13:42:35');
INSERT INTO `menu_items` VALUES ('27', '13', 'QA Temp Noodle Bowl Revised 6925', 'Updated temporary test dish', '223.25', '34626b9ca415c526787d934c15e81232.jpg', '1', '1', '2026-09-25 19:11:36', '2026-09-25 19:12:33');

-- Table structure for table `restaurant_settings`
DROP TABLE IF EXISTS `restaurant_settings`;
CREATE TABLE `restaurant_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(100) NOT NULL DEFAULT '4 TO 9',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `opening_time` time DEFAULT '10:00:00',
  `closing_time` time DEFAULT '23:00:00',
  `booking_duration` int(11) DEFAULT 90 COMMENT 'Duration in minutes',
  `max_booking_guests` int(11) DEFAULT 20,
  `allow_online_booking` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `restaurant_settings`
INSERT INTO `restaurant_settings` VALUES ('1', '4 TO 9', '+91 98765 43210', 'info@4to9.com', '123 Restaurant Street, City, State', '10:00:00', '23:00:00', '90', '20', '1', '2026-09-24 19:57:31', '2026-09-24 19:57:31');

-- Table structure for table `restaurant_tables`
DROP TABLE IF EXISTS `restaurant_tables`;
CREATE TABLE `restaurant_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(10) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `restaurant_tables`
INSERT INTO `restaurant_tables` VALUES ('1', '01', 'Window Table', '2', 'First Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('2', '02', 'Corner Table', '4', 'First Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('3', '03', 'Center Table', '4', 'First Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('4', '04', 'Window Table', '6', 'First Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('5', '05', 'Private Area', '8', 'Second Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('6', '06', 'Garden Table', '4', 'Outdoor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('7', '07', 'Terrace Table', '2', 'Outdoor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('8', '08', 'VIP Room', '10', 'Second Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('9', '09', 'Family Table', '6', 'First Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');
INSERT INTO `restaurant_tables` VALUES ('10', '10', 'Bar Table', '2', 'Ground Floor', 'active', '2026-09-24 19:57:31', '2026-09-24 19:57:31');

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('1', 'Admin User', 'admin@4to9.com', '9876543210', '$2y$10$aXb1v83WMqEKU/30QfUd9OQ6PLFproDDP1AkQpxXe1XsKBhJnWewG', 'admin', 'active', '2026-09-24 19:57:31', '2026-09-25 13:57:42');
INSERT INTO `users` VALUES ('3', 'Saurav Sitoula', 'saurav123sitoula@gmail.com', '9824016761', '$2y$10$YVR/aS91eBU6Oqw/WKcykuutQ13xg9XxuDAPqc4lh3jEvCfIcTHGO', 'customer', 'active', '2026-09-25 13:22:03', '2026-09-25 13:22:03');
INSERT INTO `users` VALUES ('4', 'QA Customer', 'qa.admin.audit.07b2733c@example.invalid', '9876543210', 'unused', 'customer', 'active', '2026-09-25 19:11:29', '2026-09-25 19:11:29');

SET FOREIGN_KEY_CHECKS = 1;
