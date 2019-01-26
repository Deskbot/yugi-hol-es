--mysql flavour

CREATE TABLE IF NOT EXISTS `ygh_Pledge` (
	`PledgeID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`RequestID` int(10) unsigned NOT NULL,
	`PledgeMakerID` int(10) unsigned NOT NULL,
	`QuantityPledged` smallint(5) unsigned NOT NULL,
	`QuantityFulfilled` smallint(5) NOT NULL,
	`Time` int(10) unsigned NOT NULL,
	PRIMARY KEY (`PledgeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `ygh_Request` (
	`RequestID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`UserID` int(10) unsigned NOT NULL,
	`CardName` varchar(55) COLLATE utf8_bin NOT NULL,
	`CardSet` varchar(40) COLLATE utf8_bin NOT NULL,
	`CardEdition` char(1) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`Quantity` smallint(5) unsigned DEFAULT NULL,
	`StartTime` int(10) unsigned NOT NULL,
	`EndTime` int(10) unsigned DEFAULT NULL,
	`Note` text COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`RequestID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE IF NOT EXISTS `ygh_User` (
	`UserID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	`SecretID` int(10) unsigned NOT NULL,
	PRIMARY KEY (`UserID`),
	KEY `SecretID` (`SecretID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
