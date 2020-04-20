# TEAM23 SetupModule
The SetupModule makes it easy to install new persistent data for your current Magento 2 project.
It allows adding of the following Magento 2 data:
- EAV attributes
- cms blocks
- cms pages

## How to
If you want to add data, just override the extensions data folders in your custom theme.
Run `bin/magento setup:upgrade`

### Example

## How it works internally
Whenever you run `bin/magento setup:upgrade` the extension will 
- look in its data folders and check all xml file versions 
(i.e. `filename_1.0.0.xml` would result in `1.0.0`) 
- compare the highest found version to the value stored in the database
 (in `core_config_data`: `team23/setup_module/version`). 
- run installation for all filenames with and higher version than the version stored in the database
- increase the database version to the highest files version


## Blocks
Block configs are located in `resources/cms_blocks`. To add a new block add a `.xml` file with a custom name and the new version number after a underscore(`_`) at the end, e.g. `certified-block_1.0.0.xml`.

Your xml files must have the following structure:
```xml
<xml>
    <identifier>certified_block</identifier>
    <title>Certified Block</title>
    <content><![CDATA[
<p>Your block content goes here</p>
        ]]>
    </content>
    <store>0</store>
    <isActive>1</isActive>
</xml>
```
Note that the blocks are identified by the `identifier` value inside the file, not the filename.

