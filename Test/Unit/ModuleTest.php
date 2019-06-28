<?php

namespace Team23\SetupModule\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\TestFramework\Unit\BaseTestCase;

class ModuleTest extends BaseTestCase
{
    const MODULE_NAME = 'Team23_SetupModule';

    public function testTheModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);

        $this->assertArrayHasKey(self::MODULE_NAME, $paths, 'Module should be registered');
    }
}
