# TEAM23 SetupModule
## About
* With SetupModule extension you can easily add cms blocks to your magento2 project.
* All cms blocks are located in the `resources/cms_blocks` folder. In these `.xml` files you can setup your new or existing cms block.
* Ability to add other cms contents other than blocks will be added later.

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

## Upgrades
To upgrade your blocks to a new version follow these steps:
* Set the module version number according to your needs in `etc/module.xml`, e.g. `setup_version="1.0.1"`
* Add your new version upgrade to `Setup/UpgradeData.php`:

```php
// 1.0.1
if (version_compare($context->getVersion(), '1.0.1') < 0) {
    $this->runUpgrade->runUpgrade('1.0.1');
}
```

* Upgrade/Add your new blocks with the current version number at the end of the filename `certified-block_1.0.1.xml`
* Existing blocks will be overwritten, new blocks will automatically be added
* run `php bin/magento setup:upgrade` in your magento2 root folder to upgrade to new version
