<?php

namespace Knawat\Dropshipping\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Class InstallData
 * @package Knawat\Dropshipping\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSet;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {

        $this->eavSetupFactory = $eavSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /*create Knawat Attribute set*/
        $attributeSetIds = 0;
        $attributeModel = $this->attributeSetFactory->create()->load('Knawat', 'attribute_set_name');
        $attributeSetIds = $attributeModel->getAttributeSetId();
        if ($attributeSetIds == 0) {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet = $this->attributeSetFactory->create();
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
            $data = [
            'attribute_set_name' => 'Knawat', // define custom attribute set name here
            'entity_type_id' => $entityTypeId,
            'sort_order' => 200,
            ];
            $attributeSet->setData($data);
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($attributeSetId);
            $attributeSet->save();
        }
    /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // /**
        //  * Add attributes to the eav/attribute
        //  * create is_knawat Attribute for product
        //  */
        $attributeId = $this->eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'is_knawat');
        if (!$attributeId) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'is_knawat', /* Custom Attribute Code */
                [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Knawat?',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
                ]
            );
        }
        // /**
        //  * Add attributes to the Sales Ortder
        //  * create is_knawat Attribute for order
        //  */
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $installer]);
        $salesSetup->addAttribute(Order::ENTITY, 'is_knawat', [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        'default' => '0',
        'visible' => false,
        'nullable' => true
        ]);
        /**
         * create knawat_order_id Attribute for order
         */
        $salesSetup->addAttribute(Order::ENTITY, 'knawat_order_id', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'length' => 255,
            'nullable' => true
        ]);
        /**
         * create knawat_sync_failed Attribute for order
         */
        $salesSetup->addAttribute(Order::ENTITY, 'knawat_sync_failed', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'default' => '0',
            'visible' => false,
            'nullable' => true
        ]);
        /**
         * create knawat_order_status Attribute for order
         */
        $salesSetup->addAttribute(Order::ENTITY, 'knawat_order_status', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 255,
            'visible' => false,
            'nullable' => true
        ]);
        /**
         * create knawat_order_status grid column for order
         */
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'knawat_order_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' =>'Knawat Status'
            ]
        );
        /**
         * create shipment_provider_name Attribute for order
         */
        $salesSetup->addAttribute(Order::ENTITY, 'shipment_provider_name', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 255,
            'visible' => false,
            'nullable' => true
        ]);
        /**
         * create shipment_tracking_number Attribute for order
         */
        $salesSetup->addAttribute(Order::ENTITY, 'shipment_tracking_number', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 255,
            'visible' => false,
            'nullable' => true
        ]);

        $installer->endSetup();
    }
}
