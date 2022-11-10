<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class AttributeRemover
 *
 * @package Team23\SetupModule\Setup
 */
class AttributeRemover
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * AttributeRemover constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Remove one or many product EAV attributes
     *
     * @param array|string $attributes
     * @return void
     */
    public function remove(array|string $attributes): void
    {
        /**
         * @var EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create();

        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                if ($this->isExist($eavSetup, $attribute)) {
                    $this->removeAttribute($eavSetup, $attribute);
                }
            }
        } elseif (is_string($attributes)) {
            $this->removeAttribute($eavSetup, $attributes);
        }
    }

    /**
     * Check if a product EAV attribute exists
     *
     * @paran \Magento\Eav\Setup\EavSetup $eavSetup
     * @param $eavSetup
     * @param string $attribute
     * @return bool
     */
    private function isExist($eavSetup, string $attribute): bool
    {
        if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attribute) > 1) {
            return true;
        }

        return false;
    }

    /**
     * Remove the product EAV attribute
     *
     * @paran \Magento\Eav\Setup\EavSetup $eavSetup
     * @param $eavSetup
     * @param string $attribute
     * @return void
     */
    private function removeAttribute($eavSetup, string $attribute): void
    {
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }
}
