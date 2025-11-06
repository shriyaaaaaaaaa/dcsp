-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: dcsp
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'rijan@gmail.com','Rijan@123');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cab_6`
--

DROP TABLE IF EXISTS `cab_6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cab_6` (
  `id` int(11) NOT NULL,
  `org_name` varchar(255) DEFAULT NULL,
  `sub_admin_id` int(11) DEFAULT NULL,
  `teacher_reg` varchar(255) DEFAULT NULL,
  `student_reg` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subjects` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cab_6`
--

LOCK TABLES `cab_6` WRITE;
/*!40000 ALTER TABLE `cab_6` DISABLE KEYS */;
INSERT INTO `cab_6` VALUES (2,'CAB',6,NULL,NULL,'2025-07-14 07:16:35','[\"Database Management\",\"GIS\",\"Micro-Processor\"]'),(5,'CAB',6,NULL,NULL,'2025-08-06 04:03:25','[\"Advance Dotnet\"]'),(6,'CAB',6,NULL,NULL,'2025-08-06 11:41:35','[\"Advance Java\",\"Operating Systems\",\"Programming in C\"]'),(7,'CAB',6,NULL,NULL,'2025-08-06 11:44:54','[\"Information Security\",\"Micro-Processor\",\"Web Technologies\"]'),(8,'CAB',6,NULL,NULL,'2025-08-06 11:47:38','[\"Advance Dotnet\"]'),(18,'CAB',6,'1',NULL,'2025-08-07 10:35:26',NULL),(19,'CAB',6,'2',NULL,'2025-08-07 10:35:28',NULL),(20,'CAB',6,'3',NULL,'2025-08-07 10:35:30',NULL),(21,'CAB',6,'4',NULL,'2025-08-07 10:35:33',NULL),(22,'CAB',6,'5',NULL,'2025-08-07 10:35:35',NULL),(23,'CAB',6,'6',NULL,'2025-08-07 10:35:38',NULL),(25,'CAB',6,'8',NULL,'2025-08-07 10:35:43',NULL),(29,'CAB',6,'p10069',NULL,'2025-08-15 05:39:39',NULL),(30,'CAB',6,NULL,'400','2025-09-05 18:37:51',NULL);
/*!40000 ALTER TABLE `cab_6` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `org_name` varchar(255) DEFAULT NULL,
  `sub_admin_id` int(11) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `subjects` text DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'CAB',6,'BCA 1st sem','Database Management,GIS,Micro-Processor','10:00:00','15:00:00'),(2,'CAB',6,'BCA 2nd sem class','Database Management,GIS,Micro-Processor,Operating Systems','11:00:00','16:00:00'),(3,'CAB',6,'BCA 3rd sem','GIS,Programming in C,Web Technologies','10:00:00','16:00:00'),(4,'CAB',6,'BCA 4th sem','GIS,Operating Systems,Programming in C,Information Security','12:00:00','18:00:00'),(5,'JAYCESS',13,'BIT 1st year','Database Management, Programming in C, Computer Networks, Mathematics, Operating Systems, Web Technologies','11:00:00','17:30:00'),(7,'JAYCESS',13,'BCA test','GIS, Micro-Processor, Operating Systems, Database Management, Web Technologies, Programming in C','09:00:00','16:00:00'),(8,'MVEBS',19,'BCA 1st sem','Computer Fundamentals and Applications,Digital Logic,Mathematics-I,Professional Communication and Ethics,Programming in C','09:00:00','15:00:00'),(9,'MVEBS',19,'BCA 2nd sem','Discrete Structure,Mathematics-II,Micro-Processor,OOP in Java,UX/UI Design','10:00:00','16:00:00'),(10,'MVEBS',19,'BCA 3rd sem','Applied Economics,Data Structure and Algorithms,Database Management System,Probability and Statistics,System Analysis and Design,Web Technology-I','09:00:00','16:00:00');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comment` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `org_id` int(100) NOT NULL,
  `teacher_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `comment` varchar(1000) NOT NULL,
  `date` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
INSERT INTO `comment` VALUES (1,19,202,'','can you make me look good','2025-09-05 22:52:49'),(3,19,201,'Ravi Ram','this is a try message where i can express my thoughts and feelings about the schedule that my organization created and i can tell what i didn\'t like about the schedule.','2025-09-05 22:58:37'),(4,6,3,'Test3','their is only one class for me atleat add one more class for me','2025-09-06 00:25:20'),(5,6,5,'Test5','hi i would like to test this comment section in you software','2025-09-06 13:17:22'),(6,13,8,'Rijan Subedi','ryrt','2025-11-06 11:34:57'),(7,13,8,'Rijan Subedi','vfcghcg','2025-11-06 11:36:27'),(8,13,8,'Rijan Subedi','fyfvu','2025-11-06 12:13:49'),(9,13,8,'Rijan Subedi','1233','2025-11-06 12:14:09');
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses_subjects`
--

DROP TABLE IF EXISTS `courses_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses_subjects` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subject` (`category`,`faculty`,`subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses_subjects`
--

LOCK TABLES `courses_subjects` WRITE;
/*!40000 ALTER TABLE `courses_subjects` DISABLE KEYS */;
INSERT INTO `courses_subjects` VALUES (1,'Business','BBA','Accounting','2025-07-13 07:07:34'),(2,'Business','BBA','Marketing','2025-07-13 07:07:34'),(3,'Business','BBA','Finance','2025-07-13 07:07:34'),(4,'Business','BBA','Management','2025-07-13 07:07:34'),(5,'Business','BBA','Organizational Behavior','2025-07-13 07:07:34'),(6,'Business','BBA','Business Communication','2025-07-13 07:07:34'),(7,'Business','BBS','Principles of Management','2025-07-13 07:07:34'),(8,'Business','BBS','Financial Accounting','2025-07-13 07:07:34'),(9,'Business','BBS','Business Statistics','2025-07-13 07:07:34'),(10,'Business','BBS','Entrepreneurship','2025-07-13 07:07:34'),(11,'Business','BBS','Taxation','2025-07-13 07:07:34'),(12,'Business','BBM','Business Policy','2025-07-13 07:07:34'),(13,'Business','BBM','Strategic Management','2025-07-13 07:07:34'),(14,'Business','BBM','Operations Management','2025-07-13 07:07:34'),(15,'Business','BBM','Human Resource Management','2025-07-13 07:07:34'),(16,'Business','MBA','Strategic Management','2025-07-13 07:07:34'),(17,'Business','MBA','Marketing Research','2025-07-13 07:07:34'),(18,'Business','MBA','Corporate Finance','2025-07-13 07:07:34'),(19,'Business','MBA','Leadership','2025-07-13 07:07:34'),(20,'Business','MBA','Operations Strategy','2025-07-13 07:07:34'),(21,'Business','MBS','Advanced Financial Accounting','2025-07-13 07:07:34'),(22,'Business','MBS','Business Environment','2025-07-13 07:07:34'),(23,'Business','MBS','Research Methodology','2025-07-13 07:07:34'),(24,'Business','MBS','Marketing Management','2025-07-13 07:07:34'),(25,'Technology','BCA','Programming in C','2025-07-13 07:07:34'),(26,'Technology','BCA','Data Structures','2025-07-13 07:07:34'),(27,'Technology','BCA','Database Management','2025-07-13 07:07:34'),(28,'Technology','BCA','Web Technologies','2025-07-13 07:07:34'),(29,'Technology','BCA','Operating Systems','2025-07-13 07:07:34'),(30,'Technology','BSc CSIT','Computer Organization','2025-07-13 07:07:34'),(31,'Technology','BSc CSIT','Networking','2025-07-13 07:07:34'),(32,'Technology','BSc CSIT','Software Engineering','2025-07-13 07:07:34'),(33,'Technology','BSc CSIT','Artificial Intelligence','2025-07-13 07:07:34'),(34,'Technology','BSc CSIT','Mobile Computing','2025-07-13 07:07:34'),(35,'Technology','BIT','Digital Logic','2025-07-13 07:07:34'),(36,'Technology','BIT','System Analysis','2025-07-13 07:07:34'),(37,'Technology','BIT','Multimedia','2025-07-13 07:07:34'),(38,'Technology','BIT','E-Commerce','2025-07-13 07:07:34'),(39,'Technology','BIT','Information Security','2025-07-13 07:07:34'),(40,'Technology','BIM','Information System','2025-07-13 07:07:34'),(41,'Technology','BIM','Project Management','2025-07-13 07:07:34'),(42,'Technology','BIM','Business Analytics','2025-07-13 07:07:34'),(43,'Technology','BIM','ERP','2025-07-13 07:07:34'),(44,'Technology','BIM','Database Systems','2025-07-13 07:07:34'),(45,'Engineering','Computer Engineering','Data Structures','2025-07-13 07:07:34'),(46,'Engineering','Computer Engineering','Microprocessor','2025-07-13 07:07:34'),(47,'Engineering','Computer Engineering','Algorithm Design','2025-07-13 07:07:34'),(48,'Engineering','Computer Engineering','Operating Systems','2025-07-13 07:07:34'),(49,'Engineering','Computer Engineering','Networking','2025-07-13 07:07:34'),(50,'Engineering','Civil Engineering','Surveying','2025-07-13 07:07:34'),(51,'Engineering','Civil Engineering','Structural Analysis','2025-07-13 07:07:34'),(52,'Engineering','Civil Engineering','Hydraulics','2025-07-13 07:07:34'),(53,'Engineering','Civil Engineering','Construction Materials','2025-07-13 07:07:34'),(54,'Engineering','Civil Engineering','Environmental Engineering','2025-07-13 07:07:34'),(55,'Engineering','Mechanical Engineering','Thermodynamics','2025-07-13 07:07:34'),(56,'Engineering','Mechanical Engineering','Fluid Mechanics','2025-07-13 07:07:34'),(57,'Engineering','Mechanical Engineering','Machine Design','2025-07-13 07:07:34'),(58,'Engineering','Mechanical Engineering','Production Engineering','2025-07-13 07:07:34'),(59,'Engineering','Mechanical Engineering','Heat Transfer','2025-07-13 07:07:34'),(60,'Engineering','Electrical and Electronics Engineering','Circuits Theory','2025-07-13 07:07:34'),(61,'Engineering','Electrical and Electronics Engineering','Power Systems','2025-07-13 07:07:34'),(62,'Engineering','Electrical and Electronics Engineering','Control Systems','2025-07-13 07:07:34'),(63,'Engineering','Electrical and Electronics Engineering','Electrical Machines','2025-07-13 07:07:34'),(64,'Engineering','Electrical and Electronics Engineering','Renewable Energy','2025-07-13 07:07:34'),(65,'Engineering','Architecture','Design Studio','2025-07-13 07:07:34'),(66,'Engineering','Architecture','History of Architecture','2025-07-13 07:07:34'),(67,'Engineering','Architecture','Building Construction','2025-07-13 07:07:34'),(68,'Engineering','Architecture','Urban Planning','2025-07-13 07:07:34'),(69,'Engineering','Architecture','Sustainable Design','2025-07-13 07:07:34'),(70,'Engineering','Agricultural Engineering','Irrigation Engineering','2025-07-13 07:07:34'),(71,'Engineering','Agricultural Engineering','Soil and Water Conservation','2025-07-13 07:07:34'),(72,'Engineering','Agricultural Engineering','Farm Machinery','2025-07-13 07:07:34'),(73,'Engineering','Agricultural Engineering','Post-Harvest Technology','2025-07-13 07:07:34'),(74,'Engineering','Geomatics Engineering','Surveying','2025-07-13 07:07:34'),(75,'Engineering','Geomatics Engineering','Remote Sensing','2025-07-13 07:07:34'),(76,'Engineering','Geomatics Engineering','GIS','2025-07-13 07:07:34'),(77,'Engineering','Geomatics Engineering','Photogrammetry','2025-07-13 07:07:34'),(78,'Engineering','Geomatics Engineering','GPS Technology','2025-07-13 07:07:34'),(79,'Medical','MBBS','Anatomy','2025-07-13 07:07:34'),(80,'Medical','MBBS','Physiology','2025-07-13 07:07:34'),(81,'Medical','MBBS','Pharmacology','2025-07-13 07:07:34'),(82,'Medical','MBBS','Pathology','2025-07-13 07:07:34'),(83,'Medical','MBBS','Medicine','2025-07-13 07:07:34'),(84,'Medical','MBBS','Pediatrics','2025-07-13 07:07:34'),(85,'Medical','MBBS','Obstetrics','2025-07-13 07:07:34'),(86,'Medical','BDS','Dental Anatomy','2025-07-13 07:07:34'),(87,'Medical','BDS','Prosthodontics','2025-07-13 07:07:34'),(88,'Medical','BDS','Orthodontics','2025-07-13 07:07:34'),(89,'Medical','BDS','Periodontology','2025-07-13 07:07:34'),(90,'Medical','BDS','Conservative Dentistry','2025-07-13 07:07:34'),(91,'Medical','BNS/BSc Nursing','Nursing Foundation','2025-07-13 07:07:34'),(92,'Medical','BNS/BSc Nursing','Community Health Nursing','2025-07-13 07:07:34'),(93,'Medical','BNS/BSc Nursing','Medical Surgical Nursing','2025-07-13 07:07:34'),(94,'Medical','BNS/BSc Nursing','Psychiatric Nursing','2025-07-13 07:07:34'),(95,'Medical','B.Pharm','Pharmaceutics','2025-07-13 07:07:34'),(96,'Medical','B.Pharm','Pharmacology','2025-07-13 07:07:34'),(97,'Medical','B.Pharm','Phytochemistry','2025-07-13 07:07:34'),(98,'Medical','B.Pharm','Clinical Pharmacy','2025-07-13 07:07:34'),(99,'Medical','B.Pharm','Pharmaceutical Analysis','2025-07-13 07:07:34'),(100,'Medical','BPH','Public Health Administration','2025-07-13 07:07:34'),(101,'Medical','BPH','Health Promotion','2025-07-13 07:07:34'),(102,'Medical','BPH','Biostatistics','2025-07-13 07:07:34'),(103,'Medical','BPH','Epidemiology','2025-07-13 07:07:34'),(104,'Medical','BPH','Environmental Health','2025-07-13 07:07:34'),(105,'Medical','BMLT','Clinical Pathology','2025-07-13 07:07:34'),(106,'Medical','BMLT','Hematology','2025-07-13 07:07:34'),(107,'Medical','BMLT','Microbiology','2025-07-13 07:07:34'),(108,'Medical','BMLT','Immunology','2025-07-13 07:07:34'),(109,'Medical','BMLT','Biochemistry','2025-07-13 07:07:34'),(110,'Medical','B.Optom','Optics','2025-07-13 07:07:34'),(111,'Medical','B.Optom','Ocular Anatomy','2025-07-13 07:07:34'),(112,'Medical','B.Optom','Contact Lenses','2025-07-13 07:07:34'),(113,'Medical','B.Optom','Vision Science','2025-07-13 07:07:34'),(114,'Medical','B.Optom','Low Vision','2025-07-13 07:07:34'),(115,'Medical','BPT','Human Anatomy','2025-07-13 07:07:34'),(116,'Medical','BPT','Physiotherapy Techniques','2025-07-13 07:07:34'),(117,'Medical','BPT','Neurological Physiotherapy','2025-07-13 07:07:34'),(118,'Medical','BPT','Sports Physiotherapy','2025-07-13 07:07:34'),(119,'Other','BHM','Hotel Operations','2025-07-13 07:07:34'),(120,'Other','BHM','Front Office Management','2025-07-13 07:07:34'),(121,'Other','BHM','Food and Beverage Service','2025-07-13 07:07:34'),(122,'Other','BHM','Housekeeping','2025-07-13 07:07:34'),(123,'Other','BHM','Hospitality Law','2025-07-13 07:07:34'),(124,'Other','BA','Sociology','2025-07-13 07:07:34'),(125,'Other','BA','Political Science','2025-07-13 07:07:34'),(126,'Other','BA','Economics','2025-07-13 07:07:34'),(127,'Other','BA','English Literature','2025-07-13 07:07:34'),(128,'Other','BA','Anthropology','2025-07-13 07:07:34'),(129,'Other','BSc','Physics','2025-07-13 07:07:34'),(130,'Other','BSc','Chemistry','2025-07-13 07:07:34'),(131,'Other','BSc','Mathematics','2025-07-13 07:07:34'),(132,'Other','BSc','Biology','2025-07-13 07:07:34'),(133,'Other','BSc','Computer Science','2025-07-13 07:07:34'),(134,'Other','BSW','Social Work Theories','2025-07-13 07:07:34'),(135,'Other','BSW','Community Development','2025-07-13 07:07:34'),(136,'Other','BSW','Social Policy','2025-07-13 07:07:34'),(137,'Other','BSW','Human Behavior','2025-07-13 07:07:34'),(138,'Other','BSW','Social Case Work','2025-07-13 07:07:34'),(139,'Other','B.Ed','Educational Psychology','2025-07-13 07:07:34'),(140,'Other','B.Ed','Curriculum Development','2025-07-13 07:07:34'),(141,'Other','B.Ed','Teaching Methods','2025-07-13 07:07:34'),(142,'Other','B.Ed','Classroom Management','2025-07-13 07:07:34'),(143,'Other','BFA','Drawing','2025-07-13 07:07:34'),(144,'Other','BFA','Painting','2025-07-13 07:07:34'),(145,'Other','BFA','Sculpture','2025-07-13 07:07:34'),(146,'Other','BFA','Art History','2025-07-13 07:07:34'),(147,'Other','BFA','Graphic Design','2025-07-13 07:07:34'),(148,'Other','LLB','Constitutional Law','2025-07-13 07:07:34'),(149,'Other','LLB','Criminal Law','2025-07-13 07:07:34'),(150,'Other','LLB','Contract Law','2025-07-13 07:07:34'),(151,'Other','LLB','Property Law','2025-07-13 07:07:34'),(152,'Other','LLB','Legal Writing','2025-07-13 07:07:34'),(153,'Other','BTTM','Travel Geography','2025-07-13 07:07:34'),(154,'Other','BTTM','Tourism Management','2025-07-13 07:07:34'),(155,'Other','BTTM','Airline Operations','2025-07-13 07:07:34'),(156,'Other','BTTM','Resort Management','2025-07-13 07:07:34'),(157,'Other','BTTM','Event Planning','2025-07-13 07:07:34'),(158,'Other','BDS','Development Studies','2025-07-13 07:07:34'),(159,'Other','BDS','Rural Development','2025-07-13 07:07:34'),(160,'Other','BDS','Poverty Reduction','2025-07-13 07:07:34'),(161,'Other','BDS','NGO Management','2025-07-13 07:07:34'),(162,'Other','BDS','Policy Analysis','2025-07-13 07:07:34'),(163,'Other','BBIS','Information Systems','2025-07-13 07:07:34'),(164,'Other','BBIS','Business Process','2025-07-13 07:07:34'),(165,'Other','BBIS','Database Management','2025-07-13 07:07:34'),(166,'Other','BBIS','Web Development','2025-07-13 07:07:34'),(167,'Other','BBIS','Project Management','2025-07-13 07:07:34'),(168,'Technology','BCA','Operational Research','2025-07-13 07:19:48'),(169,'Technology','BCA','GIS','2025-07-13 07:19:48'),(170,'Technology','BCA','Information Security','2025-07-13 07:19:48'),(177,'Technology','BCA','Micro-Processor','2025-07-13 09:07:32'),(178,'Technology','BCA','Advance Java','2025-07-16 10:50:57'),(179,'Technology','BCA','Advance Dotnet','2025-08-06 04:03:25'),(180,'Technology','BCA','Computer Fundamentals and Applications','2025-08-15 03:39:40'),(182,'Technology','BCA','Digital Logic','2025-08-15 03:39:40'),(183,'Technology','BCA','Mathematics-I','2025-08-15 03:39:40'),(184,'Technology','BCA','Professional Communication and Ethics','2025-08-15 03:39:40'),(185,'Technology','BCA','Hardware Workshop','2025-08-15 03:39:40'),(186,'Technology','BCA','Discrete Structure','2025-08-15 03:39:40'),(187,'Technology','BCA','OOP in Java','2025-08-15 03:39:40'),(188,'Technology','BCA','Mathematics-II','2025-08-15 03:39:40'),(189,'Technology','BCA','UX/UI Design','2025-08-15 03:39:40'),(190,'Technology','BCA','Data Structure and Algorithms','2025-08-15 03:39:40'),(191,'Technology','BCA','Database Management System','2025-08-15 03:39:40'),(192,'Technology','BCA','Web Technology-I','2025-08-15 03:39:40'),(193,'Technology','BCA','System Analysis and Design','2025-08-15 03:39:40'),(194,'Technology','BCA','Probability and Statistics','2025-08-15 03:39:40'),(195,'Technology','BCA','Applied Economics','2025-08-15 03:39:40'),(196,'Technology','BCA','Software Engineering','2025-08-15 03:39:40'),(197,'Technology','BCA','Numerical Methods','2025-08-15 03:41:11'),(198,'Technology','BCA','Python Programming','2025-08-15 03:41:11'),(199,'Technology','BCA','Web Technology-II','2025-08-15 03:41:11');
/*!40000 ALTER TABLE `courses_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jaycess_13`
--

DROP TABLE IF EXISTS `jaycess_13`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jaycess_13` (
  `id` int(11) NOT NULL,
  `org_name` varchar(255) DEFAULT NULL,
  `sub_admin_id` int(11) DEFAULT NULL,
  `teacher_reg` varchar(255) DEFAULT NULL,
  `student_reg` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subjects` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_teacher` (`teacher_reg`,`sub_admin_id`),
  UNIQUE KEY `unique_student` (`student_reg`,`sub_admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jaycess_13`
--

LOCK TABLES `jaycess_13` WRITE;
/*!40000 ALTER TABLE `jaycess_13` DISABLE KEYS */;
INSERT INTO `jaycess_13` VALUES (1,'JAYCESS',13,'r30056',NULL,'2025-06-08 10:31:48',NULL),(2,'JAYCESS',13,'n0069',NULL,'2025-07-16 10:38:12',NULL),(3,'JAYCESS',13,NULL,NULL,'2025-07-16 10:50:57','[\"Advance Java\"]'),(4,'JAYCESS',13,NULL,'40012118','2025-07-18 13:06:39',NULL),(5,'JAYCESS',13,NULL,NULL,'2025-08-06 16:08:08','[\"Database Management\",\"GIS\"]'),(6,'JAYCESS',13,NULL,NULL,'2025-08-06 16:15:03','[\"Advance Java\"]'),(7,'JAYCESS',13,NULL,NULL,'2025-08-06 16:16:25','[\"Advance Java\"]'),(8,'JAYCESS',13,NULL,NULL,'2025-08-06 16:16:58','[\"Programming in C\"]'),(9,'JAYCESS',13,NULL,NULL,'2025-08-06 16:19:11','[\"Web Technologies\"]'),(10,'JAYCESS',13,NULL,'40432020','2025-08-06 18:09:05',NULL),(11,'JAYCESS',13,NULL,'40442020','2025-08-06 18:09:23',NULL),(12,'JAYCESS',13,NULL,'40462021','2025-08-06 18:09:35',NULL),(13,'JAYCESS',13,NULL,'40472021','2025-08-06 18:09:41',NULL),(14,'JAYCESS',13,NULL,'40492021','2025-08-06 18:09:53',NULL),(15,'JAYCESS',13,NULL,'40502020','2025-08-06 18:29:26',NULL),(16,'JAYCESS',13,NULL,NULL,'2025-08-12 12:00:35','[\"Advance Java\"]');
/*!40000 ALTER TABLE `jaycess_13` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mvebs_19`
--

DROP TABLE IF EXISTS `mvebs_19`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvebs_19` (
  `id` int(11) NOT NULL,
  `org_name` varchar(255) DEFAULT NULL,
  `sub_admin_id` int(11) DEFAULT NULL,
  `teacher_reg` varchar(255) DEFAULT NULL,
  `student_reg` varchar(255) DEFAULT NULL,
  `subjects` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mvebs_19`
--

LOCK TABLES `mvebs_19` WRITE;
/*!40000 ALTER TABLE `mvebs_19` DISABLE KEYS */;
INSERT INTO `mvebs_19` VALUES (1,'MVEBS',19,'201',NULL,NULL,'2025-08-15 03:31:58'),(2,'MVEBS',19,'202',NULL,NULL,'2025-08-15 03:32:04'),(3,'MVEBS',19,'203',NULL,NULL,'2025-08-15 03:32:07'),(5,'MVEBS',19,NULL,NULL,'[\"Numerical Methods\",\"Python Programming\",\"Web Technology-II\"]','2025-08-15 03:41:11'),(6,'MVEBS',19,NULL,NULL,'[\"Computer Fundamentals and Applications\",\"Digital Logic\",\"Mathematics-I\",\"Professional Communication and Ethics\",\"Programming in C\"]','2025-08-15 03:43:10'),(7,'MVEBS',19,NULL,NULL,'[\"Discrete Structure\",\"Mathematics-II\",\"Micro-Processor\",\"OOP in Java\",\"UX\\/UI Design\"]','2025-08-15 03:45:17'),(8,'MVEBS',19,NULL,NULL,'[\"Applied Economics\",\"Data Structure and Algorithms\",\"Database Management System\",\"Probability and Statistics\",\"System Analysis and Design\",\"Web Technology-I\"]','2025-08-15 03:48:11'),(9,'MVEBS',19,'204',NULL,NULL,'2025-08-15 03:50:06'),(10,'MVEBS',19,'205',NULL,NULL,'2025-08-15 03:50:10'),(11,'MVEBS',19,'206',NULL,NULL,'2025-08-15 03:50:15'),(12,'MVEBS',19,'207',NULL,NULL,'2025-08-15 03:50:21'),(13,'MVEBS',19,'208',NULL,NULL,'2025-08-15 03:50:24'),(14,'MVEBS',19,'209',NULL,NULL,'2025-08-15 03:50:30'),(15,'MVEBS',19,'210',NULL,NULL,'2025-08-15 03:50:33'),(16,'MVEBS',19,NULL,'101',NULL,'2025-09-05 13:01:20'),(17,'MVEBS',19,NULL,'102',NULL,'2025-09-05 13:34:52');
/*!40000 ALTER TABLE `mvebs_19` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `replies`
--

DROP TABLE IF EXISTS `replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `reply` text NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `replies`
--

LOCK TABLES `replies` WRITE;
/*!40000 ALTER TABLE `replies` DISABLE KEYS */;
INSERT INTO `replies` VALUES (1,3,19,NULL,'Pritima School','we\'ll look for it','2025-09-05 23:54:38'),(2,1,19,NULL,'Pritima School','yes i can but i wont','2025-09-05 23:55:11'),(3,1,19,NULL,'Pralhad Adhikari','You are good','2025-09-05 23:55:32'),(4,1,19,NULL,'Rijan Subedi','We are trying our best to do that','2025-09-06 00:10:44'),(5,4,6,NULL,'Ratna Rajhya','we\'ll surely look for it','2025-09-06 00:26:25'),(6,5,6,NULL,'Ratna Rajhya','okay test is clear','2025-09-06 13:22:45');
/*!40000 ALTER TABLE `replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `org_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `schedule_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schedule_json`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (1,19,'BCA 1st sem','[{\"time\":\"09:00-10:00\",\"subject\":\"Digital Logic\",\"teacher\":\"Shyam K\"},{\"time\":\"10:00-11:00\",\"subject\":\"Professional Communication and Ethics\",\"teacher\":\"Abhi Sharma\"},{\"time\":\"11:00-12:00\",\"subject\":\"Programming in C\",\"teacher\":\"Akash Shyam\"},{\"time\":\"12:00-13:00\",\"subject\":\"Mathematics-I\",\"teacher\":\"Surya K\"},{\"time\":\"14:00-15:00\",\"subject\":\"Computer Fundamentals and Applications\",\"teacher\":\"Ravi Ram\"}]','2025-09-05 21:11:59'),(2,19,'BCA 2nd sem','[{\"time\":\"10:00-11:00\",\"subject\":\"Discrete Structure\",\"teacher\":\"Shyam K\"},{\"time\":\"12:00-13:00\",\"subject\":\"Micro-Processor\",\"teacher\":\"Sashi Shyam\"},{\"time\":\"13:00-14:00\",\"subject\":\"Mathematics-II\",\"teacher\":\"Surya K\"},{\"time\":\"14:00-15:00\",\"subject\":\"OOP in Java\",\"teacher\":\"Sunita Adhikari\"},{\"time\":\"15:00-16:00\",\"subject\":\"UX/UI Design\",\"teacher\":\"Akash Shyam\"}]','2025-09-05 21:11:59'),(3,19,'BCA 3rd sem','[{\"time\":\"09:00-10:00\",\"subject\":\"Database Management System\",\"teacher\":\"Ravi Ram\"},{\"time\":\"10:00-11:00\",\"subject\":\"Probability and Statistics\",\"teacher\":\"Rejeena Ghimire\"},{\"time\":\"12:00-13:00\",\"subject\":\"System Analysis and Design\",\"teacher\":\"Akash Shyam\"},{\"time\":\"13:00-14:00\",\"subject\":\"Applied Economics\",\"teacher\":\"Samjhana Karki\"},{\"time\":\"14:00-15:00\",\"subject\":\"Data Structure and Algorithms\",\"teacher\":\"Shyam K\"},{\"time\":\"15:00-16:00\",\"subject\":\"Web Technology-I\",\"teacher\":\"Sunita Adhikari\"}]','2025-09-05 21:11:59'),(4,6,'BCA 1st sem','[{\"time\":\"10:00-11:00\",\"subject\":\"GIS\",\"teacher\":\"Prasant GC\"},{\"time\":\"13:00-14:00\",\"subject\":\"Micro-Processor\",\"teacher\":\"Test3\"},{\"time\":\"14:00-15:00\",\"subject\":\"Database Management\",\"teacher\":\"Test4\"}]','2025-09-06 00:21:38'),(5,6,'BCA 2nd sem class','[{\"time\":\"11:00-12:00\",\"subject\":\"GIS\",\"teacher\":\"Test5\"},{\"time\":\"12:00-13:00\",\"subject\":\"Database Management\",\"teacher\":\"Test4\"},{\"time\":\"14:00-15:00\",\"subject\":\"Operating Systems\",\"teacher\":\"Test6\"},{\"time\":\"15:00-16:00\",\"subject\":\"Micro-Processor\",\"teacher\":\"Test3\"}]','2025-09-06 00:21:38'),(6,6,'BCA 3rd sem','[{\"time\":\"10:00-11:00\",\"subject\":\"Programming in C\",\"teacher\":\"Test5\"},{\"time\":\"13:00-14:00\",\"subject\":\"GIS\",\"teacher\":\"test1\"},{\"time\":\"14:00-15:00\",\"subject\":\"Web Technologies\",\"teacher\":\"Test5\"}]','2025-09-06 00:21:38'),(7,6,'BCA 4th sem','[{\"time\":\"12:00-13:00\",\"subject\":\"Information Security\",\"teacher\":\"Test2\"},{\"time\":\"15:00-16:00\",\"subject\":\"GIS\",\"teacher\":\"Test5\"},{\"time\":\"16:00-17:00\",\"subject\":\"Programming in C\",\"teacher\":\"Test5\"},{\"time\":\"17:00-18:00\",\"subject\":\"Operating Systems\",\"teacher\":\"Test4\"}]','2025-09-06 00:21:38'),(8,14,'BCA 1st sem','[{\"time\":\"09:00-10:00\",\"subject\":\"Digital Logic\",\"teacher\":\"Shyam K\"},{\"time\":\"10:00-11:00\",\"subject\":\"Professional Communication and Ethics\",\"teacher\":\"Abhi Sharma\"}]','2025-11-05 00:00:00'),(9,14,'BCA 2nd sem','[{\"time\":\"10:00-11:00\",\"subject\":\"Discrete Structure\",\"teacher\":\"Shyam K\"},{\"time\":\"12:00-13:00\",\"subject\":\"Micro-Processor\",\"teacher\":\"Sashi Shyam\"}]','2025-11-05 00:00:00'),(10,14,'BCA 3rd sem','[{\"time\":\"09:00-10:00\",\"subject\":\"Database Management System\",\"teacher\":\"Ravi Ram\"},{\"time\":\"10:00-11:00\",\"subject\":\"Probability and Statistics\",\"teacher\":\"Rejeena Ghimire\"}]','2025-11-05 00:00:00');
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `org_id` varchar(30) NOT NULL,
  `class` varchar(100) NOT NULL,
  `class_id` int(100) NOT NULL,
  `tick` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` VALUES (2,'s100','Nishan Karki','nishan1@gmail.com','12345','14','BCA 3rd sem',10,1,'2025-06-12 01:19:32'),(3,'p100','Pralhad Adhikari','p@gmail.com','12345','15','',0,1,'2025-07-13 03:12:43'),(0,'400','Sathi mitra','sathi@gmail.com','12345','6','BCA 2nd sem class',29,0,'2025-09-06 07:23:23');
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_admin`
--

DROP TABLE IF EXISTS `sub_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_admin` (
  `id` int(11) NOT NULL,
  `org_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(30) NOT NULL,
  `org_type` enum('college','campus','school','organization') NOT NULL,
  `password` varchar(255) NOT NULL,
  `certificate` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_admin`
--

LOCK TABLES `sub_admin` WRITE;
/*!40000 ALTER TABLE `sub_admin` DISABLE KEYS */;
INSERT INTO `sub_admin` VALUES (6,'CAB','cab@gmail.com','','campus','12345678','uploads/certificates/684187b7f0838.jpg','2025-06-05 06:19:08',1),(13,'JAYCESS','jaycess@gmail.com','Kathmandu','organization','Rijan@1234','../admin/Uploads/certificates/684565659295c.jpg','2025-06-08 04:41:45',1),(14,'SAINIK','sainik@gmail.com','Kathmandu','school','Harion@11','../admin/Uploads/certificates/684a69a958fa0.jpg','2025-06-12 00:01:17',1);
/*!40000 ALTER TABLE `sub_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `email` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `subjects` varchar(100) NOT NULL,
  `available` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `org_id` varchar(30) NOT NULL,
  `tick` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher`
--

LOCK TABLES `teacher` WRITE;
/*!40000 ALTER TABLE `teacher` DISABLE KEYS */;
INSERT INTO `teacher` VALUES (8,'r30056','yamuna@gmail.com','Rijan Subedi','123','BHM','Housekeeping,Food and Beverage Service','10:00-11:00,09:00-10:00,08:00-09:00','9866123377','13','1','2025-06-09 00:11:37'),(9,'a100','sahil100@gmail.com','Sahil Oli','Sahil@100','sahil@100','','','9844046079','14','0','2025-06-12 00:10:53'),(10,'p100','p@gmail.com','Pralhad Adhikari','Intern@100','BCA','Micro-Processor','10:00-11:00','9866123377','15','1','2025-07-13 03:09:48'),(11,'n0069','nishan69@gmail.com','Nishan Raj Karki','Intern@100','BCA','Database Management, GIS, Programming in C, Web Technologies, Operating Systems','10:00-11:00,11:00-12:00','9761789679','13','1','2025-07-16 04:54:55'),(15,'1','test1@gmail.com','test1','Intern@100','BCA','GIS,Database Management','10:00-11:00,09:00-10:00,11:00-12:00,12:00-13:00','9866123377','6','1','2025-08-07 04:51:01'),(16,'2','test2@gmail.com','Test2','Intern@100','BCA','Operating Systems,GIS,Information Security','14:00-15:00,12:00-13:00,10:00-11:00,16:00-17:00','9866123377','6','1','2025-08-07 04:57:59'),(17,'3','test3@gmail.com','Test3','Intern@100','BCA','Micro-Processor,Data Structures,Advance Dotnet,GIS','16:00-17:00,15:00-16:00,14:00-15:00,12:00-13:00,11:00-12:00','9866123377','6','1','2025-08-07 04:59:09'),(18,'4','test4@gmail.com','Test4','Intern@100','BCA','Operating Systems,Advance Dotnet,Database Management','09:00-10:00,10:00-11:00,11:00-12:00,12:00-13:00','9844046079','6','1','2025-08-07 05:00:36'),(19,'5','test5@gmail.com','Test5','Intern@100','BCA','Operating Systems,Web Technologies,GIS,Programming in C','10:00-11:00,12:00-13:00,14:00-15:00,11:00-12:00,15:00-16:00','9844046079','6','1','2025-08-07 05:02:04'),(20,'6','test6@gmail.com','Test6','Intern@100','BCA','Operating Systems','10:00-11:00','9761789679','6','1','2025-08-07 08:21:39');
/*!40000 ALTER TABLE `teacher` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-06 13:23:37
