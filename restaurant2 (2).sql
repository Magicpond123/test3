-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2024 at 09:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant2`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `bill_date` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `status` enum('paid','pending','cancelled') DEFAULT 'paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `payment_id`, `order_id`, `total_amount`, `bill_date`, `payment_method`, `promotion_id`, `table_id`, `status`) VALUES
(1, 3, 21, 740.00, '2024-09-09 14:30:05', '0', 0, 2, 'paid'),
(2, 4, 21, 740.00, '2024-09-09 14:31:30', '0', 0, 2, 'paid'),
(13, NULL, 44, 581.00, '2024-09-10 22:36:42', 'cash', 1, 1, 'paid'),
(14, NULL, 46, 496.00, '2024-09-10 23:10:06', 'cash', 1, 1, 'paid'),
(15, NULL, 45, 654.00, '2024-09-10 23:30:21', 'qr', 2, 1, 'paid'),
(16, 53, 4, 299.00, '2024-09-11 01:55:27', 'cash', NULL, NULL, 'paid'),
(17, 54, 4, 299.00, '2024-09-11 01:59:07', 'cash', NULL, NULL, 'paid'),
(18, 55, 4, 299.00, '2024-09-11 02:02:04', 'cash', NULL, NULL, 'paid'),
(19, 56, 4, 299.00, '2024-09-11 02:02:26', 'cash', NULL, NULL, 'paid'),
(20, 57, 4, 299.00, '2024-09-11 02:02:40', 'cash', NULL, NULL, 'paid'),
(21, 58, 4, 299.00, '2024-09-11 02:04:33', 'cash', NULL, NULL, 'paid'),
(22, 59, 4, 299.00, '2024-09-11 02:05:22', 'cash', NULL, NULL, 'paid'),
(23, 60, 4, 299.00, '2024-09-11 02:05:53', 'cash', NULL, NULL, 'paid'),
(24, 61, 4, 299.00, '2024-09-11 02:07:26', 'cash', NULL, NULL, 'paid'),
(25, 64, 5, 299.00, '2024-09-11 02:15:54', 'cash', NULL, NULL, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `type`) VALUES
(1, 'อาหาร'),
(2, 'เครื่องดื่ม'),
(3, 'ของหวาน');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `emp_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `location` text NOT NULL,
  `role` int(1) NOT NULL COMMENT '1=Owner\r\n2=Cashier\r\n3=Receptionist\r\n4=Kitchen',
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`emp_id`, `username`, `password`, `firstname`, `lastname`, `mail`, `location`, `role`, `status`) VALUES
(1, 'mgp02', '123', 'samdosaodkosa', 'sadsadsa', 'sadsadsad@gmail.com', 'sadasd', 1, 1),
(2, 'mgp01', '123', 'สัจจกร', 'ศิวธนภูวดล', 'pondza1087@gmail.com', 'กหฟหฟกหฟกฟหกหฟก', 3, 1),
(3, 'mgp03', '123', 'จักรพงศ์', 'พันดา', 'eiei@gmail.com', 'asdasdasd', 4, 1),
(4, 'mgp04', '123', 'จิตติพัฒน์', 'สุตานัน', 'jihee@gmail.com', 'asdasdasd', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menuitems`
--

CREATE TABLE `menuitems` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `order_type` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menuitems`
--

INSERT INTO `menuitems` (`item_id`, `category_id`, `name`, `description`, `price`, `image_path`, `status`, `unit_id`, `order_type`) VALUES
(7, 1, 'เนื้อนุ่ม', 'นุ่มฟูเหมือนตูดเด็ก', 49.00, 'uploads/7851e4fa502fd4e7b36c63357ac5db68.jpg', 1, 1, 1),
(10, 2, 'น้ำ', 'น้ำสะอาด สดชื่นเมื่อดื่ม', 7.00, 'uploads/a_.png', 1, 5, 1),
(11, 2, 'น้ำอัดลม', 'หวานฉ่ำ สดชื่น', 30.00, 'uploads/132_1.jpg', 1, 5, 1),
(12, 3, 'ข้าวเหนียวมะม่วง', 'ข้าวเหนียวมะม่วงหอมอร่อยหวานฉ่ำสดๆ จากสวน', 129.00, 'uploads/ข้าวเหนียวมะม่วง.jpg', 1, 6, 1),
(13, 1, 'เนื้อสไลด์', 'เนื้อนำเข้าพิเศษ', 79.00, 'uploads/7851e4fa502fd4e7b36c63357ac5db68.jpg', 1, 6, 1),
(14, 1, 'เนื้อหมักงา', 'นุ่มเหมือนตูดเด็กหอมฉุย', 49.00, 'uploads/marinatedmeat-whitesesamepork45-1170x468px-1170x460.jpg', 1, 6, 1),
(15, 1, 'รวมเนื้อ', 'เนื้อสันคอ สันนอก ใบพาย', 299.00, 'uploads/7851e4fa502fd4e7b36c63357ac5db68.jpg', 1, 7, 2);

-- --------------------------------------------------------

--
-- Table structure for table `order_buffet`
--

CREATE TABLE `order_buffet` (
  `order_buffet_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL,
  `adult` int(11) NOT NULL,
  `child` int(11) NOT NULL,
  `price_adult` decimal(10,2) NOT NULL,
  `price_child` decimal(10,2) NOT NULL,
  `payment_status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_buffet`
--

INSERT INTO `order_buffet` (`order_buffet_id`, `table_id`, `emp_id`, `order_date`, `adult`, `child`, `price_adult`, `price_child`, `payment_status`) VALUES
(32, 2, 2, '2024-09-09 15:11:46', 4, 0, 596.00, 0.00, 1),
(36, 1, 2, '2024-09-10 04:42:39', 4, 0, 596.00, 0.00, 1),
(37, 1, 2, '2024-09-10 04:45:04', 4, 0, 596.00, 0.00, 1),
(38, 4, 2, '2024-09-10 08:56:20', 4, 0, 596.00, 0.00, 1),
(39, 4, 2, '2024-09-10 09:08:07', 4, 2, 596.00, 198.00, 1),
(40, 3, 2, '2024-09-10 15:00:05', 3, 1, 447.00, 99.00, 1),
(41, 1, 2, '2024-09-10 15:17:18', 4, 1, 596.00, 99.00, 1),
(42, 1, 2, '2024-09-10 22:07:04', 4, 0, 596.00, 0.00, 1),
(43, 1, 2, '2024-09-10 22:21:21', 4, 1, 596.00, 99.00, 1),
(44, 1, 2, '2024-09-10 22:36:01', 3, 1, 447.00, 99.00, 1),
(45, 1, 2, '2024-09-10 22:42:16', 4, 1, 596.00, 99.00, 1),
(46, 1, 2, '2024-09-10 22:42:53', 4, 0, 596.00, 0.00, 1),
(47, 1, 2, '2024-09-10 23:42:46', 4, 0, 596.00, 0.00, 1),
(48, 1, 2, '2024-09-11 02:08:16', 1, 0, 149.00, 0.00, 1),
(49, 1, 2, '2024-09-11 02:11:37', 4, 0, 596.00, 0.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_buffet_details`
--

CREATE TABLE `order_buffet_details` (
  `order_buffet_detail_id` int(11) NOT NULL,
  `order_buffet_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(1) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_buffet_details`
--

INSERT INTO `order_buffet_details` (`order_buffet_detail_id`, `order_buffet_id`, `item_id`, `quantity`, `status`) VALUES
(72, 36, 7, 1, 3),
(73, 36, 10, 1, 3),
(74, 36, 11, 1, 3),
(75, 37, 7, 2, 3),
(76, 37, 10, 2, 3),
(77, 37, 12, 1, 3),
(78, 38, 7, 1, 3),
(79, 38, 10, 1, 3),
(80, 39, 7, 1, 3),
(81, 39, 13, 1, 3),
(82, 39, 10, 1, 3),
(83, 40, 7, 1, 3),
(84, 40, 10, 1, 3),
(85, 40, 12, 1, 3),
(86, 41, 7, 1, 3),
(87, 41, 13, 1, 3),
(88, 41, 14, 1, 3),
(89, 42, 7, 1, 3),
(90, 42, 10, 1, 3),
(91, 43, 7, 1, 3),
(92, 43, 13, 1, 3),
(93, 43, 14, 1, 3),
(94, 44, 7, 1, 3),
(95, 44, 13, 1, 3),
(96, 44, 14, 1, 3),
(97, 44, 10, 1, 3),
(98, 45, 7, 1, 3),
(99, 45, 13, 1, 3),
(100, 45, 14, 1, 3),
(101, 46, 7, 1, 3),
(102, 46, 13, 1, 4),
(103, 47, 7, 1, 3),
(104, 48, 7, 1, 3),
(105, 49, 7, 1, 3),
(106, 49, 13, 1, 3),
(107, 49, 10, 1, 3),
(108, 49, 11, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `order_pickup`
--

CREATE TABLE `order_pickup` (
  `order_pickup_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_pickup`
--

INSERT INTO `order_pickup` (`order_pickup_id`, `emp_id`, `order_date`) VALUES
(1, 2, '2024-09-05 17:56:17'),
(2, 2, '2024-09-06 01:38:31'),
(3, 2, '2024-09-09 17:07:09'),
(4, 2, '2024-09-11 01:51:41'),
(5, 2, '2024-09-11 02:15:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_pickup_details`
--

CREATE TABLE `order_pickup_details` (
  `order_pickup_detail_id` int(11) NOT NULL,
  `order_pickup_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_pickup_details`
--

INSERT INTO `order_pickup_details` (`order_pickup_detail_id`, `order_pickup_id`, `item_id`, `quantity`) VALUES
(1, 1, 15, 1),
(2, 2, 15, 2),
(3, 3, 15, 1),
(4, 4, 15, 1),
(5, 5, 15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_promotions`
--

CREATE TABLE `order_promotions` (
  `order_promotion_id` int(11) NOT NULL,
  `order_buffet_id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_promotions`
--

INSERT INTO `order_promotions` (`order_promotion_id`, `order_buffet_id`, `promotion_id`) VALUES
(1, 32, 1),
(2, 32, 2),
(3, 32, 1),
(4, 32, 1),
(33, 36, 1),
(34, 37, 1),
(35, 37, 1),
(36, 37, 1),
(37, 37, 1),
(38, 37, 1),
(39, 32, 1),
(40, 36, 1),
(41, 38, 1),
(42, 39, 1),
(43, 40, 1),
(44, 41, 1),
(45, 42, 1),
(46, 43, 1),
(47, 47, 1),
(48, 49, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_type` int(1) NOT NULL,
  `payment_time` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `order_type`, `payment_time`, `total_amount`, `payment_status`) VALUES
(37, 36, 1, '2024-09-10 08:09:59', 533.00, 1),
(38, 37, 1, '2024-09-10 08:16:00', 688.00, 1),
(39, 37, 1, '2024-09-10 08:17:27', 688.00, 1),
(40, 36, 1, '2024-09-10 08:17:42', 682.00, 1),
(41, 37, 1, '2024-09-10 08:28:45', 688.00, 1),
(42, 37, 1, '2024-09-10 08:38:29', 688.00, 1),
(43, 37, 1, '2024-09-10 08:40:17', 688.00, 1),
(44, 32, 1, '2024-09-10 08:45:13', 447.00, 1),
(45, 36, 1, '2024-09-10 08:45:52', 533.00, 1),
(46, 38, 1, '2024-09-10 08:57:30', 503.00, 1),
(47, 39, 1, '2024-09-10 09:09:08', 780.00, 1),
(48, 40, 1, '2024-09-10 15:02:12', 582.00, 1),
(49, 41, 1, '2024-09-10 21:56:05', 723.00, 1),
(50, 42, 1, '2024-09-10 22:08:26', 503.00, 1),
(51, 43, 1, '2024-09-10 22:24:15', 723.00, 1),
(52, 47, 1, '2024-09-10 23:43:11', 496.00, 1),
(53, 4, 2, '2024-09-11 01:55:27', 299.00, 1),
(54, 4, 2, '2024-09-11 01:59:07', 299.00, 1),
(55, 4, 2, '2024-09-11 02:02:04', 299.00, 1),
(56, 4, 2, '2024-09-11 02:02:26', 299.00, 1),
(57, 4, 2, '2024-09-11 02:02:40', 299.00, 1),
(58, 4, 2, '2024-09-11 02:04:33', 299.00, 1),
(59, 4, 2, '2024-09-11 02:05:22', 299.00, 1),
(60, 4, 2, '2024-09-11 02:05:53', 299.00, 1),
(61, 4, 2, '2024-09-11 02:07:26', 299.00, 1),
(62, 48, 1, '2024-09-11 02:08:58', 198.00, 1),
(63, 49, 1, '2024-09-11 02:15:14', 612.00, 1),
(64, 5, 2, '2024-09-11 02:15:54', 299.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `discount` double NOT NULL DEFAULT 0,
  `discount_percent` double NOT NULL,
  `type` varchar(100) NOT NULL,
  `rule_person` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`promotion_id`, `name`, `description`, `start_date`, `end_date`, `discount`, `discount_percent`, `type`, `rule_person`) VALUES
(1, 'มา 4 จ่าย 3 ', '', '2024-09-10 00:00:00', '2024-09-11 00:00:00', 149, 0, 'discount-type-person', 4),
(2, 'โปรวันเกิด', 'มาเกิดอะไรวันนี้', '2024-09-10 00:00:00', '0000-00-00 00:00:00', 0, 25, 'discount-type-birthday', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `table_status` int(1) NOT NULL COMMENT '1=available\r\n2=unavailable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`table_id`, `table_number`, `table_status`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`unit_id`, `name`) VALUES
(1, 'กิโลกรัม'),
(2, 'กรัม'),
(3, 'ชิ้น'),
(4, 'แพ็ค'),
(5, 'ขวด'),
(6, 'จาน'),
(7, 'ขีด');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indexes for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `menuitems_ibfk_2` (`unit_id`);

--
-- Indexes for table `order_buffet`
--
ALTER TABLE `order_buffet`
  ADD PRIMARY KEY (`order_buffet_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `order_buffet_details`
--
ALTER TABLE `order_buffet_details`
  ADD PRIMARY KEY (`order_buffet_detail_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `order_buffet_id` (`order_buffet_id`) USING BTREE;

--
-- Indexes for table `order_pickup`
--
ALTER TABLE `order_pickup`
  ADD PRIMARY KEY (`order_pickup_id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `order_pickup_details`
--
ALTER TABLE `order_pickup_details`
  ADD PRIMARY KEY (`order_pickup_detail_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `order_promotions`
--
ALTER TABLE `order_promotions`
  ADD PRIMARY KEY (`order_promotion_id`),
  ADD KEY `order_buffet_id` (`order_buffet_id`),
  ADD KEY `promotion_id` (`promotion_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_id`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`unit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menuitems`
--
ALTER TABLE `menuitems`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_buffet`
--
ALTER TABLE `order_buffet`
  MODIFY `order_buffet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `order_buffet_details`
--
ALTER TABLE `order_buffet_details`
  MODIFY `order_buffet_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `order_pickup`
--
ALTER TABLE `order_pickup`
  MODIFY `order_pickup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_pickup_details`
--
ALTER TABLE `order_pickup_details`
  MODIFY `order_pickup_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_promotions`
--
ALTER TABLE `order_promotions`
  MODIFY `order_promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD CONSTRAINT `menuitems_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `menuitems_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`unit_id`);

--
-- Constraints for table `order_buffet`
--
ALTER TABLE `order_buffet`
  ADD CONSTRAINT `order_buffet_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  ADD CONSTRAINT `order_buffet_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`);

--
-- Constraints for table `order_buffet_details`
--
ALTER TABLE `order_buffet_details`
  ADD CONSTRAINT `order_buffet_details_ibfk_1` FOREIGN KEY (`order_buffet_id`) REFERENCES `order_buffet` (`order_buffet_id`),
  ADD CONSTRAINT `order_buffet_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menuitems` (`item_id`);

--
-- Constraints for table `order_pickup`
--
ALTER TABLE `order_pickup`
  ADD CONSTRAINT `order_pickup_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`);

--
-- Constraints for table `order_pickup_details`
--
ALTER TABLE `order_pickup_details`
  ADD CONSTRAINT `order_pickup_details_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menuitems` (`item_id`);

--
-- Constraints for table `order_promotions`
--
ALTER TABLE `order_promotions`
  ADD CONSTRAINT `order_promotions_ibfk_1` FOREIGN KEY (`order_buffet_id`) REFERENCES `order_buffet` (`order_buffet_id`),
  ADD CONSTRAINT `order_promotions_ibfk_2` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`);

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `bills` (`bill_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
