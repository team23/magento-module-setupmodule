<?php

namespace Team23\SetupModule\Setup;

use Magento\Eav\Setup\EavSetupFactory;

class AttributeGroupCreator
{
    /** @var EavSetupFactory */
    protected $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param $xmlArray
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($xmlArray)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $groupName = $xmlArray['name'];
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'default');
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName);
    }
}
