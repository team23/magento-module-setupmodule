<?php

namespace Team23\SetupModule\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 *
 * @package Team23\SetupModule\Setup
 */
class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @var \Team23\SetupModule\Model\Config
     */
    protected $config;
    /**
     * @var \Team23\SetupModule\Model\SetupResourceCreator
     */
    protected $resourceCreator;

    /**
     * Recurring constructor.
     *
     * @param \Team23\SetupModule\Model\Config $config
     * @param \Team23\SetupModule\Model\SetupResourceCreator $resourceCreator
     */
    public function __construct(
        \Team23\SetupModule\Model\Config $config,
        \Team23\SetupModule\Model\SetupResourceCreator $resourceCreator
    ) {
        $this->config = $config;
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
