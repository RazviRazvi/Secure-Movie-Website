-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 02, 2025 at 03:01 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rezervare_filme`
--

-- --------------------------------------------------------

--
-- Table structure for table `cinema`
--

CREATE TABLE `cinema` (
  `ID_cinema` int(11) NOT NULL,
  `adresa` char(60) DEFAULT NULL,
  `nume_cinema` char(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cinema`
--

INSERT INTO `cinema` (`ID_cinema`, `adresa`, `nume_cinema`) VALUES
(1, 'Str. Mesteacanului nr. 40', 'Voicu SRL'),
(2, 'Bulevardul Timisoarei', 'Cinema Plaza'),
(3, 'Calea Bucuresti', 'Inspire Cinema'),
(4, 'Iuliu Maniu', 'Cinema AFI'),
(5, 'Strada criptografiei nr.2', 'CryptoCinema');

-- --------------------------------------------------------

--
-- Table structure for table `clienti`
--

CREATE TABLE `clienti` (
  `ID_client` int(11) NOT NULL,
  `nume` char(60) DEFAULT NULL,
  `prenume` char(60) DEFAULT NULL,
  `CNP` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clienti`
--

INSERT INTO `clienti` (`ID_client`, `nume`, `prenume`, `CNP`) VALUES
(1, 'Razvan', 'Galeseanu', 1234567890123),
(2, 'Vlad', 'Voicu', 9876543210987),
(3, 'alexutu', 'hacker', 4561237896540),
(4, 'Bob', 'Brown', 6547891234567);

-- --------------------------------------------------------

--
-- Table structure for table `film`
--

CREATE TABLE `film` (
  `ID_film` int(11) NOT NULL,
  `nume_film` char(60) DEFAULT NULL,
  `durata` int(11) DEFAULT NULL,
  `Tip_film` enum('3D','4D') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `film`
--

INSERT INTO `film` (`ID_film`, `nume_film`, `durata`, `Tip_film`) VALUES
(1, 'Avatar', 180, '3D'),
(2, 'Inception', 148, '4D'),
(3, 'Titanic', 195, '3D'),
(4, 'Star Wars', 150, '4D');

-- --------------------------------------------------------

--
-- Table structure for table `rezervare`
--

CREATE TABLE `rezervare` (
  `ID_rezervare` int(11) NOT NULL,
  `ID_client` int(11) DEFAULT NULL,
  `ID_sali_filme` int(11) DEFAULT NULL,
  `tip_plata` enum('cash','card') DEFAULT NULL,
  `data` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rezervare`
--

INSERT INTO `rezervare` (`ID_rezervare`, `ID_client`, `ID_sali_filme`, `tip_plata`, `data`) VALUES
(4, 4, 4, 'cash', '2024-11-28 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sala`
--

CREATE TABLE `sala` (
  `ID_sala` int(11) NOT NULL,
  `ID_cinema` int(11) DEFAULT NULL,
  `nr_sala` int(11) DEFAULT NULL,
  `capacitate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sala`
--

INSERT INTO `sala` (`ID_sala`, `ID_cinema`, `nr_sala`, `capacitate`) VALUES
(1, 1, 3, 400),
(2, 1, 2, 350),
(3, 2, 1, 300),
(4, 3, 1, 250),
(5, 3, 2, 200);

-- --------------------------------------------------------

--
-- Table structure for table `sali_filme`
--

CREATE TABLE `sali_filme` (
  `ID_sali_filme` int(11) NOT NULL,
  `ID_sala` int(11) DEFAULT NULL,
  `ID_film` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sali_filme`
--

INSERT INTO `sali_filme` (`ID_sali_filme`, `ID_sala`, `ID_film`) VALUES
(2, 2, 4),
(3, 2, 4),
(4, 3, 4),
(5, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'raz', '$2y$10$WV5rsZNkYAURQ5fY0F4b6eDOHNyZ2OTylx0o.whFnqH44xldv8C7.'),
(2, 'admin', '$2y$10$NtmfOzIGF2R/GaSDsX.d/.ymynYOGq5UmFsgiGZ/Ey5zvbBRISKyy'),
(3, 'razvan', '$2y$10$LFiGOgdXu8xw3pN3Pz48eO4vghrZv0F0jVpviyGasKNiYKS191azu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cinema`
--
ALTER TABLE `cinema`
  ADD PRIMARY KEY (`ID_cinema`);

--
-- Indexes for table `clienti`
--
ALTER TABLE `clienti`
  ADD PRIMARY KEY (`ID_client`);

--
-- Indexes for table `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`ID_film`);

--
-- Indexes for table `rezervare`
--
ALTER TABLE `rezervare`
  ADD PRIMARY KEY (`ID_rezervare`),
  ADD KEY `ID_client` (`ID_client`),
  ADD KEY `ID_sali_filme` (`ID_sali_filme`);

--
-- Indexes for table `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`ID_sala`),
  ADD KEY `ID_cinema` (`ID_cinema`);

--
-- Indexes for table `sali_filme`
--
ALTER TABLE `sali_filme`
  ADD PRIMARY KEY (`ID_sali_filme`),
  ADD KEY `ID_sala` (`ID_sala`),
  ADD KEY `ID_film` (`ID_film`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cinema`
--
ALTER TABLE `cinema`
  MODIFY `ID_cinema` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clienti`
--
ALTER TABLE `clienti`
  MODIFY `ID_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `film`
--
ALTER TABLE `film`
  MODIFY `ID_film` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rezervare`
--
ALTER TABLE `rezervare`
  MODIFY `ID_rezervare` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sala`
--
ALTER TABLE `sala`
  MODIFY `ID_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sali_filme`
--
ALTER TABLE `sali_filme`
  MODIFY `ID_sali_filme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rezervare`
--
ALTER TABLE `rezervare`
  ADD CONSTRAINT `rezervare_ibfk_1` FOREIGN KEY (`ID_client`) REFERENCES `clienti` (`ID_client`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezervare_ibfk_2` FOREIGN KEY (`ID_sali_filme`) REFERENCES `sali_filme` (`ID_sali_filme`) ON DELETE CASCADE;

--
-- Constraints for table `sala`
--
ALTER TABLE `sala`
  ADD CONSTRAINT `sala_ibfk_1` FOREIGN KEY (`ID_cinema`) REFERENCES `cinema` (`ID_cinema`) ON DELETE CASCADE;

--
-- Constraints for table `sali_filme`
--
ALTER TABLE `sali_filme`
  ADD CONSTRAINT `sali_filme_ibfk_1` FOREIGN KEY (`ID_sala`) REFERENCES `sala` (`ID_sala`) ON DELETE CASCADE,
  ADD CONSTRAINT `sali_filme_ibfk_2` FOREIGN KEY (`ID_film`) REFERENCES `film` (`ID_film`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
