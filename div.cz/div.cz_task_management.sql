-- MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

CREATE TABLE `DIV.cz_Tasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Description` longtext NOT NULL,
  `Comments` longtext DEFAULT NULL,
  `Created` date NOT NULL,
  `Assigned` varchar(64) DEFAULT NULL,
  `Creator` varchar(64) DEFAULT NULL,
  `Status` varchar(16) NOT NULL,
  `Priority` varchar(16) NOT NULL,
  `Category` varchar(16) NOT NULL,
  `IPaddress` varchar(64) DEFAULT NULL,
  `Updated` date NOT NULL,
  `DueDate` date NOT NULL,
  `ParentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DIV.cz_Tasks_ParentID_5291898e_fk_DIV.cz_Tasks_id` (`ParentID`),
  CONSTRAINT `DIV.cz_Tasks_ParentID_5291898e_fk_DIV.cz_Tasks_id` FOREIGN KEY (`ParentID`) REFERENCES `DIV.cz_Tasks` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2024-05-03 13:30:29
