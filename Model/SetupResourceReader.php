<?php

namespace Team23\SetupModule\Model;

use Exception;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Xml\Parser;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

/**
 * Class ResourceReader
 *
 * @package Team23\SetupModule\Model
 */
class SetupResourceReader
{
    const RESOURCE_PATH = 'resources';
    /**
     * @var Parser
     */
    protected Parser $xmlParser;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $themeFactory;
    /**
     * @var DesignInterface
     */
    protected DesignInterface $design;
    /**
     * @var ComponentRegistrar
     */
    protected ComponentRegistrar $componentRegistrar;
    /**
     * @var Config
     */
    protected Config $config;
    /**
     * @var ReadFactory
     */
    protected ReadFactory $readDirFactory;

    /**
     * ResourceReader constructor.
     *
     * @param Parser $xmlParser
     * @param CollectionFactory $themeFactory
     * @param DesignInterface $design
     * @param ComponentRegistrar $componentRegistrar
     * @param Config $config
     * @param ReadFactory $readDirFactory
     */
    public function __construct(
        Parser             $xmlParser,
        CollectionFactory  $themeFactory,
        DesignInterface    $design,
        ComponentRegistrar $componentRegistrar,
        Config             $config,
        ReadFactory        $readDirFactory
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
     * The version number must follow this schema: "{number > 0}.{number}.{number}"
     *
     * @param string $file
     * @return string
     */
    protected function extractVersion(string $file): string
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
    protected function getAbsoluteModulePath(): ?string
    {
        return $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            $this->config->getFullyQualifiedModuleName()
        ) ?? '';
    }

    /**
     * Get the absolute path of the current frontend theme (i.e. app/design/Vendor/themename/) or '' if not found
     *
     * @return string
     */
    protected function getAbsoluteThemePath(): string
    {
        $themePath = $this->getTheme()->getFullPath();

        return $this->componentRegistrar->getPath(
            ComponentRegistrar::THEME,
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
     * @throws ValidatorException
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
     * @throws ValidatorException
     */
    public function getResourceData(string $resourceType, string $setupVersion): array
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
     * @param string $version
     * @param string $setupVersion
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
        if ($pathBase === 'module' && ($path = $this->getAbsoluteModulePath())) {
            return $path . '/' . \Magento\Framework\Module\Dir::MODULE_VIEW_DIR . '/' . self::RESOURCE_PATH;
        }

        if ($pathBase === 'theme' && ($path = $this->getAbsoluteThemePath())) {
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
     * @throws ValidatorException
     * @throws Exception
     */
    protected function getResourceFiles(string $resourcePath, string $type): array
    {
        if (empty($type)) {
            throw new Exception("Invalid resource type given: must be a valid folder in resources/");
        }

        /**
         * @var Read $pathDir
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
     * @return ThemeInterface
     */
    protected function getTheme(): ThemeInterface
    {
        $themeCollection = $this->themeFactory->create();
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        $themeIdentifier = $this->design->getConfigurationDesignTheme($area);
        if (is_numeric($themeIdentifier)) {
            $theme = $themeCollection->getItemById($themeIdentifier);
        } else {
            $themeFullPath = $area . ThemeInterface::PATH_SEPARATOR . $themeIdentifier;
            $theme = $themeCollection->getThemeByFullPath($themeFullPath);
        }

        return $theme;
    }
}
