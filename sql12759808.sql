-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 09:18 PM
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
-- Database: `sql12759808`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_no` int(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `order_name` varchar(255) NOT NULL,
  `order_name2` varchar(255) NOT NULL,
  `order_quantity` int(255) NOT NULL,
  `order_size` varchar(255) NOT NULL,
  `basePrice` int(255) NOT NULL,
  `order_price` int(255) NOT NULL,
  `order_addons` varchar(255) NOT NULL,
  `order_addons_price` int(255) NOT NULL,
  `order_image` varchar(255) NOT NULL,
  `order_addons2` varchar(255) NOT NULL,
  `order_addons_price2` int(255) NOT NULL,
  `order_addons3` varchar(255) NOT NULL,
  `order_addons_price3` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos-menu`
--

CREATE TABLE `pos-menu` (
  `menu_no` int(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `title1` varchar(255) NOT NULL,
  `title2` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `label2` varchar(255) NOT NULL,
  `price1` int(255) NOT NULL,
  `price2` int(255) NOT NULL,
  `price3` int(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `qty` int(255) NOT NULL,
  `stock_date` varchar(255) NOT NULL,
  `stock_time` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos-menu`
--

INSERT INTO `pos-menu` (`menu_no`, `code`, `title1`, `title2`, `label`, `label2`, `price1`, `price2`, `price3`, `image`, `qty`, `stock_date`, `stock_time`) VALUES
(1, 'A001', 'Peperoni', 'Pizza', 'Food', 'Pizza', 150, 300, 900, 'pizza.jpg', 0, '11/17/2024', '07:00:38 PM'),
(2, 'A002', 'Cheese', 'Burger', 'Food', 'Burger', 100, 180, 250, 'burger.jpg', 0, '11/18/2024', '05:36:05 PM'),
(3, 'B001', 'Americano', 'Coffee', 'Beverages', 'Coffee', 80, 120, 150, 'americano.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(4, 'B002', 'Cappuccino', 'Coffee', 'Beverages', 'Coffee', 80, 120, 150, 'cappuccino.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(5, 'B003', 'Spanish', 'Latte', 'Beverages', 'Coffee', 80, 120, 150, 'latte.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(6, 'B004', 'Hazelnut', 'Latte', 'Beverages', 'Coffee', 80, 120, 150, 'hazelnut-latte.jpg', 0, '2024-11-18', '17:36:27'),
(7, 'U001', 'Sisig', 'Beef', 'Food', 'Ulam', 250, 450, 900, 'sisig.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(8, 'P001', 'Carbonara', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'carbonara.jpg', 0, '2024-11-19', '00:37:01'),
(9, 'P002', 'Spaghetti', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'spagheti.jpg', 0, '11/18/2024', '05:35:28 PM'),
(10, 'P003', 'Miki', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'pansit-miki.jpg', 12, '2024-11-18', '05:33:41 PM'),
(11, 'D001', 'Buko Pandan', 'Salad', 'Food', 'Dessert', 50, 90, 150, 'buko-pandan.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(32, 'U002', 'Chicken', 'Adobo', 'Food', 'Ulam', 150, 300, 500, 'd741f28eef20eaef73b8d93df2ce555c.jpg', 0, '2024-11-18', '16:41:11'),
(35, 'D002', 'Cassava ', 'Cake', 'Food', 'Dessert', 80, 125, 250, 'cassava-cake.jpg', 10, '2024-11-25', '15:54:10'),
(36, 'U003', 'Pork', 'Sinigang', 'Food', 'Ulam', 150, 250, 500, 'pork-sinigang.jpg', 8, '2024-11-25', '16:12:42'),
(37, 'U004', 'Chicken', 'Inasal', 'Food', 'Ulam', 120, 250, 550, 'chicken-inasal.jpg', -4, '2024-11-25', '16:14:27'),
(38, 'U004', 'Laing', 'Authentic', 'Food', 'Ulam', 80, 150, 250, 'laing.jpg', -4, '2024-11-25', '16:17:38'),
(39, 'D003', 'Halo-Halo', 'Special', 'Food', 'Dessert', 85, 130, 200, 'halo-halo.jpg', 0, '2024-11-25', '16:19:15'),
(40, 'T001', 'Strawberry', 'Fruit Tea', 'Beverages', 'Tea', 85, 120, 150, 'strawberry-fruit-tea.jpg', 3, '2024-11-25', '16:22:21'),
(41, 'T002', 'Blueberry', 'Fruit Tea', 'Beverages', 'Tea', 85, 120, 150, 'blueberry-fruit-tea.jpg', 10, '2024-11-25', '16:24:18'),
(42, 'T003', 'Green Apple', 'Fruit Tea', 'Beverages', 'Tea', 80, 120, 150, 'greenapple-fruit-tea.jpg', 0, '2024-11-25', '16:26:01'),
(43, 'S001', 'Coke', 'Soda', 'Beverages', 'Soda', 30, 75, 120, 'coke.jpg', 10, '2024-11-25', '16:28:31'),
(44, 'R001', 'Plain', 'Rice', 'Food', 'Rice', 20, 150, 300, 'plain-rice.jpg', 14, '2024-11-25', '16:41:04'),
(45, 'R002', 'Java', 'Rice', 'Food', 'Rice', 20, 120, 300, 'java-rice.jpg', 13, '2024-11-25', '16:41:58'),
(46, 'R003', 'Fried', 'Rice', 'Food', 'Rice', 30, 150, 400, 'fried-rice.jpg', 14, '2024-11-25', '16:42:58'),
(47, 'T003', 'Mango', 'Fruit Tea', 'Beverages', 'Tea', 80, 120, 150, 'mango-fruit-tea.jpg', 0, '2024-11-25', '23:45:33');

-- --------------------------------------------------------

--
-- Table structure for table `que_orders`
--

CREATE TABLE `que_orders` (
  `que_order_no` int(255) NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `items_ordered` varchar(999) NOT NULL,
  `total_amount` int(255) NOT NULL,
  `amount_paid` int(255) NOT NULL,
  `amount_change` int(255) NOT NULL,
  `order_take` varchar(255) NOT NULL,
  `table_number` int(255) NOT NULL,
  `order_status` varchar(255) NOT NULL,
  `waiter_name` varchar(100) DEFAULT NULL,
  `waiter_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `que_orders`
--

INSERT INTO `que_orders` (`que_order_no`, `receipt_number`, `date`, `time`, `items_ordered`, `total_amount`, `amount_paid`, `amount_change`, `order_take`, `table_number`, `order_status`, `waiter_name`, `waiter_code`) VALUES
(65, '196', '4/8/2025', '8:24:31 PM', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"180\",\"delivered\":\"1\"}]', 180, 0, 0, 'Dine In', 0, 'done', NULL, NULL),
(72, '197', '4/8/2025', '10:04:36 PM', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"80\",\"delivered\":\"1\"}]', 80, 0, 0, 'Dine In', 0, 'done', NULL, NULL),
(92, '222', '4/9/2025', '3:01:57 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"0.25L\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\",\"delivered\":\"1\",\"receipt_number\":\"222\"}]', 120, 0, 0, 'Dine In', 5, 'done', 'sasd asdasd', 'SA7393'),
(93, '222', '4/9/2025', '3:02:18 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"0.25L\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\",\"delivered\":\"1\",\"receipt_number\":\"222\"}]', 180, 0, 0, 'Dine In', 5, 'done', 'sasd asdasd', 'SA7393'),
(94, '222', '4/9/2025', '3:02:57 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"0.25L\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\",\"delivered\":\"1\",\"receipt_number\":\"222\"}]', 700, 0, 0, 'Dine In', 4, 'pending', 'sasd asdasd', 'SA7393'),
(95, '222', '4/9/2025', '3:03:32 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"0.25L\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\",\"delivered\":\"1\",\"receipt_number\":\"222\"}]', 205, 0, 0, 'Dine In', 4, 'pending', 'sasd asdasd', 'SA7393'),
(96, '222', '4/9/2025', '3:09:28 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"0.25L\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\",\"delivered\":\"1\",\"receipt_number\":\"222\"}]', 85, 0, 0, 'Dine In', 6, 'pending', 'sasd asdasd', 'SA7393'),
(97, '222', '4/9/2025', '3:12:21 AM', '[{\"order_name\":\"Coke\",\"order_name2\":\"Soda\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"30\",\"receipt_number\":222},{\"order_name\":\"Pork\",\"order_name2\":\"Sinigang\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"receipt_number\":222}]', 180, 0, 0, 'Take Out', 5, 'pending', 'sasd asdasd', 'SA7393');

-- --------------------------------------------------------

--
-- Table structure for table `remit_returns`
--

CREATE TABLE `remit_returns` (
  `return_id` int(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `total_sales` varchar(255) NOT NULL,
  `return_date` varchar(255) NOT NULL,
  `return_time` varchar(255) NOT NULL,
  `return_validation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remit_sales`
--

CREATE TABLE `remit_sales` (
  `remit_id` int(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `total_sales` varchar(255) NOT NULL,
  `remit_date` varchar(255) NOT NULL,
  `remit_time` varchar(255) NOT NULL,
  `remit_shortage` int(255) NOT NULL,
  `remit_validation` varchar(255) NOT NULL,
  `cashier_shift` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reserve_table`
--

CREATE TABLE `reserve_table` (
  `reserve_no` int(255) NOT NULL,
  `reserve_date` varchar(255) NOT NULL,
  `reserve_time` varchar(255) NOT NULL,
  `table_number` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return-orders`
--

CREATE TABLE `return-orders` (
  `return_id` int(255) NOT NULL,
  `receipt_number` int(255) NOT NULL,
  `return_date` varchar(255) NOT NULL,
  `return_time` varchar(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `items_ordered` varchar(255) NOT NULL,
  `total_amount` int(255) NOT NULL,
  `amount_paid` int(255) NOT NULL,
  `amount_change` int(255) NOT NULL,
  `order_take` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return-orders`
--

INSERT INTO `return-orders` (`return_id`, `receipt_number`, `return_date`, `return_time`, `cashier_name`, `items_ordered`, `total_amount`, `amount_paid`, `amount_change`, `order_take`) VALUES
(4, 101, '11/17/2024', '4:59:10 PM', 'Mike', '[{\"order_name\":\"Miki\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"150\"}]', 150, 500, 350, 'Take Out'),
(5, 100, '11/17/2024', '4:58:51 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"100\"}]', 100, 500, 400, 'Take Out'),
(6, 102, '11/17/2024', '4:59:31 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"1\"}]', 165, 500, 335, 'Take Out'),
(7, 103, '11/17/2024', '5:13:41 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"115\"}]', 115, 500, 385, 'Take Out'),
(8, 105, '11/17/2024', '5:25:39 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"10\"}]', 115, 500, 385, 'Take Out'),
(9, 105, '11/17/2024', '9:51:40 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(10, 105, '11/17/2024', '9:52:46 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(11, 105, '11/17/2024', '10:01:22 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(12, 105, '11/18/2024', '3:37:27 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 500, 350, 'Take Out'),
(13, 119, '11/18/2024', '6:30:27 PM', 'Mike', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Peperoni\",\"order_name2\":\"Pizza\",\"order_quantity\":\"x1\",\"order_size\":', 380, 500, 120, 'Dine In'),
(14, 1, '11/18/2024', '6:29:12 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"10\"}]', 195, 500, 305, 'Dine In'),
(15, 1, '11/18/2024', '6:53:18 PM', 'Mike', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\"', 380, 500, 120, 'Take Out'),
(16, 122, '11/18/2024', '6:57:02 PM', '', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 500, 350, 'Take Out'),
(17, 121, '11/18/2024', '6:54:25 PM', '', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 200, 50, 'Take Out'),
(18, 127, '11/19/2024', '3:48:51 PM', 'sample', '[{\"order_name\":\"Spanish\",\"order_name2\":\"Latte\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons\":\"Sugar\",\"order_addons_price\":\"5\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 125, 200, 75, 'Take Out');

-- --------------------------------------------------------

--
-- Table structure for table `total_sales`
--

CREATE TABLE `total_sales` (
  `total_order` int(255) NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `items_ordered` varchar(999) NOT NULL,
  `total_amount` int(255) NOT NULL,
  `amount_paid` int(255) NOT NULL,
  `amount_change` int(255) NOT NULL,
  `order_take` varchar(255) NOT NULL,
  `table_number` int(255) NOT NULL,
  `order_status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_sales`
--

INSERT INTO `total_sales` (`total_order`, `receipt_number`, `date`, `time`, `cashier_name`, `items_ordered`, `total_amount`, `amount_paid`, `amount_change`, `order_take`, `table_number`, `order_status`) VALUES
(184, '58', '2/10/2025', '11:55:38 PM', 'kape', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(185, '58', '2/10/2025', '11:57:10 PM', 'kape', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(186, '58', '2/10/2025', '11:57:26 PM', 'kape', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(187, '58', '2/10/2025', '11:58:15 PM', 'kape', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(188, '58', '2/10/2025', '11:59:13 PM', 'kape', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(189, '60', '4/8/2025', '7:47:32 PM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 500, 350, 'Dine In', 0, 'pending'),
(190, '60', '4/8/2025', '7:47:50 PM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 20, 0, 'Dine In', 0, 'pending'),
(191, '60', '4/8/2025', '7:50:34 PM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 20, 0, 'Dine In', 0, 'pending'),
(192, '61', '4/8/2025', '7:50:46 PM', 'sasd', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"80\"}]', 80, 20, 0, 'Dine In', 0, 'pending'),
(193, '59', '4/8/2025', '7:52:42 PM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 50, 0, 'Dine In', 0, 'pending'),
(194, '62', '4/8/2025', '8:05:49 PM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Family\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"500\"}]', 515, 2000, 1403, 'Dine In', 0, 'pending'),
(195, '63', '4/8/2025', '8:06:10 PM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"180\"}]', 180, 2000, 1778, 'Dine In', 0, 'pending'),
(196, '70', '4/8/2025', '8:52:56 PM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\",\"order_addons\":null,\"order_addons2\":null,\"order_addons3\":null,\"total_amount\":\"100\"}]', 100, 233, 101, 'Dine In', 6, 'pending'),
(197, '74', '4/9/2025', '12:05:08 AM', 'sasd', '[{\"order_name\":\"Peperoni\",\"order_name2\":\"Pizza\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 200, 50, 'Dine In', 0, 'pending'),
(198, '75', '4/9/2025', '12:57:39 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 233, 83, 'Dine In', 0, 'pending'),
(199, '73', '4/9/2025', '12:57:49 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 233, 133, 'Dine In', 0, 'pending'),
(200, '69', '4/9/2025', '12:57:59 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 233, 133, 'Dine In', 0, 'pending'),
(201, '64', '4/9/2025', '2:10:43 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 1000, 900, 'Dine In', 0, 'pending'),
(202, '66', '4/9/2025', '2:10:53 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 12345, 12245, 'Dine In', 0, 'pending'),
(203, '67', '4/9/2025', '2:11:01 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"100\"}]', 100, 12345, 12245, 'Dine In', 0, 'pending'),
(204, '71', '4/9/2025', '2:11:09 AM', 'sasd', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"80\"}]', 80, 123, 43, 'Dine In', 0, 'pending'),
(205, '82', '4/9/2025', '2:11:20 AM', 'sasd', '[{\"order_name\":\"Chicken\",\"order_name2\":\"Inasal\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\"}]', 120, 1233, 1113, 'Dine In', 0, 'pending'),
(206, '68', '4/9/2025', '2:11:31 AM', 'sasd', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"180\"}]', 180, 1233, 1053, 'Dine In', 0, 'pending'),
(207, '76', '4/9/2025', '2:11:55 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 1234, 1084, 'Dine In', 0, 'pending'),
(208, '77', '4/9/2025', '2:12:06 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 12345, 12195, 'Dine In', 0, 'pending'),
(209, '86', '4/9/2025', '2:36:14 AM', 'sasd', '[{\"order_name\":\"Strawberry\",\"order_name2\":\"Fruit Tea\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"85\"},{\"order_name\":\"Strawberry\",\"order_name2\":\"Fruit Tea\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"120\"}]', 205, 23320, 23115, 'Dine In', 0, 'pending'),
(210, '89', '4/9/2025', '2:58:15 AM', 'sasd', '[{\"order_name\":\"Strawberry\",\"order_name2\":\"Fruit Tea\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"85\",\"delivered\":\"1\"}]', 85, 21102, 21017, 'Dine In', 0, 'pending'),
(211, '90', '4/9/2025', '2:58:55 AM', 'sasd', '[{\"order_name\":\"Strawberry\",\"order_name2\":\"Fruit Tea\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"85\",\"delivered\":\"1\"}]', 85, 12345, 12260, 'Dine In', 0, 'pending'),
(212, '87', '4/9/2025', '2:59:04 AM', 'sasd', '[{\"order_name\":\"Miki\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Bacon\",\"order_addons_price\":\"20\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"}]', 85, 1234, 1149, 'Dine In', 0, 'pending'),
(213, '88', '4/9/2025', '2:59:13 AM', 'sasd', '[{\"order_name\":\"Miki\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Bacon\",\"order_addons_price\":\"20\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"}]', 150, 12345, 12195, 'Dine In', 0, 'pending'),
(214, '91', '4/9/2025', '2:59:23 AM', 'sasd', '[{\"order_name\":\"Halo-Halo\",\"order_name2\":\"Special\",\"order_quantity\":\"x3\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"85\"}]', 255, 12345, 12090, 'Dine In', 0, 'pending'),
(215, '79', '4/9/2025', '2:59:33 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 12345, 12195, 'Dine In', 0, 'pending'),
(216, '78', '4/9/2025', '2:59:41 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 300, 12345, 12045, 'Dine In', 0, 'pending'),
(217, '80', '4/9/2025', '2:59:52 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 515, 12345, 11830, 'Dine In', 0, 'pending'),
(218, '81', '4/9/2025', '3:00:02 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 150, 12345, 12195, 'Dine In', 0, 'pending'),
(219, '83', '4/9/2025', '3:00:13 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 120, 12345, 12225, 'Dine In', 0, 'pending'),
(220, '84', '4/9/2025', '3:00:40 AM', 'sasd', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\"}]', 120, 2345, 2225, 'Dine In', 0, 'pending'),
(221, '85', '4/9/2025', '3:00:51 AM', 'sasd', '[{\"order_name\":\"Miki\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Bacon\",\"order_addons_price\":\"20\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"basePrice\":\"150\",\"delivered\":\"1\"}]', 170, 12345, 12175, 'Dine In', 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `user-staff`
--

CREATE TABLE `user-staff` (
  `staff_no` int(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contactNumber` int(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `staff_token` int(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `waiter_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user-staff`
--

INSERT INTO `user-staff` (`staff_no`, `lastName`, `middleName`, `firstName`, `password`, `contactNumber`, `email`, `staff_token`, `avatar`, `waiter_code`) VALUES
(23, 'rustiko', 'coffee', 'kape', '$2y$10$VnSdQzyohAAFAOZGNAeut.XOMFSj//qclyPCPF8TgzLxmTTLu0b4a', 2147483647, 'kaperustiko@gmail.com', 23, 'default.jpg', ''),
(24, 'Dayandante', 'dalaguit', 'michael', '$2y$10$wu4DPoTJ93/VuHutX1Nt6eKSzniGdCjpYp3BYB/cANuSPg62.A8za', 912345678, 'mike@gmail.com', 24, 'default.jpg', ''),
(25, 'bading', 'talaga', 'nandy', '$2y$10$q2OAGhAKZ29fY5xSOokoSeqiWBfyE.CrEuPYwUC.qhVDctFrxwBjK', 2147483647, 'nandybading@gmail.comm', 25, 'default.jpg', ''),
(26, 'bading', 'talaga', 'nandy', '$2y$10$Vh5skZ8XBWWT8RVHywDyV.PoN01hUvPIdrkNkXRhsfSm48JWtTtLG', 2147483647, 'nandybading@gmail.comm', 26, 'default.jpg', ''),
(27, 'asdasd', 'asdas', 'sasd', '$2y$10$ORN7cdSN4qtSfKLPxi1ybeHk8UPRS5KteW1rcT/jjk14xGkDhP1w2', 213123, 'jake@mailinator.com', 27, 'default.jpg', ''),
(28, 'asdasd', 'asdas', 'sasd', '$2y$10$dNcdqkdU2FZJni1CyJ5L0.asC1s5ymHdZyXMlZGNUGMs5VVN/RhIy', 2131231, 'jake1@mailinator.com', 28, 'default.jpg', 'SA7393');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(255) NOT NULL,
  `voucher_code` varchar(255) NOT NULL,
  `voucher_discount` int(255) NOT NULL,
  `voucher_deadline` varchar(255) NOT NULL,
  `voucher_description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `voucher_code`, `voucher_discount`, `voucher_deadline`, `voucher_description`) VALUES
(5, 'SAMPLE', 32, '2025-02-11', 'SAMPLES'),
(6, 'Sa', 33, '2025-02-08', 'sd'),
(7, 'NANDY', 90, '2025-03-17', 'Lulu nimo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_no`);

--
-- Indexes for table `pos-menu`
--
ALTER TABLE `pos-menu`
  ADD PRIMARY KEY (`menu_no`);

--
-- Indexes for table `que_orders`
--
ALTER TABLE `que_orders`
  ADD PRIMARY KEY (`que_order_no`);

--
-- Indexes for table `remit_returns`
--
ALTER TABLE `remit_returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `remit_sales`
--
ALTER TABLE `remit_sales`
  ADD PRIMARY KEY (`remit_id`);

--
-- Indexes for table `reserve_table`
--
ALTER TABLE `reserve_table`
  ADD PRIMARY KEY (`reserve_no`);

--
-- Indexes for table `return-orders`
--
ALTER TABLE `return-orders`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `total_sales`
--
ALTER TABLE `total_sales`
  ADD PRIMARY KEY (`total_order`);

--
-- Indexes for table `user-staff`
--
ALTER TABLE `user-staff`
  ADD PRIMARY KEY (`staff_no`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=498;

--
-- AUTO_INCREMENT for table `pos-menu`
--
ALTER TABLE `pos-menu`
  MODIFY `menu_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `que_orders`
--
ALTER TABLE `que_orders`
  MODIFY `que_order_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `remit_returns`
--
ALTER TABLE `remit_returns`
  MODIFY `return_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `remit_sales`
--
ALTER TABLE `remit_sales`
  MODIFY `remit_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reserve_table`
--
ALTER TABLE `reserve_table`
  MODIFY `reserve_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `return-orders`
--
ALTER TABLE `return-orders`
  MODIFY `return_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `total_sales`
--
ALTER TABLE `total_sales`
  MODIFY `total_order` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `user-staff`
--
ALTER TABLE `user-staff`
  MODIFY `staff_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
