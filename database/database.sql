-- Database Schema for Online Indoor Plants E-Commerce System
CREATE DATABASE IF NOT EXISTS `online_indoor_plants` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `online_indoor_plants`;

-- Drop tables if they exist (in proper reverse foreign key order)
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `product_variations`;
DROP TABLE IF EXISTS `pot_colours`;
DROP TABLE IF EXISTS `pot_types`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `province` VARCHAR(100) NULL,
  `postal_code` VARCHAR(20) NULL,
  `phone` VARCHAR(30) NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE `categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Products Table
CREATE TABLE `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `size` ENUM('Small', 'Medium', 'Large') DEFAULT 'Medium',
  `light_requirement` VARCHAR(100) DEFAULT 'Indirect Sunlight',
  `watering_requirement` VARCHAR(100) DEFAULT 'Once a week',
  `suitable_location` VARCHAR(100) DEFAULT 'Living Room & Office',
  `care_level` ENUM('Easy', 'Moderate', 'Expert') DEFAULT 'Easy',
  `stock_quantity` INT DEFAULT 50,
  `main_image` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Pot Types Table
CREATE TABLE `pot_types` (
  `pot_type_id` INT AUTO_INCREMENT PRIMARY KEY,
  `pot_type_name` VARCHAR(50) NOT NULL UNIQUE,
  `image` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Pot Colours Table
CREATE TABLE `pot_colours` (
  `pot_colour_id` INT AUTO_INCREMENT PRIMARY KEY,
  `colour_name` VARCHAR(50) NOT NULL UNIQUE,
  `hex_code` VARCHAR(10) DEFAULT '#FFFFFF'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Product Variations Table (Plant + Pot Type + Pot Colour)
CREATE TABLE `product_variations` (
  `variation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `pot_type_id` INT NOT NULL,
  `pot_colour_id` INT NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `additional_price` DECIMAL(10, 2) DEFAULT 0.00,
  `stock_quantity` INT DEFAULT 20,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`pot_type_id`) REFERENCES `pot_types`(`pot_type_id`) ON DELETE CASCADE,
  FOREIGN KEY (`pot_colour_id`) REFERENCES `pot_colours`(`pot_colour_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_variation` (`product_id`, `pot_type_id`, `pot_colour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Cart Table
CREATE TABLE `cart` (
  `cart_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `session_id` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Cart Items Table
CREATE TABLE `cart_items` (
  `cart_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `variation_id` INT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`cart_id`) REFERENCES `cart`(`cart_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`variation_id`) REFERENCES `product_variations`(`variation_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8b. Wishlist Table
CREATE TABLE `wishlist` (
  `wishlist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Orders Table
CREATE TABLE `orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `delivery_name` VARCHAR(100) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `payment_method` ENUM('Cash on Delivery', 'Card Payment') DEFAULT 'Cash on Delivery',
  `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
  `order_status` ENUM('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Order Items Table
CREATE TABLE `order_items` (
  `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `variation_id` INT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`variation_id`) REFERENCES `product_variations`(`variation_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- SAMPLE SEED DATA
-- =======================================================

-- Default Users (Password for both is: password123)
-- Hash generated using password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `address`, `city`, `province`, `postal_code`, `phone`, `role`) VALUES
(1, 'Admin User', 'admin@indoorplants.com', '$2y$10$w6B.jG/K850bZ43r6K0s2uP5D.b2gB.V5eDq6X.7C/hT6c3.E5zly', '123 Botanical Avenue', 'Colombo', 'Western', '00100', '+94 77 123 4567', 'admin'),
(2, 'Sarah Jenkins', 'sarah@example.com', '$2y$10$w6B.jG/K850bZ43r6K0s2uP5D.b2gB.V5eDq6X.7C/hT6c3.E5zly', '45 Green Garden Lane', 'Kandy', 'Central', '20000', '+94 71 987 6543', 'customer');

INSERT INTO `pot_types` (`pot_type_id`, `pot_type_name`, `image`) VALUES
(1, 'Ceramic Pot', 'image/pots/pot_ceramic.jpg'),
(2, 'Terracotta Pot', 'image/pots/pot_terracotta.jpg'),
(3, 'Plastic Pot', 'image/pots/pot_plastic.jpg'),
(4, 'Hanging Pot', 'image/pots/pot_hanging.jpg');

INSERT INTO `pot_colours` (`pot_colour_id`, `colour_name`, `hex_code`) VALUES
(1, 'White', '#FFFFFF'),
(2, 'Black', '#222222'),
(3, 'Brown', '#795548'),
(4, 'Green', '#2E7D32'),
(5, 'Beige', '#D7CCC8');

-- Categories
INSERT INTO `categories` (`category_id`, `category_name`, `description`, `image`) VALUES
(1, 'Air Purifying Plants', 'Plants that naturally clean indoor air and absorb toxins to improve oxygen quality.', 'image/products/snake_plant.jpg'),
(2, 'Low Light Plants', 'Thrive in low-light environments like apartments, offices, and cozy bedrooms.', 'image/products/zz_plant.jpg'),
(3, 'Desk & Table Plants', 'Compact indoor greenery ideal for office desks, dining tables, and window sills.', 'image/products/jade_plant.jpg'),
(4, 'Flowering Indoor Plants', 'Vibrant indoor flora bringing colorful blooms and sweet scent into living spaces.', 'image/products/peace_lily.jpg');

-- Products (Minimum 5 per category = 20 products total)

-- Category 1: Air Purifying Plants
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `size`, `light_requirement`, `watering_requirement`, `suitable_location`, `care_level`, `stock_quantity`, `main_image`, `is_featured`) VALUES
(1, 1, 'Snake Plant (Sansevieria)', 'Extremely resilient air-purifying plant with architectural sword-like leaves that releases oxygen overnight.', 1500.00, 'Medium', 'Low to Bright Light', 'Every 2-3 weeks', 'Living Room & Bedroom', 'Easy', 45, 'image/products/snake_plant.jpg', 1),
(2, 1, 'Spider Plant (Chlorophytum)', 'Fast-growing indoor plant known for producing graceful arching stems and baby plantlets. Great air cleaner.', 1100.00, 'Medium', 'Bright Indirect Light', 'Once a week', 'Study Desk & Bookshelf', 'Easy', 50, 'image/products/spider_plant.jpg', 1),
(3, 1, 'Peace Lily (Spathiphyllum)', 'Elegant dark green leaves paired with pristine white blooms. Removes VOCs and purifies air indoors.', 2400.00, 'Large', 'Medium Indirect Light', 'Twice a week', 'Living Room & Reception', 'Moderate', 30, 'image/products/peace_lily.jpg', 1),
(4, 1, 'Areca Palm', 'Feathery tropical palm fronds that naturally humidify the air and add lush resort vibes to rooms.', 2800.00, 'Large', 'Bright Indirect Light', 'Twice a week', 'Spacious Living Room', 'Moderate', 25, 'image/products/areca_palm.jpg', 0),
(5, 1, 'Aloe Vera', 'Popular succulent famed for its soothing gel leaves and remarkable air detoxifying powers.', 1250.00, 'Small', 'Direct Sunlight', 'Every 3 weeks', 'Sunny Window Sill', 'Easy', 60, 'image/products/aloe_vera.jpg', 0),

-- Category 2: Low Light Plants
(6, 2, 'ZZ Plant (Zamioculcas)', 'Waxy, glossy leaves with incredible drought tolerance. Performs remarkably well in dark corners.', 2000.00, 'Medium', 'Low Light to Shadow', 'Once a month', 'Bedrooms & Dark Corners', 'Easy', 40, 'image/products/zz_plant.jpg', 1),
(7, 2, 'Cast Iron Plant', 'Indestructible indoor plant with deep green foliage that survives neglect, shade, and dry conditions.', 2200.00, 'Medium', 'Low Light', 'Every 2 weeks', 'Office Hallway & Entry', 'Easy', 20, 'image/products/cast_iron_plant.jpg', 0),
(8, 2, 'Golden Pothos', 'Classic trailing vine with heart-shaped yellow-variegated leaves that thrives practically anywhere.', 1400.00, 'Small', 'Low to Bright Indirect', 'Every 1-2 weeks', 'Hanging Basket & Shelf', 'Easy', 75, 'image/products/pothos.jpg', 1),
(9, 2, 'Parlor Palm', 'Compact mini palm suitable for low-light rooms with elegant fan-like fronds.', 1800.00, 'Medium', 'Medium to Low Light', 'Once a week', 'Dining Table & Desk', 'Easy', 35, 'image/products/parlor_palm.jpg', 0),
(10, 2, 'Philodendron Green', 'Striking heart-leaf indoor plant with rich green foliage. Highly adaptable to artificial light.', 1900.00, 'Medium', 'Low to Medium Light', 'Every 1-2 weeks', 'Workstation & Office Desk', 'Easy', 30, 'image/products/philodendron.jpg', 0),

-- Category 3: Desk & Table Plants
(11, 3, 'Money Plant (Pachira)', 'Classic tabletop plant with glossy leaves and braided stem, symbol of good luck and prosperity.', 1100.00, 'Small', 'Bright Indirect Light', 'Every 2 weeks', 'Office Desk & Cash Counter', 'Easy', 55, 'image/products/money_plant.jpg', 1),
(12, 3, 'Lucky Bamboo', 'Charming desk plant arranged in green stalks bringing positive energy and harmony into rooms.', 1700.00, 'Small', 'Low to Medium Light', 'Keep roots in fresh water', 'Study Table & Coffee Table', 'Easy', 40, 'image/products/lucky_bamboo.jpg', 0),
(13, 3, 'Jade Plant (Crassula Ovata)', 'Resilient succulent with thick jade-green oval leaves and woody stems.', 1350.00, 'Small', 'Direct Sunlight', 'Every 3 weeks', 'Window Sill & Desk', 'Easy', 50, 'image/products/jade_plant.jpg', 1),
(14, 3, 'Peperomia Watermelon', 'Eye-catching desk plant with unique leaf patterns mimicking miniature watermelon rinds.', 1600.00, 'Small', 'Medium Indirect Light', 'Once a week', 'Side Table & Shelf', 'Moderate', 45, 'image/products/peperomia.jpg', 0),
(15, 3, 'Succulent Trio Collection', 'Set of 3 assorted resilient mini succulents planted in stylish ceramic pots for desktop décor.', 2100.00, 'Small', 'Direct Sunlight', 'Every 3 weeks', 'Computer Desk & Shelf', 'Easy', 25, 'image/products/succulent_trio.jpg', 0),

-- Category 4: Flowering Indoor Plants
(16, 4, 'Phalaenopsis Orchid', 'Breathtaking flowering orchid with long-lasting pink and white tropical blooms.', 2900.00, 'Medium', 'Bright Indirect Light', 'Once a week', 'Dining Table & Console', 'Moderate', 30, 'image/products/orchid.jpg', 1),
(17, 4, 'Anthurium (Flamingo Flower)', 'Glossy heart-shaped leaves crowned with vibrant red wax flowers that bloom year-round.', 2650.00, 'Medium', 'Bright Indirect Light', 'Once a week', 'Living Room Centerpiece', 'Moderate', 35, 'image/products/anthurium.jpg', 1),
(18, 4, 'African Violet', 'Compact flowering classic boasting velvety fuzzy leaves and violet-blue floral clusters.', 1450.00, 'Small', 'Bright Indirect Light', 'Bottom watering weekly', 'Bedroom Window & Shelf', 'Moderate', 40, 'image/products/african_violet.jpg', 0),
(19, 4, 'Christmas Cactus (Schlumbergera)', 'Arching segmented succulent stems that erupt into brilliant fuchsia blossoms in winter.', 1750.00, 'Medium', 'Bright Indirect Light', 'Every 1-2 weeks', 'Veranda & Covered Patio', 'Easy', 28, 'image/products/christmas_cactus.jpg', 0),
(20, 4, 'Rex Begonia', 'Foliage and flower star featuring metallic swirl leaves and soft pink blossoms.', 2250.00, 'Medium', 'Medium Light', 'Once a week', 'Side Table & Reading Nook', 'Moderate', 22, 'image/products/begonia.jpg', 0);

-- Product Variations (Plant + Pot Type + Pot Colour)
-- Generating entries for products with Ceramic/Terracotta/Plastic + White/Black/Brown variations
INSERT INTO `product_variations` (`product_id`, `pot_type_id`, `pot_colour_id`, `image`, `additional_price`, `stock_quantity`) VALUES
-- Snake Plant (Product 1)
(1, 1, 1, 'image/product_variations/snake_ceramic_white.jpg', 0.00, 20),
(1, 1, 2, 'image/product_variations/snake_ceramic_black.jpg', 250.00, 15),
(1, 1, 3, 'image/product_variations/snake_ceramic_brown.jpg', 200.00, 10),
(1, 2, 3, 'image/product_variations/snake_terracotta_brown.jpg', -300.00, 18),
(1, 2, 1, 'image/product_variations/snake_terracotta_white.jpg', -200.00, 12),
(1, 3, 2, 'image/product_variations/snake_plastic_black.jpg', -450.00, 25),

-- Spider Plant (Product 2)
(2, 1, 1, 'image/product_variations/spider_ceramic_white.jpg', 0.00, 20),
(2, 1, 2, 'image/product_variations/spider_ceramic_black.jpg', 200.00, 15),
(2, 2, 3, 'image/product_variations/spider_terracotta_brown.jpg', -200.00, 20),
(2, 3, 1, 'image/product_variations/spider_plastic_white.jpg', -350.00, 30),

-- Peace Lily (Product 3)
(3, 1, 1, 'image/product_variations/peace_ceramic_white.jpg', 0.00, 15),
(3, 1, 2, 'image/product_variations/peace_ceramic_black.jpg', 250.00, 10),
(3, 2, 3, 'image/product_variations/peace_terracotta_brown.jpg', -300.00, 12),

-- ZZ Plant (Product 6)
(6, 1, 1, 'image/product_variations/zz_ceramic_white.jpg', 0.00, 15),
(6, 1, 2, 'image/product_variations/zz_ceramic_black.jpg', 300.00, 12),
(6, 2, 3, 'image/product_variations/zz_terracotta_brown.jpg', -250.00, 10),

-- Jade Plant (Product 11)
(11, 1, 1, 'image/product_variations/jade_ceramic_white.jpg', 0.00, 20),
(11, 2, 3, 'image/product_variations/jade_terracotta_brown.jpg', -200.00, 18),
(11, 3, 2, 'image/product_variations/jade_plastic_black.jpg', -400.00, 22),

-- Phalaenopsis Orchid (Product 16)
(16, 1, 1, 'image/product_variations/orchid_ceramic_white.jpg', 0.00, 15),
(16, 1, 2, 'image/product_variations/orchid_ceramic_black.jpg', 300.00, 10),
(16, 2, 3, 'image/product_variations/orchid_terracotta_brown.jpg', -250.00, 12),

-- Anthurium (Product 17)
(17, 1, 1, 'image/product_variations/anthurium_ceramic_white.jpg', 0.00, 15),
(17, 1, 2, 'image/product_variations/anthurium_ceramic_black.jpg', 250.00, 12),
(17, 2, 3, 'image/product_variations/anthurium_terracotta_brown.jpg', -300.00, 10);

-- Sample Order
INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `delivery_name`, `delivery_address`, `city`, `province`, `postal_code`, `phone`, `payment_method`, `payment_status`, `order_status`) VALUES
(1, 2, 3900.00, 'Sarah Jenkins', '45 Green Garden Lane', 'Kandy', 'Central', '20000', '+94 71 987 6543', 'Cash on Delivery', 'Pending', 'Confirmed');

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `variation_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 1, 1500.00),
(2, 1, 3, 7, 1, 2400.00);
