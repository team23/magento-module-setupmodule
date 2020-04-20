<?php

namespace Team23\SetupModule\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Team23\SetupModule\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /** @var RunUpgrade */
    protected $runUpgrade;

    /**
     * InstallSchema constructor.
     * @param RunUpgrade $runUpgrade
     */
    public function __construct(
        RunUpgrade $runUpgrade
    ) {
        $this->runUpgrade = $runUpgrade;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->runUpgrade->run('1.0.0');
        $setup->endSetup();
    }
}
