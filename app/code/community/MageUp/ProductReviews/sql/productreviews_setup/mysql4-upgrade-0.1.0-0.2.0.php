<?php
/**
 * MageUp ProductReviews module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageup.info@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade MageUp ProductReviews to newer
 * versions in the future. If you wish to customize MageUp ProductReviews for your
 * needs please refer to http://www.mageup.com for more information.
 *
 * @category    MageUp
 * @package     MageUp_ProductReviews
 * @copyright   Copyright (c) 2013 MageUp (http://www.mageup.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$voteTable = $this->getTable('productreviews/vote');
$installer->run("

CREATE TABLE IF NOT EXISTS {$voteTable} (
  `vote_id` bigint(20) unsigned NOT NULL auto_increment,
  `remote_ip` varchar(16) NOT NULL default '',
  `remote_ip_long` int(11) NOT NULL default '0',
  `customer_id` int(11) unsigned default '0',
  `review_id` bigint(20) unsigned default NULL,
  `value` tinyint (3) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  CONSTRAINT `FK_MAGEUP_REVIEW_VOTE_REVIEW` FOREIGN KEY (`review_id`) REFERENCES {$this->getTable('productreviews/review')} (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='MageUp Review Vote table';

ALTER TABLE {$this->getTable('productreviews/review')} DROP `thumbs_up`, DROP `thumbs_down`;

");

$installer->endSetup();