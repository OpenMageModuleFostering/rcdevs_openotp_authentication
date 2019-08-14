<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Add openotp field to table 'admin/user'
 */
$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'openotp',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 30,
        'default'   => null,
        'nullable'  => true,
        'comment'   => 'OpenOTP enabled'
    )
);

$installer->endSetup();
