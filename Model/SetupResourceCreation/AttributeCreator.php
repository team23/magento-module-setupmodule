<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Zend_Validate_Exception;

/**
 * Class AttributeCreator
 *
 * @package Team23\SetupModule\Setup
 */
class AttributeCreator implements CreatorInterface
{
    /**
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * AttributeCreator constructor.
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
        if (!isset($data['code'])) {
            throw new LocalizedException(__("The xml tag 'code' may not be empty"));
        }

        if (isset($data['source']) && !class_exists($data['source'])) {
            throw new LocalizedException(
                __("The xml tag 'source' refers to a class that does not exist")
            );
        }

        if (isset($data['backend']) && !class_exists($data['backend'])) {
            throw new LocalizedException(
                __("The xml tag 'backend' refers to a class that does not exist")
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function save(array $data): void
    {
        $this->validate($data);

        /**
         * @var EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $data['code'],
            [
                'input' => $data['input'] ?? null,
                'label' => $data['label'] ?? null,
                'type' => $data['type'] ?? null,
                'source' => $data['source'] ?? null,
                'visible' => (int)($data['visible'] ?? 0),
                'required' => (int)($data['required'] ?? 0),
                'user_defined' => (int)($data['user_defined'] ?? 0),
                'default' => $data['default'] ?? null,
                'searchable' => (int)($data['searchable'] ?? 0),
                'filterable' => (int)($data['filterable'] ?? 0),
                'comparable' => (int)($data['comparable'] ?? 0),
                'visible_on_front' => (int)($data['visible_on_front'] ?? 0),
                'is_used_in_grid' => (int)($data['is_used_in_grid'] ?? 0),
                'is_filterable_in_grid' => (int)($data['is_filterable_in_grid'] ?? 0),
                'used_in_product_listing' => (int)($data['used_in_product_listing'] ?? 0),
                'backend' => $data['backend'] ?? null,
            ]
        );

        if (array_key_exists('option', $data)) {
            $attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $data['code']);
            $eavSetup->addAttributeOption(['attribute_id' => $attributeId, 'values' => $data['option']]);
        }

        if (array_key_exists('group', $data)) {
            $attributeSetId = $eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'default');
            $attributeGroupId = $eavSetup->getAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $data['group']);
            $attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $data['code']);
            $eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $attributeGroupId, $attributeId, null);
        }
    }
}
