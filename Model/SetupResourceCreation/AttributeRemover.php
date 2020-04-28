<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

/**
 * Class AttributeRemover
 *
 * @package Team23\SetupModule\Setup
 */
class AttributeRemover
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * AttributeRemover constructor.
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Remove one or many product EAV attributes
     *
     * @param array|string $attributes
     */
    public function remove($attributes)
    {
        /**
         * @var \Magento\Eav\Setup\EavSetup $eavSetup
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
     * @param string $attribute
     * @return bool
     */
    private function isExist($eavSetup, $attribute)
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
     * @param string $attribute
     */
    private function removeAttribute($eavSetup, $attribute)
    {
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }
}
