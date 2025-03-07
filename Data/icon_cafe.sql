-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 12:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `icon_cafe`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `price`, `image_path`, `category`) VALUES
(1, 'Cappuccino coffee', 130.00, 'img/cappucino.jpg', 'Coffees'),
(2, 'Chocolate coffee', 190.00, 'img/background.png', 'Coffees'),
(3, 'Cold coffee with ice-cream', 150.00, 'img/cold_coffee_with_ice_cream.webp', 'Coffees'),
(4, 'White chocolate mocha', 170.00, 'img/white_chocolate_mocha.jpg', 'Coffees'),
(5, 'Keser Coffee', 180.00, 'img/keser coffee.jfif', 'Coffees'),
(6, 'Rose cold coffee', 160.00, 'img/rose cold coffee.jpg', 'Coffees'),
(7, 'Masala Chai', 30.00, 'img/masala chai.jpg', 'Coffees'),
(8, 'Green tea', 50.00, 'img/Green tea.jpg', 'Coffees'),
(9, 'Grilled Sandwich', 80.00, 'img/grilled-sandwich.webp', 'Sandwiches'),
(10, 'Cheese Sandwich', 100.00, 'img/cheese sendwiches.jpg', 'Sandwiches'),
(11, 'Veg Burger', 100.00, 'img/veg burger.jpg', 'Burgers & Fries'),
(12, 'Cheese Burger', 120.00, 'img/veg cheese burger.jfif', 'Burgers & Fries'),
(13, 'Chicken Burger', 150.00, 'img/Chiken burger.webp', 'Burgers & Fries'),
(14, 'Double Cheese Burger', 180.00, 'img/double cheese burger.jpg', 'Burgers & Fries'),
(15, 'French Fries', 90.00, 'img/french fries.jfif', 'Burgers & Fries'),
(16, 'Cappuccino', 130.00, 'img/cappucino.jpg', 'Recommends'),
(17, 'Brownie', 100.00, 'img/brownie.jpg', 'Recommends'),
(18, 'Avocado Toast', 190.00, 'img/avocado_toast.jpg', 'Recommends'),
(19, 'Alfredo Pasta', 130.00, 'img/alfredo_pasta.jpg', 'Recommends'),
(20, 'White Chocolate Mocha', 90.00, 'img/white_chocolate_mocha.jpg', 'Recommends');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `secondname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `secondname`, `lastname`, `email`, `password`, `created_at`) VALUES
(4, 'Rohil', 'Narendrabhai', 'Lad', 'rohillad1305@gmail.com', '$2y$10$6Dhbuh/PK0giPf9VMqMECucR166F4MELBmESp6yvMi4WSa4ssQPz2', '2024-11-18 14:09:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
