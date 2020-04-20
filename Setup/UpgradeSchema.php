<?php

namespace Team23\SetupModule\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Team23\SetupModule\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /** @var RunUpgrade */
    protected $runUpgrade;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeSchema constructor.
     * @param RunUpgrade $runUpgrade
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        RunUpgrade $runUpgrade,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->runUpgrade = $runUpgrade;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->runUpgrade->run('1.0.1');
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->runUpgrade->run('1.0.2');
        }
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->runUpgrade->run('1.0.3');
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->runUpgrade->run('1.0.4');
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $this->runUpgrade->run('1.0.5');
        }
        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $this->runUpgrade->run('1.0.6');
        }
        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $this->runUpgrade->run('1.0.7');
        }
        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $this->runUpgrade->run('1.0.8');
        }
        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $this->runUpgrade->run('1.0.9');
        }
        if (version_compare($context->getVersion(), '1.0.10') < 0) {
            $this->runUpgrade->run('1.0.10');
        }

        $setup->endSetup();
    }
}
