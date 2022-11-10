<?php

namespace Team23\SetupModule\Model;

/**
 * Class Config
 *
 * @package Team23\SetupModule\Model
 */
class Config
{
    const MODULE_NAME = 'SetupModule';
    const MODULE_VENDOR = 'Team23';

    /**
     * Get the fully qualified module name (i.e. Vendor_Module)
     *
     * @return string
     */
    public function getFullyQualifiedModuleName(): string
    {
        return self::MODULE_VENDOR . '_' . self::MODULE_NAME;
    }
}

