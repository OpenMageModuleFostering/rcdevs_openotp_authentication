<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Add openotp field to table 'admin/user'
 */
$installer->getConnection()->addColumn($this->getTable('admin/user'), 'openotp', 'varchar(30) null');

$installer->endSetup();