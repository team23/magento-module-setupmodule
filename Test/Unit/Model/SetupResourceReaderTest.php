<?php

namespace Team23\SetupModule\Test\Unit\Model;

/**
 * Class ResourceReader
 *
 * @package Team23\SetupModule\Model
 */
class SetupResourceReaderTest extends \PHPUnit\Framework\TestCase
{
    protected $xmlParserMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeFactoryMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $designMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $componentRegistrarMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $readDirFactoryMock;
    /**
     * @var \Team23\SetupModule\Model\SetupResourceReader
     */
    protected $model;

    protected function setUp()
    {
        $this->xmlParserMock = $this->createMock(\Magento\Framework\Xml\Parser::class);
        $this->themeFactoryMock = $this->createMock(\Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class);
        $this->designMock = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $this->componentRegistrarMock = $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class);
        $this->configMock = $this->createMock(\Team23\SetupModule\Model\Config::class);
        $this->readDirFactoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);

        $this->model = new \Team23\SetupModule\Model\SetupResourceReader(
            $this->xmlParserMock,
            $this->themeFactoryMock,
            $this->designMock,
            $this->componentRegistrarMock,
            $this->configMock,
            $this->readDirFactoryMock
        );
    }

    /**
     * Run a private/protected method in $this->model
     *
     * @param $methodName the method to run
     * @param array $args the arguments to use
     * @return mixed
     * @throws \ReflectionException
     */
    protected function runMethod($methodName, array $args)
    {
        $class = new \ReflectionClass(get_class($this->model));
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->model, $args);
    }

    public function testFileVersionExtractionCorrect()
    {
        $testValidFileName = 'foo_barBaz-123_1.0.1.xml';
        $this->assertEquals(
            '1.0.1',
            $this->runMethod('extractVersion', [$testValidFileName])
        );
    }

    public function testFileVersionExtractionLowerZero()
    {
        $testValidFileName = 'foo_barBaz-123_0.0.1.xml';
        $this->assertEquals(
            '',
            $this->runMethod('extractVersion', [$testValidFileName])
        );
    }

    public function testFileVersionExtractionInvalid()
    {
        $testValidFileName = 'foo_barBaz-123_0.0.1.9.jxml';
        $this->assertEquals(
            '',
            $this->runMethod('extractVersion', [$testValidFileName])
        );
    }

    public function testResourceFileShouldNotBeUpdated()
    {
        $fileVersion = '0.0.0';
        $setupVersion = '0.0.0';
        $this->assertFalse(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '1.0.0';
        $setupVersion = '1.0.0';
        $this->assertFalse(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '1.0.1';
        $setupVersion = '1.1.0';
        $this->assertFalse(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '1.0.0';
        $setupVersion = '1.0.1';
        $this->assertFalse(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );
    }

    public function testResourceFileShouldBeUpdated()
    {
        $fileVersion = '0.0.1';
        $setupVersion = '0.0.0';
        $this->assertTrue(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '1.0.1';
        $setupVersion = '1.0.0';
        $this->assertTrue(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '2.0.1';
        $setupVersion = '1.2.0';
        $this->assertTrue(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );

        $fileVersion = '1.0.12';
        $setupVersion = '1.0.11';
        $this->assertTrue(
            $this->runMethod('shouldBeUpdated', [$fileVersion, $setupVersion])
        );
    }

}
