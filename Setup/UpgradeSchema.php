<?php
 
namespace Tagalys\Mpages\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
            //be careful, since everything below is true for installation !
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //code to upgrade to 1.0.1
            $mpagescacheTableName = $installer->getTable('tagalys_mpagescache');
            if ($installer->getConnection()->isTableExists($mpagescacheTableName) != true) {
                $mpagescacheTable = $installer->getConnection()
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
                $installer->getConnection()->createTable($mpagescacheTable);
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            //code to upgrade to 1.0.2
            $mpagescacheTable = $installer->getConnection()->addColumn(
                $installer->getTable('tagalys_mpagescache'),
                'platform',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Magento Category Page?',
                    'default' => 0
                ]
            );
        }
 
        $installer->endSetup();
    }
}