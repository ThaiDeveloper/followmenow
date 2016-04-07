<?php
/** @var $installer Evince_Ourteam_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('ourteam/ourteam');

if ($installer->getConnection()->isTableExists($tableName)) {

    /* Add Meta Title */
    $installer->getConnection()
        ->addColumn($tableName, 'meta_title', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'length' => 255,
            'after' => null,
            'comment' => 'Meta Title'
        ));

    /* Add Meta Description */
    $installer->getConnection()
        ->addColumn($tableName, 'meta_description', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'length' => 1024,
            'after' => null,
            'comment' => 'Meta Description'
        ));

}
$installer->endSetup();
