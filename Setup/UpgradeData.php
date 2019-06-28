<?php

namespace Team23\SetupModule\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeData
 * @package Team23\SetupModule\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var SaveBlock */
    private $saveBlock;

    private $runUpgrade;

    /** @var RunUpgradeAttribute */
    protected $runUpgradeAttribute;

    /**
     * UpgradeData constructor.
     * @param SaveBlock $saveBlock
     */
    public function __construct(SaveBlock $saveBlock, RunUpgrade $runUpgrade)
    {
        $this->saveBlock = $saveBlock;
        $this->runUpgrade = $runUpgrade;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // todo: change this
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->runUpgrade->runUpgrade('1.0.1');
        }

        $setup->endSetup();
    }
}
