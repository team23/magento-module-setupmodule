<?php

namespace Team23\SetupModule\Model;

/**
 * Class ResourceReader
 *
 * @package Team23\SetupModule\Model
 */
class SetupResourceReader
{
    const RESOURCE_PATH = 'resources';
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $xmlParser;
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $themeFactory;
    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;
    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readDirFactory;

    /**
     * ResourceReader constructor.
     *
     * @param \Magento\Framework\Xml\Parser $xmlParser
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param Config $config
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readDirFactory
     */
    public function __construct(
        \Magento\Framework\Xml\Parser $xmlParser,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Team23\SetupModule\Model\Config $config,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readDirFactory
    ) {
        $this->xmlParser = $xmlParser;
        $this->themeFactory = $themeFactory;
        $this->design = $design;
        $this->componentRegistrar = $componentRegistrar;
        $this->config = $config;
        $this->readDirFactory = $readDirFactory;
    }

    /**
     * Extract the version from a string or "" if not found or valid
     *
     * The version number must be follow this schema: "{number > 0}.{number}.{number}"
     *
     * @param string $file
     * @return string
     */
    protected function extractVersion(string $file)
    {
        $file = pathinfo($file, PATHINFO_FILENAME);
        preg_match('/[1-9]+[0-9]*\.[0-9]+\.[0-9]+$/', $file, $version);

        return $version[0] ?? '';
    }

    /**
     * Get the absolute path of the SetupModule install path or '' if not found
     *
     * @return string|null
     */
    protected function getAbsoluteModulePath()
    {
        return $this->componentRegistrar->getPath(
                \Magento\Framework\Component\ComponentRegistrar::MODULE,
                $this->config->getFullyQualifiedModuleName()
            ) ?? '';
    }

    /**
     * Get the absolute path of the current frontend theme (i.e. app/design/Vendor/themename/) or '' if not found
     *
     * @return string
     */
    protected function getAbsoluteThemePath()
    {
        $themePath = $this->getTheme()->getFullPath();

        return $this->componentRegistrar->getPath(
                \Magento\Framework\Component\ComponentRegistrar::THEME,
                $themePath
            ) ?? '';
    }

    /**
     * Get the files located in module and theme module path.
     *
     * Override the module files relative paths in favor of the theme file relative paths.
     * This allows overriding of resource files in the current theme.
     *
     * @param string $resourceType
     * @return array ['path' => string, 'files' => array]
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function getFilteredResourceFiles(string $resourceType): array
    {
        $moduleFiles = $this->getResourceFiles($this->resolveResourcePath('module'), $resourceType);
        $moduleThemeFiles = $this->getResourceFiles($this->resolveResourcePath('theme'), $resourceType);

        // remove all overridden files from module (files that exists in theme)
        $moduleFiles['files'] = array_diff($moduleFiles['files'], $moduleThemeFiles['files']);

        return [$moduleFiles, $moduleThemeFiles];
    }

    /**
     * Get resource file data for all valid xml resources of a certain resource type.
     *
     * @param string $resourceType
     * @param string $setupVersion
     * @return array
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getResourceData(string $resourceType, string $setupVersion)
    {
        $locations = $this->getFilteredResourceFiles($resourceType);
        $result = [];

        foreach ($locations as $location) {
            // for each found data that matches the prerequisites of being a xml file ending on a valid version number
            foreach ($location['files'] as $file) {
                $version = $this->extractVersion($file);

                // if the version is valid and is higher than the current setupVersion parse the file & add the data
                if ($version && $this->shouldBeUpdated($version, $setupVersion)) {
                    $absPath = $location['path'] . '/' . $file;
                    $result[$version][] = [
                        'content' => $this->xmlParser->load($absPath)->xmlToArray(),
                        'path' => $absPath
                    ];
                }
            }
        }

        // sort array by key (version) using version_compare
        uksort($result, 'version_compare');

        return $result;
    }

    /**
     *  Check if a given version is lower than the setupVersion
     *
     * @param $version
     * @param $setupVersion
     * @return bool
     */
    protected function shouldBeUpdated(string $version, string $setupVersion): bool
    {
        return version_compare($setupVersion, $version, '<') ?? false;
    }

    /**
     * Resolve the absolute path of the SetupModule resource path depending on the location (module|theme)
     *
     * @param string $pathBase
     * @return string
     */
    protected function resolveResourcePath(string $pathBase = 'module'): string
    {
        if ($pathBase === 'module') {
            $path = $this->getAbsoluteModulePath();
            return $path . '/' . \Magento\Framework\Module\Dir::MODULE_VIEW_DIR . '/' . self::RESOURCE_PATH;
        }

        if ($pathBase === 'theme') {
            $path = $this->getAbsoluteThemePath();
            return $path . '/' . $this->config->getFullyQualifiedModuleName() . '/' . self::RESOURCE_PATH;
        }

        return '';
    }

    /**
     * Get resource files for all xml resources of a certain resource type that end on the pattern '_*.xml'
     *
     * The returned array follows the scheme: ['path' => string, 'files' => array]
     *
     * @param string $resourcePath
     * @param string $type
     * @return array ['path' => string, 'files' => array]
     * @throws \Magento\Framework\Exception\ValidatorException
     * @throws \Exception
     */
    protected function getResourceFiles(string $resourcePath, string $type): array
    {
        if (empty($type)) {
            throw new \Exception("Invalid resource type given: must be a valid folder in resources/");
        }

        /**
         * @var \Magento\Framework\Filesystem\Directory\Read $pathDir
         */
        $pathDir = $this->readDirFactory->create($resourcePath);
        $files = $pathDir->search('*_*.xml', $type);

        // return path and files separately in order to easily filter overrides afterwards
        return [
            'path' => $resourcePath,
            'files' => $files,
        ];
    }

    /**
     * Get the current active frontend theme object
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    protected function getTheme()
    {
        $themeCollection = $this->themeFactory->create();
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        $themeIdentifier = $this->design->getConfigurationDesignTheme($area);
        if (is_numeric($themeIdentifier)) {
            $theme = $themeCollection->getItemById($themeIdentifier);
        } else {
            $themeFullPath = $area . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . $themeIdentifier;
            $theme = $themeCollection->getThemeByFullPath($themeFullPath);
        }

        return $theme;
    }
}
