-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 12:46 PM
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
-- Database: `login_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `programare_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `data_adaugare` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `programare_id`, `text`, `data_adaugare`) VALUES
(6, 1, 'asd', '2025-06-21');

-- --------------------------------------------------------

--
-- Table structure for table `medici`
--

CREATE TABLE `medici` (
  `id` int(11) NOT NULL,
  `nume` varchar(50) NOT NULL,
  `prenume` varchar(50) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medici`
--

INSERT INTO `medici` (`id`, `nume`, `prenume`, `telefon`, `email`) VALUES
(1, 'Popescu', 'Andrei', '0784385033', 'pandrei123@gmail.com'),
(2, 'Ardeleanu', 'Mihai', '0725221428', 'amihai121@gmail.com'),
(3, 'Ionescu', 'Razvan', '0725647123', 'razvan2484@yahoo.com');

-- --------------------------------------------------------

--
-- Table structure for table `pacienti`
--

CREATE TABLE `pacienti` (
  `id` int(11) NOT NULL,
  `numar_fisa` varchar(50) DEFAULT NULL,
  `data_inregistrare` date DEFAULT NULL,
  `nume` varchar(100) DEFAULT NULL,
  `specie` varchar(50) DEFAULT NULL,
  `rasa` varchar(50) DEFAULT NULL,
  `sex` enum('M','F') DEFAULT NULL,
  `greutate` float DEFAULT NULL,
  `culoare` varchar(50) DEFAULT NULL,
  `varsta` int(11) DEFAULT NULL,
  `microcip` varchar(50) DEFAULT NULL,
  `boli_cronice` text DEFAULT NULL,
  `proprietar_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pacienti`
--

INSERT INTO `pacienti` (`id`, `numar_fisa`, `data_inregistrare`, `nume`, `specie`, `rasa`, `sex`, `greutate`, `culoare`, `varsta`, `microcip`, `boli_cronice`, `proprietar_id`) VALUES
(6, '1', '2025-11-04', 'Lexi', 'Pisica', 'Europeana', 'F', 3, 'Maro, alb si negru', 2, '02121', 'Nu are', 1),
(8, '3', '2025-12-05', 'Rex', 'Caine', 'Doberman', 'M', 10, 'Negru', 7, '', '', 2),
(9, '4', '2025-05-26', 'Martinel', 'Caine', 'pitbull', 'M', 5, 'negru', 2, '3121', 'nu are', 1);

-- --------------------------------------------------------

--
-- Table structure for table `plati`
--

CREATE TABLE `plati` (
  `id` int(11) NOT NULL,
  `pacient_id` int(11) NOT NULL,
  `serviciu_id` int(11) NOT NULL,
  `suma` decimal(10,2) NOT NULL,
  `data_plata` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plati`
--

INSERT INTO `plati` (`id`, `pacient_id`, `serviciu_id`, `suma`, `data_plata`) VALUES
(6, 8, 5, 150.00, '2025-05-19'),
(14, 6, 6, 25.00, '2025-06-24'),
(19, 6, 4, 50.00, '2025-06-24'),
(20, 6, 8, 500.00, '2025-06-24');

-- --------------------------------------------------------

--
-- Table structure for table `programari`
--

CREATE TABLE `programari` (
  `id` int(11) NOT NULL,
  `pacient_id` int(11) NOT NULL,
  `medic_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `interval_orar` varchar(50) NOT NULL,
  `status` enum('neconfirmată','confirmată','anulată','finalizată') DEFAULT 'neconfirmată',
  `motiv` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programari`
--

INSERT INTO `programari` (`id`, `pacient_id`, `medic_id`, `data`, `interval_orar`, `status`, `motiv`) VALUES
(1, 6, 1, '2025-12-05', '09:00 - 10:00', 'anulată', ''),
(3, 6, 1, '2025-12-06', '09:00 - 10:00', 'anulată', ''),
(4, 8, 3, '2025-05-19', '09:00 - 10:00', 'anulată', ''),
(5, 8, 2, '2025-05-19', '09:00 - 10:00', 'confirmată', ''),
(6, 8, 1, '2025-05-20', '11:00 - 12:00', 'anulată', ''),
(7, 6, 2, '2025-05-21', '14:00 - 15:00', 'neconfirmată', ''),
(8, 6, 2, '2025-05-28', '10:00 - 11:00', 'neconfirmată', ''),
(9, 8, 2, '2025-05-29', '14:00 - 15:00', 'neconfirmată', ''),
(10, 9, 1, '2025-05-30', '09:00 - 10:00', 'finalizată', ''),
(11, 6, 1, '0222-12-22', '09:00 - 10:00', 'neconfirmată', ''),
(12, 6, 3, '0222-02-22', '10:00 - 11:00', 'finalizată', ''),
(14, 6, 1, '2025-06-21', '09:00 - 10:00', 'finalizată', ''),
(16, 9, 1, '2025-06-21', '14:00 - 15:00', 'confirmată', ''),
(17, 6, 1, '2025-06-24', '09:00 - 10:00', 'finalizată', ''),
(19, 6, 1, '2025-06-24', '10:00 - 11:00', 'neconfirmată', ''),
(20, 6, 1, '2025-06-26', '09:00 - 10:00', 'finalizată', '');

-- --------------------------------------------------------

--
-- Table structure for table `proprietari`
--

CREATE TABLE `proprietari` (
  `id` int(11) NOT NULL,
  `nume` varchar(50) NOT NULL,
  `prenume` varchar(50) NOT NULL,
  `adresa` text DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proprietari`
--

INSERT INTO `proprietari` (`id`, `nume`, `prenume`, `adresa`, `telefon`, `email`) VALUES
(1, 'Cretu', 'Mateiiiiii', 'constanta', '0784385033', 'mateicretu123@gmail.com'),
(2, 'David ', 'Vladut', 'Str Primaverii, Barlad', '07221212112', 'davidv123@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `servicii`
--

CREATE TABLE `servicii` (
  `id` int(11) NOT NULL,
  `pacient_id` int(11) NOT NULL,
  `medic_id` int(11) NOT NULL,
  `denumire` varchar(100) NOT NULL,
  `pret` decimal(10,2) NOT NULL,
  `data_efectuare` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicii`
--

INSERT INTO `servicii` (`id`, `pacient_id`, `medic_id`, `denumire`, `pret`, `data_efectuare`) VALUES
(4, 6, 2, 'Deparazitare externa', 50.00, '2025-06-25'),
(5, 8, 2, 'Vaccin antirabic', 150.00, '0000-00-00'),
(6, 6, 1, 'Vaccin anual', 25.00, '2025-05-26'),
(8, 6, 2, 'asdadsa', 500.00, '2025-01-06'),
(9, 6, 1, 'asdasdasd', 1212.00, '2025-05-26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `user_name`, `password`, `date`, `is_admin`) VALUES
(1, 59322252786371748, 'matei123', '1234', '2024-12-30 12:06:58', 0),
(2, 9223372036854775807, 'matei', '1234', '2024-12-30 12:07:06', 0),
(3, 96351373754870, 'admin', 'admin', '2025-01-07 11:49:50', 1),
(5, 1234, 'hadi', '1234', '2025-01-07 11:58:07', 1),
(6, 3421, 'alex', 'alex', '2025-04-11 11:47:21', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `programare_id` (`programare_id`);

--
-- Indexes for table `medici`
--
ALTER TABLE `medici`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pacienti`
--
ALTER TABLE `pacienti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proprietar` (`proprietar_id`);

--
-- Indexes for table `plati`
--
ALTER TABLE `plati`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serviciu_id` (`serviciu_id`),
  ADD KEY `pacient_id` (`pacient_id`);

--
-- Indexes for table `programari`
--
ALTER TABLE `programari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacient_id` (`pacient_id`),
  ADD KEY `medic_id` (`medic_id`);

--
-- Indexes for table `proprietari`
--
ALTER TABLE `proprietari`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicii`
--
ALTER TABLE `servicii`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacient_id` (`pacient_id`),
  ADD KEY `medic_id` (`medic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`),
  ADD KEY `user_name` (`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medici`
--
ALTER TABLE `medici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pacienti`
--
ALTER TABLE `pacienti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `plati`
--
ALTER TABLE `plati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `programari`
--
ALTER TABLE `programari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `proprietari`
--
ALTER TABLE `proprietari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `servicii`
--
ALTER TABLE `servicii`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`programare_id`) REFERENCES `programari` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pacienti`
--
ALTER TABLE `pacienti`
  ADD CONSTRAINT `fk_proprietar` FOREIGN KEY (`proprietar_id`) REFERENCES `proprietari` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `plati`
--
ALTER TABLE `plati`
  ADD CONSTRAINT `plati_ibfk_1` FOREIGN KEY (`pacient_id`) REFERENCES `pacienti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plati_ibfk_2` FOREIGN KEY (`serviciu_id`) REFERENCES `servicii` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programari`
--
ALTER TABLE `programari`
  ADD CONSTRAINT `programari_ibfk_1` FOREIGN KEY (`pacient_id`) REFERENCES `pacienti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `programari_ibfk_2` FOREIGN KEY (`medic_id`) REFERENCES `medici` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicii`
--
ALTER TABLE `servicii`
  ADD CONSTRAINT `servicii_ibfk_1` FOREIGN KEY (`pacient_id`) REFERENCES `pacienti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `servicii_ibfk_2` FOREIGN KEY (`medic_id`) REFERENCES `medici` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
