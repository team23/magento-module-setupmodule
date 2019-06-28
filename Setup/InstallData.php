<?php

namespace Team23\SetupModule\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Snowdog\Menu\Model\Menu\NodeFactory;
use Snowdog\Menu\Model\MenuFactory;

/**
 * Class InstallData
 * @package Team23\SetupModule\Setup
 */
class InstallData implements InstallDataInterface
{
    /** @var SaveBlock */
    protected $saveBlock;

    /** @var RunUpgrade */
    protected $runUpgrade;

    /** @var  MenuFactory */
    protected $menuFactory;

    /** @var NodeFactory */
    protected $nodeFactory;

    /**
     * InstallData constructor.
     * @param SaveBlock $saveBlock
     * @param RunUpgrade $runUpgrade
     * @param MenuFactory $menuFactory
     * @param NodeFactory $nodeFactory
     */
    public function __construct(SaveBlock $saveBlock, RunUpgrade $runUpgrade, MenuFactory $menuFactory, NodeFactory $nodeFactory)
    {
        $this->saveBlock = $saveBlock;
        $this->runUpgrade = $runUpgrade;
        $this->menuFactory = $menuFactory;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->runUpgrade->runUpgrade('1.0.0');

        /**
         * Create footer_menu Content
         */
        $footerMenuContent = [
            'title' => 'Footer Menu Content',
            'identifier' => 'footer_menu_content',
            'css_class' => 'footer_menu_content',
        ];
        $contentMenu = $this->menuFactory->create()->setData($footerMenuContent)->save();
        $contentMenu->saveStores([1]);

        /**
         * Create footer_menu Content nodes
         */

        $nodeData = [
            'type' => 'custom_url',
            'title' => 'Inhalt',
            'classes' => 'footer_menu_headline',
            'menu_id' => $contentMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'bierstorten',
            'title' => 'Bierstorten',
            'menu_id' => $contentMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'verkostung',
            'title' => 'Verkostung',
            'menu_id' => $contentMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'brauereien',
            'title' => 'Brauereien',
            'menu_id' => $contentMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'philosophie',
            'title' => 'Philosophie',
            'menu_id' => $contentMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        /**
         * Create footer_menu Service
         */
        $footerMenuService = [
            'title' => 'Footer Menu Service',
            'identifier' => 'footer_menu_service',
            'css_class' => 'footer_menu_service',
        ];
        $serviceMenu = $this->menuFactory->create()->setData($footerMenuService)->save();
        $serviceMenu->saveStores([1]);

        /**
         * Create footer_menu Content nodes
         */

        $nodeData = [
            'type' => 'custom_url',
            'title' => 'Kundenservice',
            'classes' => 'footer_menu_headline',
            'menu_id' => $serviceMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'agb',
            'title' => 'AGB',
            'menu_id' => $serviceMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'impressum',
            'title' => 'Impressum',
            'menu_id' => $serviceMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'datenschutz',
            'title' => 'Datenschutz',
            'menu_id' => $serviceMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $nodeData = [
            'type' => 'cms_page',
            'content' => 'sitemap',
            'title' => 'Sitemap',
            'menu_id' => $serviceMenu->getId()
        ];
        $this->nodeFactory->create()->setData($nodeData)->save();

        $setup->endSetup();
    }
}
