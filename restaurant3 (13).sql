-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2024 at 02:16 PM
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
-- Database: `restaurant3`
--

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
  `price_child` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_buffet`
--

INSERT INTO `order_buffet` (`order_buffet_id`, `table_id`, `emp_id`, `order_date`, `adult`, `child`, `price_adult`, `price_child`) VALUES
(20, 2, 2, '2024-08-29 05:54:18', 1, 0, 149.00, 0.00),
(21, 2, 2, '2024-08-29 08:54:17', 2, 1, 298.00, 99.00),
(22, 2, 2, '2024-08-29 08:56:26', 1, 0, 149.00, 0.00),
(23, 2, 2, '2024-08-29 13:59:14', 1, 0, 149.00, 0.00),
(24, 2, 2, '2024-08-29 14:30:44', 1, 0, 149.00, 0.00),
(25, 2, 2, '2024-09-01 17:41:03', 1, 0, 149.00, 0.00),
(26, 2, 2, '2024-09-06 01:37:58', 2, 0, 298.00, 0.00),
(27, 1, 2, '2024-09-07 17:15:01', 1, 0, 149.00, 0.00),
(28, 1, 2, '2024-09-07 17:15:50', 1, 0, 149.00, 0.00),
(29, 3, 2, '2024-09-07 17:20:30', 1, 0, 149.00, 0.00),
(30, 4, 2, '2024-09-07 17:22:29', 1, 0, 149.00, 0.00);

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
(43, 20, 7, 1, 0),
(44, 20, 13, 1, 0),
(45, 21, 13, 2, 0),
(46, 21, 14, 1, 0),
(47, 21, 10, 1, 0),
(48, 21, 12, 1, 0),
(49, 22, 7, 1, 0),
(50, 23, 7, 1, 3),
(51, 24, 7, 1, 1),
(52, 25, 7, 1, 1),
(53, 26, 7, 1, 1),
(54, 26, 13, 1, 1),
(55, 26, 14, 1, 1),
(56, 27, 7, 1, 1),
(57, 28, 7, 1, 1),
(58, 28, 13, 1, 1),
(59, 29, 7, 1, 1),
(60, 29, 13, 1, 1),
(61, 30, 7, 1, 1);

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
(2, 2, '2024-09-06 01:38:31');

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
(2, 2, 15, 2);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_type` int(1) DEFAULT NULL,
  `payment_time` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` int(11) NOT NULL COMMENT '1=Paid\r\n2=pending\r\n3=cancelled',
  `promotion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `discount` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 4, 2),
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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `promotion_id` (`promotion_id`);

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
  MODIFY `order_buffet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_buffet_details`
--
ALTER TABLE `order_buffet_details`
  MODIFY `order_buffet_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_pickup`
--
ALTER TABLE `order_pickup`
  MODIFY `order_pickup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_pickup_details`
--
ALTER TABLE `order_pickup_details`
  MODIFY `order_pickup_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
