<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilCtrlContextTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlContextTest extends TestCase
{
    /**
     * @var ArrayBasedRequestWrapper
     */
    private ArrayBasedRequestWrapper $request;

    /**
     * @var ilCtrlPathFactory
     */
    private ilCtrlPathFactory $factory;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * @var array
     */
    private array $values;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->values = [];
        $this->refinery = $this->createMock(Refinery::class);
        $this->factory = $this->createMock(ilCtrlPathFactory::class);
        $this->request = $this->createMock(ArrayBasedRequestWrapper::class);
        $this->request->method('has')->willReturn(true);
        $this->request
            ->method('retrieve')
            ->willReturnCallback(function ($key) {
                return $this->values[$key] ?? null;
            });
    }

    /**
     * @param string|null $cid_path
     * @return ilCtrlPathInterface
     */
    protected function getPath(string $cid_path = null) : ilCtrlPathInterface
    {
        return new class($cid_path) extends ilCtrlAbstractPath {
            // override parent constructor, so we don't
            // have to mock the ilCtrlStructure.
            public function __construct(string $cid_path = null)
            {
                $this->cid_path = $cid_path;
            }
        };
    }

    /**
     * @param array|null $request_values
     * @return ilCtrlContextInterface
     */
    protected function getContextWithManualAdoption(array $request_values = null) : ilCtrlContextInterface
    {
        $this->values = $request_values ?? [];

        return new class($this->factory, $this->request, $this->refinery) extends ilCtrlContext {
            // override parent constructor, so the request values
            // are not adopted immediately.
            public function __construct(
                ilCtrlPathFactory $path_factory,
                ArrayBasedRequestWrapper $request_wrapper,
                Refinery $refinery
            ) {
                $this->path_factory = $path_factory;
                $this->path = $path_factory->null();
                $this->request_wrapper = $request_wrapper;
                $this->refinery = $refinery;
            }

            // provide a public method that manually adopts the
            // request values.
            public function adopt() : void
            {
                $this->adoptRequestParameters();
            }
        };
    }

    public function testContextPropertyAdoptionByRequest() : void
    {
        $expected_cid_path = 'test_cid_path';
        $this->factory
            ->method('existing')
            ->willReturn($this->getPath($expected_cid_path));

        $this->factory
            ->method('find')
            ->willReturn($this->getPath($expected_cid_path));

        $context = $this->getContextWithManualAdoption([
            ilCtrlInterface::PARAM_CMD_MODE => 'test_cmd_mode',
            ilCtrlInterface::PARAM_REDIRECT => 'test_redirect_source',
            ilCtrlInterface::PARAM_BASE_CLASS => 'test_base_class',
            ilCtrlInterface::PARAM_CMD_CLASS => 'test_cmd_class',
            ilCtrlInterface::PARAM_CMD => 'test_cmd',
            ilCtrlInterface::PARAM_CID_PATH => $expected_cid_path,
        ]);

        $this->assertFalse($context->isAsync());
        $this->assertEquals('ilias.php', $context->getTargetScript());
        $this->assertNull($context->getCmdMode());
        $this->assertNull($context->getRedirectSource());
        $this->assertNull($context->getBaseClass());
        $this->assertNull($context->getCmdClass());
        $this->assertNull($context->getCmd());
        $this->assertNull($context->getPath()->getCidPath());
        $this->assertNull($context->getObjType());
        $this->assertNull($context->getObjId());

        // manually adopt request values.
        $context->adopt();

        $this->assertFalse($context->isAsync());
        $this->assertNull($context->getObjType());
        $this->assertNull($context->getObjId());
        $this->assertEquals('ilias.php', $context->getTargetScript());

        $this->assertEquals('test_cmd_mode', $context->getCmdMode());
        $this->assertEquals('test_redirect_source', $context->getRedirectSource());
        $this->assertEquals('test_base_class', $context->getBaseClass());
        $this->assertEquals('test_cmd_class', $context->getCmdClass());
        $this->assertEquals('test_cmd', $context->getCmd());
        $this->assertEquals('test_cid_path', $context->getPath()->getCidPath());
    }

    public function testContextAsyncPropertyAdoptionByRequest() : void
    {
        $context = $this->getContextWithManualAdoption();
        $this->assertFalse($context->isAsync());

        $this->values[ilCtrlInterface::PARAM_CMD_MODE] = ilCtrlInterface::CMD_MODE_PROCESS;
        $context->adopt();
        $this->assertFalse($context->isAsync());

        $this->values[ilCtrlInterface::PARAM_CMD_MODE] = ilCtrlInterface::CMD_MODE_HTML;
        $context->adopt();
        $this->assertFalse($context->isAsync());

        $this->values[ilCtrlInterface::PARAM_CMD_MODE] = ilCtrlInterface::CMD_MODE_ASYNC;
        $context->adopt();
        $this->assertTrue($context->isAsync());
    }

    public function testContextBaseClassPropertyAdoptionByRequest() : void
    {
        $expected_cid_path = '0';
        $this->factory
            ->method('find')
            ->willReturn($this->getPath($expected_cid_path));

        $context = $this->getContextWithManualAdoption();
        $this->assertNull($context->getBaseClass());

        $this->values[ilCtrlInterface::PARAM_BASE_CLASS] = ilCtrlBaseClass1TestGUI::class;
        $context->adopt();

        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getBaseClass());
        $this->assertEquals($expected_cid_path, $context->getPath()->getCidPath());
    }

    public function testContextPropertyAdoptionWithoutCidPathAndBaseClass() : void
    {
        $context = $this->getContextWithManualAdoption();
        $this->assertNull($context->getBaseClass());
        $this->assertNull($context->getPath()->getCidPath());

        $context->adopt();

        $this->assertNull($context->getBaseClass());
        $this->assertNull($context->getPath()->getCidPath());
    }

    public function testContextPropertiesThatCantBeAdopted() : void
    {
        $context = new ilCtrlContext($this->factory, $this->request, $this->refinery);

        $this->assertNull($context->getObjType());
        $this->assertNull($context->getObjId());
        $this->assertEquals('ilias.php', $context->getTargetScript());

        $context
            ->setObjType('test_type')
            ->setObjId(42)
            ->setTargetScript('test_script');

        $this->assertEquals('test_type', $context->getObjType());
        $this->assertEquals(42, $context->getObjId());
        $this->assertEquals('test_script', $context->getTargetScript());
    }

    public function testContextCommandClassPropertyWithoutBaseClass() : void
    {
        $context = new ilCtrlContext($this->factory, $this->request, $this->refinery);
        $this->factory
            ->method('find')
            ->willReturn($this->getPath('http://localhost'));

        $this->assertNull($context->getCmdClass());
        $context->setCmdClass(ilCtrlCommandClass1TestGUI::class);
        $this->assertEquals(ilCtrlCommandClass1TestGUI::class, $context->getCmdClass());

        $context = $this->getContextWithManualAdoption([
            ilCtrlInterface::PARAM_CMD_CLASS => ilCtrlBaseClass1TestGUI::class,
        ]);

        $this->assertNull($context->getCmdClass());
        $context->adopt();
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getCmdClass());
    }

    public function testContextCommandClassPropertyWithBaseClass() : void
    {
        $context = new ilCtrlContext($this->factory, $this->request, $this->refinery);
        $this->factory
            ->method('find')
            ->willReturn($this->getPath('http://localhost'));

        $context->setBaseClass(ilCtrlBaseClass1TestGUI::class);
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getCmdClass());

        $context->setCmdClass(ilCtrlCommandClass1TestGUI::class);
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getBaseClass());
        $this->assertEquals(ilCtrlCommandClass1TestGUI::class, $context->getCmdClass());

        $context = $this->getContextWithManualAdoption([
            ilCtrlInterface::PARAM_CMD_CLASS => ilCtrlBaseClass1TestGUI::class,
        ]);

        $this->assertNull($context->getCmdClass());
        $context->adopt();
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getCmdClass());
    }

    public function testContextPathProgressionWhenSettingClasses() : void
    {
        // for this test an actual instance of the path factory
        // is required, therefore we have to creat the test ctrl
        // structure.

        $structure_artifact = require __DIR__ . '/Data/Structure/test_ctrl_structure.php';
        $base_class_artifact = require __DIR__ . '/Data/Structure/test_base_classes.php';

        $structure = new ilCtrlStructure(
            $structure_artifact,
            $base_class_artifact,
            []
        );

        $path_factory = new ilCtrlPathFactory($structure);
        $context = new ilCtrlContext($path_factory, $this->request, $this->refinery);
        $this->assertNull($context->getPath()->getCidPath());

        $context->setBaseClass(ilCtrlInvalidGuiClass::class);
        $this->assertNull($context->getPath()->getCidPath());
        $this->assertNull($context->getBaseClass());

        $context->setCmdClass(ilCtrlCommandClass1TestGUI::class);
        $this->assertNull($context->getPath()->getCidPath());
        $this->assertNull($context->getCmdClass());

        $context->setBaseClass(ilCtrlBaseClass1TestGUI::class);
        $this->assertEquals('0', $context->getPath()->getCidPath());
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getBaseClass());
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getCmdClass());

        $context->setCmdClass(ilCtrlCommandClass1TestGUI::class);
        $this->assertEquals('0:2', $context->getPath()->getCidPath());
        $this->assertEquals(ilCtrlBaseClass1TestGUI::class, $context->getBaseClass());
        $this->assertEquals(ilCtrlCommandClass1TestGUI::class, $context->getCmdClass());
    }
}
