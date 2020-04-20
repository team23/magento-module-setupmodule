<?php

namespace Team23\SetupModule\Setup;

/**
 * Class RunUpgrade
 * @package Team23\SetupModule\Setup
 */
class RunUpgrade
{

    /** @var SaveBlock $saveBlock */
    private $saveBlock;

    /** @var SavePage $savePage */
    private $savePage;

    /** @var AttributeCreator */
    protected $attributeCreator;

    /**
     * RunUpgrade constructor.
     * @param SaveBlock $saveBlock
     * @param SavePage $savePage
     * @param AttributeCreator $attributeCreator
     */
    public function __construct(SaveBlock $saveBlock, SavePage $savePage, AttributeCreator $attributeCreator, AttributeRemover $attributeRemover)
    {
        $this->saveBlock = $saveBlock;
        $this->savePage = $savePage;
        $this->attributeCreator = $attributeCreator;
        $this->attributeRemover = $attributeRemover;
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
     * Runs Upgrade by version
     * Takes all xml files with current version and saves to new block
     * @param $version
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function runUpgrade($version)
    {

        // BLOCKS
        $blocks = glob(dirname(__DIR__) . '/resources/cms_blocks/*_' . $version . '.xml');
        foreach ($blocks as $block) {
            $blockContent = file_get_contents($block);
            $xml = new \SimpleXMLElement($blockContent);

            $this->saveBlock->saveBlock(
                (string)$xml->identifier,
                (string)$xml->content,
                (string)$xml->title,
                [(string)$xml->store],
                (int)$xml->isActive
            );
        }

        // PAGES
        $pages = glob(dirname(__DIR__) . '/resources/pages/*_' . $version . '.xml');
        foreach ($pages as $page) {
            $pageContent = file_get_contents($page);
            $xml = new \SimpleXMLElement($pageContent);

            $this->savePage->savePage(
                (string)$xml->identifier,
                (string)$xml->content,
                (string)$xml->title,
                (string)$xml->contentHeading,
                [(string)$xml->store],
                (int)$xml->isActive,
                (string)$xml->pageLayout
            );
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

    /**
     * @param null $records (string or array)
     * Remove Attributes
     */
    public function removeAttributes($records = null)
    {
        if ($records) {
            $this->attributeRemover->remove($records);
        }
    }
}
