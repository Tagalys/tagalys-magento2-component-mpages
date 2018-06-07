<?php
 
namespace Tagalys\Mpages\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
 
        $mpagescacheTableName = $installer->getTable('tagalys_mpagesccache');
        if ($installer->getConnection()->isTableExists($mpagescacheTableName) != true) {
            $configTable = $installer->getConnection()
                ->newTable($mpagescacheTableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Store ID'
                )
                ->addColumn(
                    'url',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default' => ''
                    ],
                    'Mpage URL component'
                )
                ->addColumn(
                    'cachedata',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'default' => ''
                    ],
                    'Cache data in JSON'
                )
                ->setComment('Tagalys Mpages Cache')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($configTable);
        }

        $installer->endSetup();
    }
}