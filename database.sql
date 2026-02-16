CREATE TABLE IF NOT EXISTS `wp_grt_booking_availability` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Data
INSERT INTO `wp_grt_booking_availability` (`start_date`, `end_date`, `status`) VALUES
('2024-01-01', '2024-01-31', 'available'),
('2024-02-01', '2024-02-28', 'available'),
('2024-03-01', '2024-03-31', 'available');
