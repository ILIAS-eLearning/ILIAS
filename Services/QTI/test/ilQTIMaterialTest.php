<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMaterialTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMaterial::class, new ilQTIMaterial());
    }

    public function testAddMattext() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMattext('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'mattext']], $instance->materials);
    }

    public function testAddMatimage() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatimage('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matimage']], $instance->materials);
    }

    public function testAddMatemtext() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatemtext('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matemtext']], $instance->materials);
    }

    public function testAddMataudio() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMataudio('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'mataudio']], $instance->materials);
    }

    public function testAddMatvideo() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatvideo('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matvideo']], $instance->materials);
    }

    public function testAddMatapplet() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatapplet('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matapplet']], $instance->materials);
    }

    public function testAddMatapplication() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatapplication('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matapplication']], $instance->materials);
    }

    public function testAddMatref() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatref('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matref']], $instance->materials);
    }

    public function testAddMatbreak() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMatbreak('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'matbreak']], $instance->materials);
    }

    public function testAdd_extension() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addMat_extension('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'mat_extension']], $instance->materials);
    }

    public function testAddAltmaterial() : void
    {
        $instance = new ilQTIMaterial();
        $instance->addAltmaterial('Some input.');
        $this->assertEquals([['material' => 'Some input.', 'type' => 'altmaterial']], $instance->materials);
    }

    public function testGetMaterialCount() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(0, $instance->getMaterialCount());

        $instance->addAltmaterial('Some input.');
        $instance->addMatbreak('Some input.');
        $this->assertEquals(2, $instance->getMaterialCount());
    }

    public function testGetMaterial() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(false, $instance->getMaterial(0));

        $instance->addAltmaterial('Some input.');
        $instance->addMatbreak('Some other input.');
        $this->assertEquals(['material' => 'Some other input.', 'type' => 'matbreak'], $instance->getMaterial(1));
    }

    public function testSetGetFlow() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(0, $instance->getFlow());

        $instance->setFlow(8);
        $this->assertEquals(8, $instance->getFlow());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(null, $instance->getLabel());

        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testExtractText() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals('', $instance->extractText());

        $instance->addMattext('Starting text.');
        $instance->addMatemtext('I will not be included.');
        $instance->addMattext('Second.');
        $instance->addMataudio('I will not be included.');
        $instance->addMattext('End of text.');

        $this->assertEquals('Starting text.Second.End of text.', $instance->extractText());
    }
}
