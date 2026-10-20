-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 09:37 AM
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
-- Database: `brightsmile`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DentistID` int(11) NOT NULL,
  `ServiceID` int(11) NOT NULL,
  `AppointmentDate` date NOT NULL,
  `AppointmentTime` time NOT NULL,
  `AppointmentStatus` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `AppointmentNotes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dentalrecord`
--

CREATE TABLE `dentalrecord` (
  `RecordID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DentistID` int(11) NOT NULL,
  `AppointmentID` int(11) DEFAULT NULL,
  `RecordDate` date NOT NULL,
  `Diagnosis` text DEFAULT NULL,
  `ClinicalNotes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dentist`
--

CREATE TABLE `dentist` (
  `DentistID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `DeptID` int(11) DEFAULT NULL,
  `Specialty` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `DeptID` int(11) NOT NULL,
  `DepartmentName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `ItemID` int(11) NOT NULL,
  `ItemName` varchar(100) NOT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `QuantityInStock` int(11) NOT NULL DEFAULT 0,
  `ReorderLevel` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orthodonticrecord`
--

CREATE TABLE `orthodonticrecord` (
  `OrthoRecordID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DentistID` int(11) NOT NULL,
  `TreatmentType` varchar(100) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `CurrentStage` varchar(100) DEFAULT NULL,
  `ProgressNotes` text DEFAULT NULL,
  `NextAdjustmentDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `PatientID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Gender` varchar(20) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Approval` enum('Yes','No') NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `PrescriptionID` int(11) NOT NULL,
  `RecordID` int(11) NOT NULL,
  `MedicationName` varchar(100) NOT NULL,
  `Dosage` varchar(100) DEFAULT NULL,
  `Instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `ServiceID` int(11) NOT NULL,
  `ServiceName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stocktransaction`
--

CREATE TABLE `stocktransaction` (
  `TransactionID` int(11) NOT NULL,
  `ItemID` int(11) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `TransactionType` enum('Restock','Usage','Adjustment') NOT NULL,
  `Quantity` int(11) NOT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tooth`
--

CREATE TABLE `tooth` (
  `ToothID` int(11) NOT NULL,
  `ToothNumber` varchar(10) NOT NULL,
  `ToothName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toothrecord`
--

CREATE TABLE `toothrecord` (
  `ToothRecordID` int(11) NOT NULL,
  `RecordID` int(11) NOT NULL,
  `ToothID` int(11) NOT NULL,
  `ConditionDescription` varchar(150) DEFAULT NULL,
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `treatment`
--

CREATE TABLE `treatment` (
  `TreatmentID` int(11) NOT NULL,
  `RecordID` int(11) NOT NULL,
  `TreatmentName` varchar(100) NOT NULL,
  `TreatmentDetails` text DEFAULT NULL,
  `TreatmentDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `ic_number` varchar(12) DEFAULT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Role` enum('Patient','Dentist','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_id_sequence`
--

CREATE TABLE `role_id_sequence` (
  `Role` enum('Patient','Admin','Dentist') NOT NULL,
  `LastID` int(11) NOT NULL,
  PRIMARY KEY (`Role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role_id_sequence` (`Role`, `LastID`) VALUES
('Patient', 1000),
('Admin', 2000),
('Dentist', 3000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `UserID` (`UserID`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD UNIQUE KEY `uq_dentist_appointment` (`DentistID`,`AppointmentDate`,`AppointmentTime`),
  ADD KEY `fk_appointment_patient` (`PatientID`),
  ADD KEY `fk_appointment_service` (`ServiceID`);

--
-- Indexes for table `dentalrecord`
--
ALTER TABLE `dentalrecord`
  ADD PRIMARY KEY (`RecordID`),
  ADD UNIQUE KEY `AppointmentID` (`AppointmentID`),
  ADD KEY `fk_record_patient` (`PatientID`),
  ADD KEY `fk_record_dentist` (`DentistID`);

--
-- Indexes for table `dentist`
--
ALTER TABLE `dentist`
  ADD PRIMARY KEY (`DentistID`),
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD KEY `fk_dentist_department` (`DeptID`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`DeptID`),
  ADD UNIQUE KEY `DepartmentName` (`DepartmentName`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`ItemID`);

--
-- Indexes for table `orthodonticrecord`
--
ALTER TABLE `orthodonticrecord`
  ADD PRIMARY KEY (`OrthoRecordID`),
  ADD KEY `fk_ortho_patient` (`PatientID`),
  ADD KEY `fk_ortho_dentist` (`DentistID`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`PatientID`),
  ADD UNIQUE KEY `UserID` (`UserID`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`PrescriptionID`),
  ADD KEY `fk_prescription_record` (`RecordID`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`ServiceID`),
  ADD UNIQUE KEY `ServiceName` (`ServiceName`);

--
-- Indexes for table `stocktransaction`
--
ALTER TABLE `stocktransaction`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `fk_stock_item` (`ItemID`),
  ADD KEY `fk_stock_admin` (`AdminID`);

--
-- Indexes for table `tooth`
--
ALTER TABLE `tooth`
  ADD PRIMARY KEY (`ToothID`),
  ADD UNIQUE KEY `ToothNumber` (`ToothNumber`);

--
-- Indexes for table `toothrecord`
--
ALTER TABLE `toothrecord`
  ADD PRIMARY KEY (`ToothRecordID`),
  ADD UNIQUE KEY `uq_record_tooth` (`RecordID`,`ToothID`),
  ADD KEY `fk_tooth` (`ToothID`);

--
-- Indexes for table `treatment`
--
ALTER TABLE `treatment`
  ADD PRIMARY KEY (`TreatmentID`),
  ADD KEY `fk_treatment_record` (`RecordID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `uq_user_ic_number` (`ic_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dentalrecord`
--
ALTER TABLE `dentalrecord`
  MODIFY `RecordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dentist`
--
ALTER TABLE `dentist`
  MODIFY `DentistID` int(11) NOT NULL;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `DeptID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `ItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orthodonticrecord`
--
ALTER TABLE `orthodonticrecord`
  MODIFY `OrthoRecordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `PatientID` int(11) NOT NULL;

--
-- AUTO_INCREMENT for table `prescription`
--
ALTER TABLE `prescription`
  MODIFY `PrescriptionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stocktransaction`
--
ALTER TABLE `stocktransaction`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tooth`
--
ALTER TABLE `tooth`
  MODIFY `ToothID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `toothrecord`
--
ALTER TABLE `toothrecord`
  MODIFY `ToothRecordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `treatment`
--
ALTER TABLE `treatment`
  MODIFY `TreatmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_appointment_dentist` FOREIGN KEY (`DentistID`) REFERENCES `dentist` (`DentistID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointment_patient` FOREIGN KEY (`PatientID`) REFERENCES `patient` (`PatientID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dentalrecord`
--
ALTER TABLE `dentalrecord`
  ADD CONSTRAINT `fk_record_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointment` (`AppointmentID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_record_dentist` FOREIGN KEY (`DentistID`) REFERENCES `dentist` (`DentistID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_record_patient` FOREIGN KEY (`PatientID`) REFERENCES `patient` (`PatientID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dentist`
--
ALTER TABLE `dentist`
  ADD CONSTRAINT `fk_dentist_department` FOREIGN KEY (`DeptID`) REFERENCES `department` (`DeptID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dentist_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orthodonticrecord`
--
ALTER TABLE `orthodonticrecord`
  ADD CONSTRAINT `fk_ortho_dentist` FOREIGN KEY (`DentistID`) REFERENCES `dentist` (`DentistID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ortho_patient` FOREIGN KEY (`PatientID`) REFERENCES `patient` (`PatientID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `fk_prescription_record` FOREIGN KEY (`RecordID`) REFERENCES `dentalrecord` (`RecordID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stocktransaction`
--
ALTER TABLE `stocktransaction`
  ADD CONSTRAINT `fk_stock_admin` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_item` FOREIGN KEY (`ItemID`) REFERENCES `inventory` (`ItemID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `toothrecord`
--
ALTER TABLE `toothrecord`
  ADD CONSTRAINT `fk_tooth` FOREIGN KEY (`ToothID`) REFERENCES `tooth` (`ToothID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tooth_record` FOREIGN KEY (`RecordID`) REFERENCES `dentalrecord` (`RecordID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `treatment`
--
ALTER TABLE `treatment`
  ADD CONSTRAINT `fk_treatment_record` FOREIGN KEY (`RecordID`) REFERENCES `dentalrecord` (`RecordID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
