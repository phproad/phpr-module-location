/*Table structure for table `location_currency_rates` */
CREATE TABLE `location_currency_rates` (
  `ident` varchar(6) NOT NULL,
  `rate` decimal(12,5) DEFAULT NULL,
  `cached_date` date DEFAULT NULL,
  PRIMARY KEY (`ident`),
  KEY `rate_date` (`rate`,`cached_date`)
) DEFAULT CHARSET=utf8;