<?php

namespace Team23\SetupModule\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Team23\SetupModule\Model\SetupResourceCreator;

/**
 * Class Recurring
 *
 * @package Team23\SetupModule\Setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var SetupResourceCreator
     */
    protected SetupResourceCreator $resourceCreator;

    /**
     * Recurring constructor.
     *
     * @param SetupResourceCreator $resourceCreator
     */
    public function __construct(
        SetupResourceCreator $resourceCreator
    ) {
        $this->resourceCreator = $resourceCreator;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->resourceCreator->run();
    }
}
