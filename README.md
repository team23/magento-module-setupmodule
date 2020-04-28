# TEAM23 SetupModule
The SetupModule makes it easy to install new persistent data for your current Magento 2 project.

It allows adding of the following Magento 2 data:
- EAV attribute groups
- EAV attributes
- content pages
- content blocks
- ~~customer groups~~ (TBD / WIP!)
- ~~menus~~ (TBD / WIP!)

## How to
If you want to setup data, just add the resource folders as described in the following sections 
in your themes Team23_SetupModule module override and run `bin/magento setup:upgrade`.
All resources with a version >= the setup version stored in `core_config_data`'s path `team23/setup_module/version`
will become installed.

In rare cases when you want to repeat the installation process, just change the config with path 
`team23/setup_module/version` stored in `core_config_data` in the database. 

### Example directory structure
In app/design/frontend/VENDOR/THEME/Team23_SetupModule/
```
app
|-- ...
`-- design
    |-- ...
    `-- frontend
        |-- ...
        `-- VENDOR
            `-- THEME
                |-- ... 
                `-- Team23_SetupModule
                    `-- resources
                        |-- attribute
                        |-- attribute_group
                        |-- block
                        |-- customer_group      # (TBD / WIP!)
                        |-- menu                # (TBD / WIP!)
                        `-- page
```

## How it works internally
Whenever `bin/magento setup:upgrade` will be run the extension will 
- look in its data resource folders for defined and valid xml files (i.e. `filename_1.0.0.xml`)
- extract the files version number of the files & compare it with the current setup version in the database (in
 `core_config_data`: `team23/setup_module/version`)
- run creation for all resources with a higher version than the setup version stored in the database (initially 0.0.0)
- increase the setup version in the database to the highest version of all resources

## Installation via Composer

- Add satis.team23.de composer repository in your composer.json

```bash
composer config repositories.team23 composer https://satis.team23.de/
```

- Require team23/module-setupmodule

```bash
composer require team23/module-setupmodule ^dev-master
```

## Resources
### General File naming:
Resource files will be read from `resources/{TYPE}/`. <br>
To add a new resource file add a XML file with the of your choice. The file **MUST** end on (`_`) followed by a 
php compatible version number >= `1.0.0` and the file extension `.xml` e.g. `new-attribute_1.0.0.xml`.

The xml tags contents refer to the resources creation parameters, that Magento 2 uses in its functions. 
Therefore they **MUST** follow Magentos internal requirements (i.e. attribute codes must be written in snake_case). 

### Attribute resources
Attribute files will be read from `resources/attribute`. 

#### Mandatory tags
- `xml` - the surrounding root tag
- `code` - the attribute code 

#### Optional tags
The following optional attributes are currently supported. For more information check the 
[Product EAV Attribute Options Reference](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/attributes.html#add-product-eav-attribute-options-reference)

- `input`
- `label`
- `type`
- `source`
- `visible` - defaults to `0`
- `required` - defaults to `0`
- `user_defined` - defaults to `0`
- `default`
- `searchable` - defaults to `0`
- `filterable` - defaults to `0`
- `comparable` - defaults to `0`
- `visible_on_front` - defaults to `0`
- `is_used_in_grid` - defaults to `0`
- `is_filterable_in_grid` - defaults to `0`
- `used_in_product_listing` - defaults to `0`
- `backend`

**IMPORTANT NOTE**<br>
When using `source` or `backend` tags, make sure the provided class is defined, otherwise that will lead to errors once  
stored in the database. That's why SetupModule will throw an Exception if the class is not already defined.

#### Example
```xml
<xml>
    <code>my_custom_code</code>
    <input>text</input>
    <label>My label text</label>
    <visible>1</visible>
    <required>0</required>
    <user_defined>1</user_defined>
    <searchable>0</searchable>
    <filterable>0</filterable>
    <comparable>0</comparable>
    <visible_on_front>1</visible_on_front>
    <is_used_in_grid>0</is_used_in_grid>
    <used_in_product_listing>1</used_in_product_listing>
    <group>My example group</group>
</xml>
```

### Attribute Group resources
Attribute Group files will be read from `resources/attribute_group`. 

#### Mandatory tags
- `xml` - the surrounding root tag
- `name` - the groups name 

#### Optional tags
- `sort_order`

#### Example
```xml
<xml>
    <name>Demo Group</name>
    <sort_order>1</sort_order>
</xml>
```

### Block resources
Block files will be read from `resources/block`. 

#### Mandatory tags
- `xml` - the surrounding root tag
- `identifier` - the blocks unique identifier (must be snake_case)
- `title` - the blocks title

#### Optional tags
- `content` - defaults to `''`; Feel free to use CDATA for unescaped html
- `store_id` - defaults to `0`
- `is_active` - defaults to `0`

#### Example
```xml
<xml>
    <identifier>block_mydemo</identifier>
    <title>My demo block</title>
    <content><![CDATA[<h2>My demo title in HTML</h2>
<p>Foo bar baz!</p>]]></content>
    <store_id>0</store_id>
    <is_active>1</is_active>
</xml>
```

### Page resources
Page files will be read from `resources/page`. 

#### Mandatory tags
- `xml` - the surrounding root tag
- `identifier` - the pages unique identifier (must be snake_case)
- `title` - the pages title

#### Optional tags
- `content_heading`
- `content` - defaults to `''`; Feel free to use CDATA for unescaped html
- `store_id` - defaults to `0`
- `is_active` - defaults to `0`
- `page_layout` - defaults to `1column`

#### Example
```xml
<xml>
    <identifier>no-route</identifier>
    <title>404 - My custom 404 override</title>
    <content_heading>404 - This is my overrides content heading</content_heading>
    <content>
        <![CDATA[
    <div class="error-section">
        <p>Im sorry...</p>
        <div class="error-row">
            <a href="/">Back to home</a>
        </div>
    </div>
    ]]>
    </content>
    <store_id>0</store_id>
    <is_active>1</is_active>
    <page_layout>1column</page_layout>
</xml>
```