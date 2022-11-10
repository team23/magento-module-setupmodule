<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AttributeGroupCreator
 *
 * @package Team23\SetupModule\Model\SetupResourceCreation
 */
class AttributeGroupCreator implements CreatorInterface
{
    /**
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * AttributeGroupCreator constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        if (!isset($data['name'])) {
            throw new LocalizedException(__("The xml tag 'name' may not be empty"));
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function save(array $data): void
    {
        $this->validate($data);

        /**
         * @var EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create();
        $attributeSetId = $eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'default');
        $eavSetup->addAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $data['name'],
            $data['sort_order'] ?? null
        );
    }
}
