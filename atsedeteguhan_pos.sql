-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: atsedeteguhan_pos
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `atsedeteguhan_pos`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `atsedeteguhan_pos` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `atsedeteguhan_pos`;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_date` (`created_at`),
  KEY `idx_audit_branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,2,'ßê░ßêïßê¥ ßèáßêàßêÿßï╡','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-24 10:05:24'),(2,2,'ßê░ßêïßê¥ ßèáßêàßêÿßï╡','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-24 10:08:20'),(3,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-24 10:08:33'),(4,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-24 10:10:46'),(5,2,'ßê░ßêïßê¥ ßèáßêàßêÿßï╡','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-24 10:10:54'),(6,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-06-25 14:39:44'),(7,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Linux; Android 16; SM-N986B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.6778.260 Mobile Safari/537.36','Mobile',1,'2026-06-25 15:44:31'),(8,2,'ßê░ßêïßê¥ ßèáßêàßêÿßï╡','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 19:16:10'),(9,2,'ßê░ßêïßê¥ ßèáßêàßêÿßï╡','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 19:35:07'),(10,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 19:35:22'),(11,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 19:37:13'),(12,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 20:12:23'),(13,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 20:12:33'),(14,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:14:32'),(15,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',3,'1','0','ßëÇßèÉ: ßêÿßï¥ßêÖßê¡ ßêÿßî╜ßêÉßìì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:20:56'),(16,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',3,'0','1','ßë░ßëâßèÉ: ßêÿßï¥ßêÖßê¡ ßêÿßî╜ßêÉßìì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:21:04'),(17,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','ADD_PRODUCT','products',11,NULL,'{\"name\":\"Tesfa\",\"unit\":\"KG\"}','ßê¥ßê¡ßë╡ ßë░ßî¿ßê¥ßê»ßêì: Tesfa','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:22:03'),(18,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:25:08'),(19,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:25:24'),(20,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:26:00'),(21,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','10.59.9.221','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','Mobile',1,'2026-07-05 20:26:12'),(22,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_USER','users',3,'1','0','ßèáßëüßêƒßë╕ßïïßêì: ßï│ßïèßë╡ ßë│ßï░ßê░','10.59.9.221','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 20:32:15'),(23,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_USER','users',3,'0','1','ßèáßê╡ßîÇßê¥ßê»ßë╕ßïïßêì: ßï│ßïèßë╡ ßë│ßï░ßê░','10.59.9.221','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-05 20:32:23'),(24,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',1,NULL,'{\"total\":235,\"payment\":\"cash\",\"items\":2}','ßê╜ßï½ßî¡: RCP-260707-0001 ΓÇö 235.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 14:28:13'),(25,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',2,NULL,'{\"total\":940,\"payment\":\"cash\",\"items\":2}','ßê╜ßï½ßî¡: RCP-260707-0002 ΓÇö 940.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 14:29:36'),(26,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',3,NULL,'{\"total\":885,\"payment\":\"bank\",\"items\":4}','ßê╜ßï½ßî¡: RCP-260707-0003 ΓÇö 885.00 ßëÑßê¡','::1','Mozilla/5.0 (Linux; Android 16; SM-N986B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.6778.260 Mobile Safari/537.36','Mobile',1,'2026-07-07 14:31:05'),(27,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',4,NULL,'{\"total\":435,\"payment\":\"bank\",\"items\":3}','ßê╜ßï½ßî¡: RCP-260707-0004 ΓÇö 435.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 14:33:48'),(28,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 14:34:26'),(29,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 14:34:33'),(30,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:07:33'),(31,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:07:38'),(32,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:08:06'),(33,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:10:15'),(34,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:10:30'),(35,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',5,NULL,'{\"total\":705,\"payment\":\"bank\",\"items\":2}','ßê╜ßï½ßî¡: RCP-260707-0005 ΓÇö 705.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:11:19'),(36,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:12:35'),(37,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:12:48'),(38,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:48:20'),(39,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',6,NULL,'{\"total\":435,\"payment\":\"cash\",\"items\":3}','ßê╜ßï½ßî¡: RCP-260707-0006 ΓÇö 435.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:50:14'),(40,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:59:18'),(41,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 18:59:30'),(42,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:05:05'),(43,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:05:22'),(44,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:10:53'),(45,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:11:02'),(46,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',7,NULL,'{\"total\":2600,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: RCP-260707-0007 ΓÇö 2,600.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:11:44'),(47,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',8,NULL,'{\"total\":200,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: RCP-260707-0008 ΓÇö 200.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:11:56'),(48,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',9,NULL,'{\"total\":550,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: RCP-260707-0009 ΓÇö 550.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:17:12'),(49,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',11,'1','0','ßëÇßèÉ: Tesfa','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:21:59'),(50,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',11,'0','1','ßë░ßëâßèÉ: Tesfa','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:22:09'),(51,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',10,NULL,'{\"total\":585,\"payment\":\"cash\",\"items\":2}','ßê╜ßï½ßî¡: RCP-260707-0010 ΓÇö 585.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:41:40'),(52,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:54:43'),(53,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:55:00'),(54,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:55:10'),(55,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 19:55:19'),(56,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_USER','users',3,'1','0','ßèáßëüßêƒßë╕ßïïßêì: ßï│ßïèßë╡ ßë│ßï░ßê░','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 20:22:02'),(57,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_USER','users',3,'0','1','ßèáßê╡ßîÇßê¥ßê»ßë╕ßïïßêì: ßï│ßïèßë╡ ßë│ßï░ßê░','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 20:22:08'),(58,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','ADD_EXPENSE','expenses',1,NULL,'{\"amount\":100,\"category\":\"\\u1325\\u1308\\u1293\"}','ßïêßî¬: ßîÑßîêßèô ΓÇö 100 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 20:24:29'),(59,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 21:51:40'),(60,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','EDIT_USER','users',2,'{\"name\":\"\\u1230\\u120b\\u121d \\u1270\\u1235\\u134b\\u12ec\",\"role\":\"seller\"}','{\"name\":\"\\u1230\\u120b\\u121d \\u1270\\u1235\\u134b\\u12ec\",\"role\":\"seller\"}','ßë░ßîáßëâßêÜ ßë░ßê╡ßë░ßè½ßè¡ßêÅßêì: ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 22:02:37'),(61,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 22:02:45'),(62,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 22:02:56'),(63,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 22:05:14'),(64,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-07 22:05:24'),(65,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 01:03:52'),(66,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 08:43:17'),(67,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',11,NULL,'{\"total\":550,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00001 ΓÇö 550.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 09:10:30'),(68,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 09:14:48'),(69,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 09:14:56'),(70,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 11:16:44'),(71,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 18:39:16'),(72,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',13,NULL,'{\"qty\":100,\"buy\":999.98,\"sell\":1500.01,\"batch\":\"B-011-003\"}','ßè¡ßê¥ßë╜ßë╡: Tesfa 2 ΓÇö 100','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 18:50:41'),(73,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',14,NULL,'{\"qty\":10,\"buy\":0,\"sell\":35,\"batch\":\"B-007-002\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 18:52:34'),(74,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',12,NULL,'{\"total\":108350,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00012 ΓÇö 108,350.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 18:53:07'),(75,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',15,NULL,'{\"qty\":10,\"buy\":100,\"sell\":150,\"batch\":\"B-011-004\"}','ßè¡ßê¥ßë╜ßë╡: Tesfa 2 ΓÇö 10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 18:53:48'),(76,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',16,NULL,'{\"qty\":100,\"buy\":99.98,\"sell\":149.98,\"batch\":\"B-011-001\"}','ßè¡ßê¥ßë╜ßë╡: Tesfa 2 ΓÇö 100','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:14:57'),(77,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',17,NULL,'{\"qty\":10,\"buy\":100,\"sell\":150,\"batch\":\"B-007-001\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:16:01'),(78,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',18,NULL,'{\"qty\":50,\"buy\":450,\"sell\":500,\"batch\":\"B-003-001\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßï¥ßêÖßê¡ ßêÿßî╜ßêÉßìì ΓÇö 50','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:19:39'),(79,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','EDIT_PRODUCT','products',11,'{\"name\":\"Tesfa 2\",\"unit\":\"KG\"}','{\"name\":\"Tesfa 2\",\"unit\":\"KG\"}','ßê¥ßê¡ßë╡ ßë░ßê╡ßë░ßè½ßè¡ßêÅßêì: Tesfa 2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:23:19'),(80,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',11,'1','0','ßëÇßèÉ: Tesfa 2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:32:11'),(81,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','TOGGLE_PRODUCT','products',11,'0','1','ßë░ßëâßèÉ: Tesfa 2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:32:23'),(82,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',13,NULL,'{\"total\":300,\"payment\":\"cash\",\"items\":2}','ßê╜ßï½ßî¡: ATS00001 ΓÇö 300.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:37:34'),(83,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',14,NULL,'{\"total\":300,\"payment\":\"mixed\",\"items\":2}','ßê╜ßï½ßî¡: ATS00002 ΓÇö 300.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 19:40:38'),(84,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-08 23:09:47'),(85,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:15:13'),(86,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',15,NULL,'{\"total\":150,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00003 ΓÇö 150.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:19:24'),(87,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',16,NULL,'{\"total\":300,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00004 ΓÇö 300.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:20:19'),(88,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',19,NULL,'{\"qty\":10,\"buy\":150,\"sell\":350,\"batch\":\"ATSs00004\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:35:00'),(89,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',17,NULL,'{\"total\":1050,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00005 ΓÇö 1,050.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:35:19'),(90,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',18,NULL,'{\"total\":150,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00006 ΓÇö 150.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:35:23'),(91,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',19,NULL,'{\"total\":3500,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00007 ΓÇö 3,500.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:35:36'),(92,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',20,NULL,'{\"qty\":500,\"buy\":400,\"sell\":500,\"batch\":\"ATSs00005\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 500','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:36:12'),(93,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',21,NULL,'{\"qty\":100,\"buy\":100,\"sell\":100,\"batch\":\"ATSs00006\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 100','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:36:39'),(94,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',20,NULL,'{\"total\":249500,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00008 ΓÇö 249,500.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:36:55'),(95,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',21,NULL,'{\"total\":500,\"payment\":\"cash\",\"items\":1}','ßê╜ßï½ßî¡: ATS00009 ΓÇö 500.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-10 15:36:59'),(96,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:21:46'),(97,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:22:31'),(98,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:25:22'),(99,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',22,NULL,'{\"total\":150,\"payment\":\"mixed\",\"items\":1}','ßê╜ßï½ßî¡: ATS00010 ΓÇö 150.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:31:20'),(100,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:32:06'),(101,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:32:17'),(102,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',22,NULL,'{\"qty\":99.98,\"buy\":500,\"sell\":600,\"batch\":\"ATSs00007\"}','ßè¡ßê¥ßë╜ßë╡: Tesfa 2 ΓÇö 99.98','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 11:37:41'),(103,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 13:20:08'),(104,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGIN','users',1,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:30:34'),(105,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',23,NULL,'{\"qty\":100,\"buy\":50,\"sell\":60,\"batch\":\"ATSs00001\"}','ßè¡ßê¥ßë╜ßë╡: Tesfa 2 ΓÇö 100','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:35:12'),(106,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','RECEIVE_STOCK','stock_batches',24,NULL,'{\"qty\":50,\"buy\":10,\"sell\":15,\"batch\":\"ATSs00002\"}','ßè¡ßê¥ßë╜ßë╡: ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½ ΓÇö 50','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:35:32'),(107,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','SALE','transactions',23,NULL,'{\"total\":60,\"payment\":\"bank\",\"items\":1}','ßê╜ßï½ßî¡: ATS00001 ΓÇö 60.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:37:05'),(108,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','ADD_EXPENSE','expenses',2,NULL,'{\"amount\":15,\"category\":\"\\u120c\\u120e\\u127d\"}','ßïêßî¬: ßêîßêÄßë╜ ΓÇö 15 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:44:32'),(109,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','DELETE_EXPENSE','expenses',2,NULL,NULL,'ßïêßî¬ ßë░ßê░ßê¡ßïƒßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:45:25'),(110,1,'ßèáßê╡ßë░ßï│ßï│ßê¬','LOGOUT','users',1,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:45:35'),(111,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:45:52'),(112,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',24,NULL,'{\"total\":75,\"payment\":\"mobile\",\"items\":2}','ßê╜ßï½ßî¡: ATS00002 ΓÇö 75.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:48:49'),(113,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGOUT','users',2,NULL,NULL,'ßè¿ßê╡ßê¡ßïôßë▒ ßïêßîÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 19:49:06'),(114,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 20:22:04'),(115,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','SALE','transactions',25,NULL,'{\"total\":90,\"payment\":\"bank\",\"items\":2}','ßê╜ßï½ßî¡: ATS00003 ΓÇö 90.00 ßëÑßê¡','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 20:23:46'),(116,2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','LOGIN','users',2,NULL,NULL,'ßïêßï░ ßê╡ßê¡ßïôßë▒ ßîêßëÑßë╖ßêì','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Desktop',1,'2026-07-11 21:18:00');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'ßïïßèô ßëàßê¡ßèòßî½ßìì','ßèáßï▓ßê╡ ßèáßëáßëú','+251911000000',1,'2026-06-24 10:05:11');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_exp_date` (`expense_date`),
  KEY `idx_exp_branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_edit_history`
--

DROP TABLE IF EXISTS `product_edit_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_edit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `field_changed` varchar(100) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_peh_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_edit_history`
--

LOCK TABLES `product_edit_history` WRITE;
/*!40000 ALTER TABLE `product_edit_history` DISABLE KEYS */;
INSERT INTO `product_edit_history` VALUES (4,11,1,'STOCK_RECEIVE',NULL,'{\"qty\":100,\"buy\":99.98,\"sell\":149.98,\"batch\":\"B-011-001\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 100','::1','Desktop','2026-07-08 19:14:57'),(5,7,1,'STOCK_RECEIVE',NULL,'{\"qty\":10,\"buy\":100,\"sell\":150,\"batch\":\"B-007-001\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 10','::1','Desktop','2026-07-08 19:16:00'),(6,3,1,'STOCK_RECEIVE',NULL,'{\"qty\":50,\"buy\":450,\"sell\":500,\"batch\":\"B-003-001\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 50','::1','Desktop','2026-07-08 19:19:38'),(7,11,1,'PRODUCT_EDIT','{\"name\":\"Tesfa 2\",\"unit\":\"KG\"}','{\"name\":\"Tesfa 2\",\"unit\":\"KG\"}','','::1','Desktop','2026-07-08 19:23:19'),(8,7,1,'STOCK_RECEIVE',NULL,'{\"qty\":10,\"buy\":150,\"sell\":350,\"batch\":\"ATSs00004\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 10','::1','Desktop','2026-07-10 15:35:00'),(9,7,1,'STOCK_RECEIVE',NULL,'{\"qty\":500,\"buy\":400,\"sell\":500,\"batch\":\"ATSs00005\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 500','::1','Desktop','2026-07-10 15:36:12'),(10,7,1,'STOCK_RECEIVE',NULL,'{\"qty\":100,\"buy\":100,\"sell\":100,\"batch\":\"ATSs00006\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 100','::1','Desktop','2026-07-10 15:36:39'),(11,11,1,'STOCK_RECEIVE',NULL,'{\"qty\":99.98,\"buy\":500,\"sell\":600,\"batch\":\"ATSs00007\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 99.98','::1','Desktop','2026-07-11 11:37:41'),(12,11,1,'STOCK_RECEIVE',NULL,'{\"qty\":100,\"buy\":50,\"sell\":60,\"batch\":\"ATSs00001\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 100','::1','Desktop','2026-07-11 19:35:12'),(13,7,1,'STOCK_RECEIVE',NULL,'{\"qty\":50,\"buy\":10,\"sell\":15,\"batch\":\"ATSs00002\"}','ßè¡ßê¥ßë╜ßë╡ ßë░ßëÇßëÑßêÅßêì: 50','::1','Desktop','2026-07-11 19:35:32');
/*!40000 ALTER TABLE `product_edit_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `unit` enum('PCS','KG','LITER','BOX','PACK') DEFAULT 'PCS',
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `min_stock` decimal(10,2) DEFAULT 5.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_barcode` (`barcode`),
  KEY `idx_product_name` (`name`),
  KEY `idx_product_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'ßêÿßî╜ßêÉßìì ßëàßï▒ßê╡ (ßèáßê¢ßê¡ßè¢)','PCS',NULL,'ßï¿ßèáßê¢ßê¡ßè¢ ßêÿßî╜ßêÉßìì ßëàßï▒ßê╡',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(2,'ßêÿßî╜ßêÉßìì ßëàßï▒ßê╡ (ßèÑßèòßîìßêèßï¥ßè¢)','PCS',NULL,'ßï¿ßèÑßèòßîìßêèßï¥ßè¢ ßêÿßî╜ßêÉßìì ßëàßï▒ßê╡',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(3,'ßêÿßï¥ßêÖßê¡ ßêÿßî╜ßêÉßìì','PCS',NULL,'ßï¿ßëñßë░ßè¡ßê¡ßê╡ßë▓ßï½ßèò ßêÿßï¥ßêÖßê¡',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(4,'ßï¿ßê░ßèòßëáßë╡ ßë╡ßê¥ßêàßê¡ßë╡ ßêÿßî╜ßêÉßìì','PCS',NULL,'ßê░ßèòßëáßë╡ ßë╡ßê¥ßêàßê¡ßë╡',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(5,'ßê¢ßê╡ßë│ßïêßê╗ ßï░ßëÑßë░ßê¡','PCS',NULL,'ßê¢ßê╡ßë│ßïêßê╗ ßï░ßëÑßë░ßê¡',NULL,NULL,NULL,10.00,1,1,'2026-06-24 10:05:12'),(6,'ßèÑßê╡ßè¡ßê¬ßëÑßë╢','PCS',NULL,'ßèÑßê╡ßè¡ßê¬ßëÑßë╢',NULL,NULL,NULL,20.00,1,1,'2026-06-24 10:05:12'),(7,'ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½','PCS',NULL,'ßêÿßê╡ßëÇßêì ßê¢ßèòßîáßêìßîáßï½',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(8,'ßï¿ßî╕ßêÄßë╡ ßêÿßî╜ßêÉßìì','PCS',NULL,'ßï¿ßî╕ßêÄßë╡ ßêÿßî╜ßêÉßìì',NULL,NULL,NULL,5.00,1,1,'2026-06-24 10:05:12'),(9,'ßê╗ßê¢','PCS',NULL,'ßê╗ßê¢',NULL,NULL,NULL,10.00,1,1,'2026-06-24 10:05:12'),(10,'ßï¿ßëñßë░ßè¡ßê¡ßê╡ßë▓ßï½ßèò ßèáßêìßëáßê¥','PCS',NULL,'ßï¿ßëñßë░ßè¡ßê¡ßê╡ßë▓ßï½ßèò ßìÄßë╢ ßèáßêìßëáßê¥',NULL,NULL,NULL,3.00,1,1,'2026-06-24 10:05:12'),(11,'Tesfa 2','KG','tesfa','Tesfa for test','uploads/products/prod_1783447735_24507bf9.jpg','123456789',NULL,15.00,1,1,'2026-07-05 20:22:02');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'business_name','ßèáßî╕ßï░ ßë╡ßîëßêâßèò ßê░ßèòßëáßë╡ ßë╡ßê¥ßêàßê¡ßë╡ ßëñßë╡','2026-06-24 10:05:14'),(2,'business_address','ßèáßï▓ßê╡ ßèáßëáßëú, ßèóßë╡ßï«ßî╡ßï½','2026-06-24 10:05:14'),(3,'business_phone','+251911000000','2026-06-24 10:05:14'),(4,'tin_number','','2026-06-24 10:05:14'),(5,'receipt_footer','ßèÑßîìßïÜßèáßëÑßêößê¡ ßï¡ßëúßê¡ßè¡ßïÄ! Γ£¥','2026-06-24 10:05:14'),(6,'low_stock_threshold','5','2026-06-24 10:05:14'),(7,'theme','dark','2026-07-05 20:33:25'),(8,'session_timeout','30','2026-06-24 10:05:14'),(9,'currency_symbol','ßëÑßê¡','2026-06-24 10:05:14'),(10,'logo','','2026-06-24 10:05:14');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_batches`
--

DROP TABLE IF EXISTS `stock_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT 1,
  `batch_number` varchar(50) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `quantity_received` decimal(10,2) NOT NULL,
  `quantity_remaining` decimal(10,2) NOT NULL,
  `buy_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sell_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `received_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_depleted` tinyint(1) DEFAULT 0,
  `date_received` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_batch_fifo` (`product_id`,`is_depleted`,`created_at`),
  KEY `idx_batch_branch` (`branch_id`),
  KEY `idx_batch_supplier` (`supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_batches`
--

LOCK TABLES `stock_batches` WRITE;
/*!40000 ALTER TABLE `stock_batches` DISABLE KEYS */;
INSERT INTO `stock_batches` VALUES (23,11,1,'ATSs00001','','',100.00,97.00,50.00,60.00,1,'',0,'2026-07-11','2026-07-11 19:35:12'),(24,7,1,'ATSs00002','','',50.00,47.00,10.00,15.00,1,'',0,'2026-07-11','2026-07-11 19:35:32');
/*!40000 ALTER TABLE `stock_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `buy_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ti_transaction` (`transaction_id`),
  KEY `idx_ti_product` (`product_id`),
  KEY `idx_ti_batch` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
INSERT INTO `transaction_items` VALUES (37,23,11,23,1.00,50.00,60.00,60.00,10.00),(38,24,7,24,1.00,10.00,15.00,15.00,5.00),(39,24,11,23,1.00,50.00,60.00,60.00,10.00),(40,25,7,24,2.00,10.00,15.00,30.00,10.00),(41,25,11,23,1.00,50.00,60.00,60.00,10.00);
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(30) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `bank_name` varchar(50) DEFAULT NULL,
  `cash_paid` decimal(10,2) DEFAULT 0.00,
  `bank_paid` decimal(10,2) DEFAULT 0.00,
  `mobile_paid` decimal(10,2) DEFAULT 0.00,
  `change_given` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `idempotency_key` varchar(64) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt` (`receipt_number`),
  UNIQUE KEY `uq_idem` (`idempotency_key`),
  KEY `idx_txn_date` (`transaction_date`),
  KEY `idx_txn_seller` (`seller_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (23,'ATS00001',1,1,60.00,0.00,60.00,'bank','CBE',0.00,60.00,0.00,0.00,NULL,'sale_1783798625038_ngziwfwts','2026-07-11 19:37:05'),(24,'ATS00002',2,1,75.00,0.00,75.00,'mobile','',0.00,0.00,75.00,0.00,NULL,'sale_1783799329067_7a91ms1na','2026-07-11 19:48:49'),(25,'ATS00003',2,1,90.00,0.00,90.00,'bank','Bank of Abyssinia',0.00,90.00,0.00,0.00,NULL,'sale_1783801426024_36nbxel6p','2026-07-11 20:23:46');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller') DEFAULT 'seller',
  `branch_id` int(11) DEFAULT 1,
  `phone` varchar(30) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  KEY `fk_users_branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ßèáßê╡ßë░ßï│ßï│ßê¬','admin','$2y$10$5d6IAQj/zDO5nn4iejmb6u9Q9nPGI7zW/vI2OEs2CZcTDvDLRslfm','admin',1,'+251911000001',NULL,1,'2026-07-11 19:30:34','2026-06-24 10:05:11'),(2,'ßê░ßêïßê¥ ßë░ßê╡ßìïßï¼','selam','$2y$10$5d6IAQj/zDO5nn4iejmb6u9Q9nPGI7zW/vI2OEs2CZcTDvDLRslfm','seller',1,'+251911000002','uploads/users/user_1783461757_e5105770.jpg',1,'2026-07-11 21:18:00','2026-06-24 10:05:11'),(3,'ßï│ßïèßë╡ ßë│ßï░ßê░','dawit','$2y$10$5d6IAQj/zDO5nn4iejmb6u9Q9nPGI7zW/vI2OEs2CZcTDvDLRslfm','seller',1,'+251911000003',NULL,1,NULL,'2026-06-24 10:05:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_product_stock`
--

DROP TABLE IF EXISTS `v_product_stock`;
/*!50001 DROP VIEW IF EXISTS `v_product_stock`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_product_stock` AS SELECT
 1 AS `product_id`,
  1 AS `product_name`,
  1 AS `unit`,
  1 AS `min_stock`,
  1 AS `is_active`,
  1 AS `total_stock`,
  1 AS `current_sell_price`,
  1 AS `current_buy_price`,
  1 AS `current_batch_id` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'atsedeteguhan_pos'
--

--
-- Current Database: `atsedeteguhan_pos`
--

USE `atsedeteguhan_pos`;

--
-- Final view structure for view `v_product_stock`
--

/*!50001 DROP VIEW IF EXISTS `v_product_stock`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_product_stock` AS select `p`.`id` AS `product_id`,`p`.`name` AS `product_name`,`p`.`unit` AS `unit`,`p`.`min_stock` AS `min_stock`,`p`.`is_active` AS `is_active`,coalesce(sum(`sb`.`quantity_remaining`),0) AS `total_stock`,(select `sb2`.`sell_price` from `stock_batches` `sb2` where `sb2`.`product_id` = `p`.`id` and `sb2`.`is_depleted` = 0 order by `sb2`.`created_at` limit 1) AS `current_sell_price`,(select `sb2`.`buy_price` from `stock_batches` `sb2` where `sb2`.`product_id` = `p`.`id` and `sb2`.`is_depleted` = 0 order by `sb2`.`created_at` limit 1) AS `current_buy_price`,(select `sb2`.`id` from `stock_batches` `sb2` where `sb2`.`product_id` = `p`.`id` and `sb2`.`is_depleted` = 0 order by `sb2`.`created_at` limit 1) AS `current_batch_id` from (`products` `p` left join `stock_batches` `sb` on(`sb`.`product_id` = `p`.`id` and `sb`.`is_depleted` = 0)) where `p`.`is_active` = 1 group by `p`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-25  2:06:25
