<?php

namespace Team23\SetupModule\Setup;

use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class AttributeRemover
 * @package Team23\SetupModule\Setup
 */
class AttributeRemover
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * AttributeRemover constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param $records
     * remove attributes
     */
    public function remove($attributes)
    {
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
     * @param $attribute
     * @return bool
     */
    private function isExist($eavSetup, $attribute)
    {
        if ($eavSetup->getAttributeId('catalog_product', $attribute) > 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $attribute
     */
    private function removeAttribute($eavSetup, $attribute)
    {
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }

}
