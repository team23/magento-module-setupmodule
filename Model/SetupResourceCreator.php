<?php

namespace Team23\SetupModule\Model;

use Exception;
use Magento\Config\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Team23\SetupModule\Model\SetupResourceCreation\AttributeGroupCreator;
use Team23\SetupModule\Model\SetupResourceCreation\AttributeCreator;
use Team23\SetupModule\Model\SetupResourceCreation\BlockCreator;
use Team23\SetupModule\Model\SetupResourceCreation\CreatorInterface;
use Team23\SetupModule\Model\SetupResourceCreation\PageCreator;
use Zend_Validate_Exception;

/**
 * Class AttributeCreator
 *
 * @package Team23\SetupModule\Setup
 */
class SetupResourceCreator
{
    const SETUP_VERSION_PATH = 'team23/setup_module/version';

    /**
     * @var Config
     */
    protected Config $config;
    
    /**
     * @var SetupResourceReader
     */
    protected SetupResourceReader $resourceReader;
    
    /**
     * @var AttributeGroupCreator
     */
    protected AttributeGroupCreator $attributeGroupCreator;
    
    /**
     * @var AttributeCreator
     */
    protected AttributeCreator $attributeCreator;
    
    /**
     * @var BlockCreator
     */
    protected BlockCreator $blockCreator;
    
    /**
     * @var PageCreator
     */
    protected PageCreator $pageCreator;

    /**
     * SetupResourceCreator constructor.
     *
     * @param Config $config
     * @param SetupResourceReader $resourceReader
     * @param AttributeGroupCreator $attributeGroupCreator
     * @param AttributeCreator $attributeCreator
     * @param BlockCreator $blockCreator
     * @param PageCreator $pageCreator
     */
    public function __construct(
        Config                  $config,
        SetupResourceReader     $resourceReader,
        AttributeGroupCreator   $attributeGroupCreator,
        AttributeCreator        $attributeCreator,
        BlockCreator            $blockCreator,
        PageCreator             $pageCreator
    ) {
        $this->config = $config;
        $this->resourceReader = $resourceReader;
        $this->attributeGroupCreator = $attributeGroupCreator;
        $this->attributeCreator = $attributeCreator;
        $this->blockCreator = $blockCreator;
        $this->pageCreator = $pageCreator;
    }

    /**
     * Run the resource creation process
     *
     * @return void
     * @throws ValidatorException
     * @throws Exception
     * @throws Zend_Validate_Exception
     * @throws LocalizedException
     */
    public function run(): void
    {
        $currentVersion = $this->getSetupVersion();
        $maxVersion = $currentVersion;

        $resourcesData = $this->getData($currentVersion);
        if ($resourcesData) {
            echo "\nRunning \e[32mTeam23_SetupModule\e[0m (setup version: {$currentVersion}):";
        }

        foreach ($resourcesData as $type => $data) {
            $maxResourceVersion = $this->getMaxVersion($data);
            if (version_compare($maxVersion, $maxResourceVersion, '<')) {
                $maxVersion = $maxResourceVersion;
            }

            // execute the Creator class for this type
            $this->createResource($type, $data);
        }

        $this->setSetupVersion($maxVersion);
    }

    /**
     * Get all possible resource data
     *
     * @param string $setupVersion
     * @return array
     * @throws ValidatorException
     */
    protected function getData(string $setupVersion): array
    {
        $resourceTypes = ['attribute', 'attribute_group', 'block', 'page'];
        // todo: add all typs like:
        // $resourceTypes = ['attribute', 'attribute_group', 'block', 'page', 'customer_group', 'menu'];

        $data = [];
        foreach ($resourceTypes as $resourceType) {
            // only add non-empty data arrays
            if ($resourceData = $this->resourceReader->getResourceData($resourceType, $setupVersion)) {
                $data[$resourceType] = $resourceData;
            }
        }

        return $data;
    }

    /**
     * Get the highest version key that is in the array or 0.0.0 if array is empty
     *
     * WARNING: This does only work here because the $data array is already sorted!
     *
     * @param array $data
     * @return string
     */
    protected function getMaxVersion(array $data): string
    {
        // ONLY WITH PHP 7.3+
        return array_key_last($data) ?? '0.0.0';
    }

    /**
     * @param $type
     * @param $data
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    protected function createResource($type, $data): void
    {
        $type = $this->snakeToCamelCase($type);
        /**
         * @var CreatorInterface $creator
         */
        if ($creator = $this->{$type . 'Creator'}) {
            $creatorClass = get_class($creator);
            echo "\n\nRunning resource creator '{$creatorClass}':";
            foreach ($data as $version => $files) {
                echo "\nProcessing version '{$version}'...";
                foreach ($files as $file) {
                    try {
                        echo "\n  creating {$type} from {$file['path']}...";
                        $creator->save($file['content']['xml']);
                        echo "\e[32m done!\e[0m";
                    } catch (LocalizedException | Zend_Validate_Exception $e) {
                        // just add more information and throw again
                        $name = get_class($e);
                        echo "\n\e[31mException '{$name}' caused by {$file['path']}: \e[0m\n";
                        echo "\e[31m{$e->getMessage()}\e[0m\n"; // we dont need too much information on console...
                        // exit(1);
                        // todo: discuss wether to continue or exit when
                    }
                }
            }
        }
    }

    /**
     * Get the current setup version saved in core_config or '0.0.0' if not set
     *
     * @return string
     */
    public function getSetupVersion(): string
    {
        return $this->config->getConfigDataValue(self::SETUP_VERSION_PATH) ?? '0.0.0';
    }

    /**
     * Set the new setup version
     *
     * @param string $version
     * @return void
     * @throws Exception
     */
    public function setSetupVersion(string $version): void
    {
        $this->config->setDataByPath(self::SETUP_VERSION_PATH, $version);
        $this->config->save();
    }

    /**
     * Convert a given snake_case string to camelCase
     *
     * @param string $str
     * @return string
     */
    protected function snakeToCamelCase(string $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($str)))));
    }
}
