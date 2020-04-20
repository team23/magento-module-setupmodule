<?php

namespace Team23\SetupModule\Setup;

/**
 * Class RunUpgrade
 * @package Team23\SetupModule\Setup
 */
class RunUpgrade
{
    /** @var BlockCreator */
    protected $blockCreator;

    /** @var PageCreator */
    protected $pageCreator;

    /** @var AttributeCreator */
    protected $attributeCreator;

    /** @var AttributeGroupCreator */
    protected $attributeGroupCreator;

    /**
     * RunUpgrade constructor.
     * @param BlockCreator $blockCreator
     * @param PageCreator $pageCreator
     * @param AttributeCreator $attributeCreator
     * @param AttributeGroupCreator $attributeGroupCreator
     */
    public function __construct(
        BlockCreator $blockCreator,
        PageCreator $pageCreator,
        AttributeCreator $attributeCreator,
        AttributeGroupCreator $attributeGroupCreator
    ) {
        $this->blockCreator = $blockCreator;
        $this->pageCreator = $pageCreator;
        $this->attributeCreator = $attributeCreator;
        $this->attributeGroupCreator = $attributeGroupCreator;
    }

    /**
     * https://outlandish.com/blog/tutorial/xml-to-json/
     *
     * @param $xml
     * @param array $options
     * @return array
     */
    public function xmlToArray($xml, $options = [])
    {
        $defaults = [
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => [],   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => '$',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       //optional search and replace on tag and attribute names
            'keyReplace' => false       //replace values for above search values (as passed to str_replace())
        ];
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) {
                    $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                }
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = $this->xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch']) {
                    $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                }
                //add namespace prefix, if any
                if ($prefix) {
                    $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
                }

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? [$childProperties] : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = [$tagsArray[$childTagName], $childProperties];
                }
            }
        }

        //get text content of node
        $textContentArray = [];
        $plainText = trim((string)$xml);
        if ($plainText !== '') {
            $textContentArray[$options['textContent']] = $plainText;
        }

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return [
            $xml->getName() => $propertiesArray
        ];
    }

    /**
     * Runs upgrade by version
     * Gets all xml files with current version
     *
     * @param $version
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function run($version)
    {
        /**
         * Creates or saves block
         */
        $blocks = glob(dirname(__DIR__) . '/resources/blocks/*_' . $version . '.xml');
        foreach ($blocks as $block) {
            $blockContent = file_get_contents($block);
            $xml = new \SimpleXMLElement($blockContent);

            $this->blockCreator->save(
                (string)$xml->identifier,
                (string)$xml->title,
                (string)$xml->content,
                [(string)$xml->store],
                (int)$xml->isActive
            );
        }

        /**
         * Creates or saves pages
         */
        $pages = glob(dirname(__DIR__) . '/resources/pages/*_' . $version . '.xml');
        foreach ($pages as $page) {
            $pageContent = file_get_contents($page);
            $xml = new \SimpleXMLElement($pageContent);

            $this->pageCreator->save(
                (string)$xml->identifier,
                (string)$xml->title,
                (string)$xml->content,
                (string)$xml->contentHeading,
                [(string)$xml->store],
                (int)$xml->isActive,
                (string)$xml->pageLayout
            );
        }

        /**
         * Creates or saves menus
         */
        $menus = glob(dirname(__DIR__) . '/resources/menus/*_' . $version . '.xml');

        foreach ($menus as $menu) {
            $menuContent = file_get_contents($menu);
            $xml = new \SimpleXMLElement($menuContent);

            $arr = $this->xmlToArray($xml, ['alwaysArray' => ['node']]);
            $arr = $arr['xml'];

            $this->menuCreator->save(
                $arr['title'],
                $arr['identifier'],
                $arr['cssClass'],
                [$arr['store']],
                $arr['node']
            );
        }

        /**
         * Creates product attribute groups
         */
        $attributeGroups = glob(dirname(__DIR__) . '/resources/attribute_group/*_' . $version . '.xml');
        foreach ($attributeGroups as $attributeGroup) {
            $attributeGroupContent = file_get_contents($attributeGroup);
            $xml = new \SimpleXMLElement($attributeGroupContent);
            $xmlArray = $this->xmlToArray($xml, ['alwaysArray' => ['option']])['xml'];
            $this->attributeGroupCreator->save($xmlArray);
        }

        /**
         * Creates product attributes
         */
        $attributes = glob(dirname(__DIR__) . '/resources/attributes/*_' . $version . '.xml');
        foreach ($attributes as $attribute) {
            $attributeContent = file_get_contents($attribute);
            $xml = new \SimpleXMLElement($attributeContent);
            $xmlArray = $this->xmlToArray($xml, ['alwaysArray' => ['option']])['xml'];
            $this->attributeCreator->save($xmlArray);
        }
    }
}
