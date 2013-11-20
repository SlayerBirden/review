<?php
/**
 * Maketok ProductReviews module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maketok.info@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Maketok ProductReviews to newer
 * versions in the future. If you wish to customize Maketok ProductReviews for your
 * needs please refer to http://www.maketok.com for more information.
 *
 * @category    Maketok
 * @package     Maketok_ProductReviews
 * @copyright   Copyright (c) 2013 Maketok (http://www.maketok.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('productreviews/review')} (
  `review_id` bigint(20) unsigned NOT NULL auto_increment,
  `parent_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `product_id` int(11) unsigned NOT NULL default '0',
  `status_id` tinyint(3) unsigned NOT NULL default '0',
  `thumbs_up` smallint(5) unsigned NOT NULL default '0',
  `thumbs_down` smallint(5) unsigned NOT NULL default '0',
  `detail` text NOT NULL,
  `nickname` varchar(128) NOT NULL default '',
  `customer_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`review_id`),
  KEY `FK_MAGEUP_REVIEW_STATUS` (`status_id`),
  KEY `FK_MAGEUP_REVIEW_PARENT_PRODUCT` (`product_id`),
  CONSTRAINT `FK_MAGEUP_REVIEW_PARENT_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maketok Review table';

CREATE TABLE {$this->getTable('productreviews/rating')} (
  `rating_id` bigint(20) unsigned NOT NULL auto_increment,
  `remote_ip` varchar(16) NOT NULL default '',
  `remote_ip_long` int(11) NOT NULL default '0',
  `customer_id` int(11) unsigned default '0',
  `product_id` bigint(20) unsigned NOT NULL default '0',
  `review_id` bigint(20) unsigned default NULL,
  `percent` tinyint(3) NOT NULL default '0',
  `value` tinyint (3) NOT NULL default '0',
  PRIMARY KEY  (`rating_id`),
  CONSTRAINT `FK_MAGEUP_RATING_REVIEW` FOREIGN KEY (`review_id`) REFERENCES {$this->getTable('productreviews/review')} (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maketok Rating table';

");

$installer->endSetup();