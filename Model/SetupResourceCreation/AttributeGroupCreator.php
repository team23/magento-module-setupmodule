<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

/**
 * Class AttributeGroupCreator
 *
 * @package Team23\SetupModule\Model\SetupResourceCreation
 */
class AttributeGroupCreator implements CreatorInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * AttributeGroupCreator constructor.
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(array $data): void
    {
        if (!isset($data['name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__("The xml tag 'name' may not be empty"));
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(array $data): void
    {
        $this->validate($data);

        /**
         * @var \Magento\Eav\Setup\EavSetup $eavSetup
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
