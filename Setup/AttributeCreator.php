<?php

namespace Team23\SetupModule\Setup;

use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class AttributeCreator
 * @package Team23\SetupModule\Setup
 */
class AttributeCreator
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * AttributeCreator constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param $xmlArray
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($xmlArray)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $xmlArray['code'],
            [
                'input' => array_key_exists('input', $xmlArray) ? $xmlArray['input'] : null,
                'label' => array_key_exists('label', $xmlArray) ? $xmlArray['label'] : null,
                'type' => array_key_exists('type', $xmlArray) ? $xmlArray['type'] : null,
                'source' => array_key_exists('source', $xmlArray) ? $xmlArray['source'] : null,
                'visible' => array_key_exists('visible', $xmlArray) ? intval($xmlArray['visible']) : 0,
                'required' => array_key_exists('required', $xmlArray) ? intval($xmlArray['required']) : 0,
                'user_defined' => array_key_exists('user_defined', $xmlArray) ? intval($xmlArray['user_defined']) : 0,
                'default' => array_key_exists('default', $xmlArray) ? $xmlArray['default'] : null,
                'searchable' => array_key_exists('searchable', $xmlArray) ? intval($xmlArray['searchable']) : 0,
                'filterable' => array_key_exists('filterable', $xmlArray) ? intval($xmlArray['filterable']) : 0,
                'comparable' => array_key_exists('comparable', $xmlArray) ? intval($xmlArray['comparable']) : 0,
                'visible_on_front' => array_key_exists('visible_on_front', $xmlArray) ? intval($xmlArray['visible_on_front']) : 0,
                'is_used_in_grid' => array_key_exists('is_used_in_grid', $xmlArray) ? intval($xmlArray['is_used_in_grid']) : 0,
                'is_filterable_in_grid' => array_key_exists('is_filterable_in_grid', $xmlArray) ? intval($xmlArray['is_filterable_in_grid']) : 0,
                'used_in_product_listing' => array_key_exists('used_in_product_listing', $xmlArray) ? intval($xmlArray['used_in_product_listing']) : 0,
                'backend' => array_key_exists('backend', $xmlArray) ? $xmlArray['backend'] : null,
            ]
        );

        if (array_key_exists('option', $xmlArray)) {
            $entityTypeId = 'catalog_product';
            $attributeId = $eavSetup->getAttributeId($entityTypeId, $xmlArray['code']);
            $eavSetup->addAttributeOption(['attribute_id' => $attributeId, 'values' => $xmlArray['option']]);
        }

        if (array_key_exists('group', $xmlArray)) {
            $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
            $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'default');
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $xmlArray['group']);
            $attributeId = $eavSetup->getAttributeId($entityTypeId, $xmlArray['code']);
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
        }
    }
}
