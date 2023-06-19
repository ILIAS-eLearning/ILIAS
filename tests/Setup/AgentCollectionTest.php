<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class AgentCollectionTest extends TestCase
{
    use Helper;

    public function testHasConfig() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();
        $c4 = $this->newAgent();

        $c1->method("hasConfig")->willReturn(true);
        $c2->method("hasConfig")->willReturn(true);
        $c3->method("hasConfig")->willReturn(false);
        $c4->method("hasConfig")->willReturn(false);

        $col1 = new Setup\AgentCollection($refinery, ["c1" => $c1]);
        $col2 = new Setup\AgentCollection($refinery, ["c1" => $c1, "c2" => $c2]);
        $col3 = new Setup\AgentCollection($refinery, ["c1" => $c1, "c3" => $c3]);
        $col4 = new Setup\AgentCollection($refinery, ["c3" => $c3]);
        $col5 = new Setup\AgentCollection($refinery, ["c3" => $c3, "c4" => $c4]);

        $this->assertTrue($col1->hasConfig());
        $this->assertTrue($col2->hasConfig());
        $this->assertTrue($col3->hasConfig());
        $this->assertFalse($col4->hasConfig());
        $this->assertFalse($col5->hasConfig());
    }

    public function testGetArrayToConfigTransformation() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();

        $conf1 = $this->newConfig();
        $conf3 = $this->newConfig();

        foreach ([$c1,$c3] as $c) {
            $c
                ->method("hasConfig")
                ->willReturn(true);
        }
        $c2
            ->method("hasConfig")
            ->willReturn(false);

        $arr = ["c1" => ["c1_data"], "c3" => ["c3_data"]];

        $c1
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($conf1) {
                $this->assertEquals($v, ["c1_data"]);
                return $conf1;
            }));
        $c2
            ->expects($this->never())
            ->method("getArrayToConfigTransformation");
        $c3
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($conf3) {
                $this->assertEquals($v, ["c3_data"]);
                return $conf3;
            }));

        $col = new Setup\AgentCollection($refinery, ["c1" => $c1,"c2" => $c2,"c3" => $c3]);
        $trafo = $col->getArrayToConfigTransformation();
        $conf = $trafo($arr);

        $this->assertInstanceOf(Setup\ConfigCollection::class, $conf);
        $this->assertEquals(["c1", "c3"], $conf->getKeys());
        $this->assertEquals($conf1, $conf->getConfig("c1"));
        $this->assertEquals($conf3, $conf->getConfig("c3"));
    }

    public function testArrayToConfigTransformationAllowsUnsetFields() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();

        $conf1 = $this->newConfig();
        $conf3 = $this->newConfig();

        foreach ([$c1,$c3] as $c) {
            $c
                ->method("hasConfig")
                ->willReturn(true);
        }
        $c2
            ->method("hasConfig")
            ->willReturn(false);

        $arr = ["c1" => ["c1_data"]];

        $c1
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($conf1) {
                $this->assertEquals($v, ["c1_data"]);
                return $conf1;
            }));
        $c2
            ->expects($this->never())
            ->method("getArrayToConfigTransformation");
        $c3
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($conf3) {
                $this->assertEquals($v, null);
                return $conf3;
            }));

        $col = new Setup\AgentCollection($refinery, ["c1" => $c1,"c2" => $c2,"c3" => $c3]);
        $trafo = $col->getArrayToConfigTransformation();
        $conf = $trafo($arr);

        $this->assertInstanceOf(Setup\ConfigCollection::class, $conf);
        $this->assertEquals(["c1", "c3"], $conf->getKeys());
        $this->assertEquals($conf1, $conf->getConfig("c1"));
        $this->assertEquals($conf3, $conf->getConfig("c3"));
    }

    public function testGetInstallObjective() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();

        $g1 = $this->newObjective();
        $g2 = $this->newObjective();

        $conf1 = $this->newConfig();

        $c1
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(true);
        $c2
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(false);

        $c1
            ->expects($this->once())
            ->method("getInstallObjective")
            ->with($conf1)
            ->willReturn($g1);
        $c2
            ->expects($this->once())
            ->method("getInstallObjective")
            ->with()
            ->willReturn($g2);

        $col = new Setup\AgentCollection($refinery, ["c1" => $c1,"c2" => $c2]);
        $conf = new Setup\ConfigCollection(["c1" => $conf1]);

        $g = $col->getInstallObjective($conf);

        $this->assertInstanceOf(Setup\ObjectiveCollection::class, $g);
        $this->assertEquals([$g1, $g2], $g->getObjectives());
    }

    public function testGetUpdateObjective() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $storage = $this->createMock(Setup\Metrics\Storage::class);

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();

        $g1 = $this->newObjective();
        $g2 = $this->newObjective();

        $s1 = new Setup\Metrics\StorageOnPathWrapper("c1", $storage);
        $s2 = new Setup\Metrics\StorageOnPathWrapper("c2", $storage);

        $c1
            ->expects($this->once())
            ->method("getStatusObjective")
            ->with($s1)
            ->willReturn($g1);
        $c2
            ->expects($this->once())
            ->method("getStatusObjective")
            ->with($s2)
            ->willReturn($g2);

        $col = new Setup\AgentCollection($refinery, ["c1" => $c1,"c2" => $c2]);
        $g = $col->getStatusObjective($storage);

        $this->assertInstanceOf(Setup\ObjectiveCollection::class, $g);
        $this->assertEquals([$g1, $g2], $g->getObjectives());
    }

    public function testGetCollectMetricsObjective() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();

        $g1 = $this->newObjective();
        $g2 = $this->newObjective();

        $conf1 = $this->newConfig();

        $c1
            ->expects($this->once())
            ->method("getUpdateObjective")
            ->with($conf1)
            ->willReturn($g1);
        $c2
            ->expects($this->once())
            ->method("getUpdateObjective")
            ->with()
            ->willReturn($g2);

        $col = new Setup\AgentCollection($refinery, ["c1" => $c1,"c2" => $c2]);
        $conf = new Setup\ConfigCollection(["c1" => $conf1]);

        $g = $col->getUpdateObjective($conf);

        $this->assertInstanceOf(Setup\ObjectiveCollection::class, $g);
        $this->assertEquals([$g1, $g2], $g->getObjectives());
    }

    public function testGetAgent()
    {
        $refinery = $this->createMock(Refinery::class);

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();
        $c4 = $this->newAgent();

        $c = new Setup\AgentCollection(
            $refinery,
            ["c1" => $c1, "c2" => $c2, "c3" => $c3, "c4" => $c4]
        );

        $this->assertSame($c1, $c->getAgent("c1"));
        $this->assertSame($c2, $c->getAgent("c2"));
        $this->assertSame($c3, $c->getAgent("c3"));
        $this->assertSame($c4, $c->getAgent("c4"));
        $this->assertNull($c->getAgent("c5"));
    }

    public function testWithRemovedAgent()
    {
        $refinery = $this->createMock(Refinery::class);

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();
        $c4 = $this->newAgent();

        $ca = new Setup\AgentCollection(
            $refinery,
            ["c1" => $c1, "c2" => $c2, "c3" => $c3, "c4" => $c4]
        );
        $cb = $ca->withRemovedAgent("c2");

        $this->assertNotSame($ca, $cb);

        $this->assertSame($c1, $ca->getAgent("c1"));
        $this->assertSame($c2, $ca->getAgent("c2"));
        $this->assertSame($c3, $ca->getAgent("c3"));
        $this->assertSame($c4, $ca->getAgent("c4"));
        $this->assertNull($ca->getAgent("c5"));

        $this->assertSame($c1, $cb->getAgent("c1"));
        $this->assertNull($cb->getAgent("c2"));
        $this->assertSame($c3, $cb->getAgent("c3"));
        $this->assertSame($c4, $cb->getAgent("c4"));
        $this->assertNull($cb->getAgent("c5"));
    }

    public function testWithAdditionalAgent()
    {
        $refinery = $this->createMock(Refinery::class);

        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $c3 = $this->newAgent();
        $c4 = $this->newAgent();

        $ca = new Setup\AgentCollection(
            $refinery,
            ["c1" => $c1, "c2" => $c2, "c3" => $c3]
        );
        $cb = $ca->withAdditionalAgent("c4", $c4);

        $this->assertNotSame($ca, $cb);

        $this->assertSame($c1, $ca->getAgent("c1"));
        $this->assertSame($c2, $ca->getAgent("c2"));
        $this->assertSame($c3, $ca->getAgent("c3"));
        $this->assertNull($ca->getAgent("c4"));
        $this->assertNull($ca->getAgent("c5"));

        $this->assertSame($c1, $cb->getAgent("c1"));
        $this->assertSame($c2, $cb->getAgent("c2"));
        $this->assertSame($c3, $cb->getAgent("c3"));
        $this->assertSame($c4, $cb->getAgent("c4"));
        $this->assertNull($cb->getAgent("c5"));
    }

    public function testGetNamedObjective() : void
    {
        $refinery = $this->createMock(Refinery::class);

        $o = $this->newObjective();
        $c1 = $this->newAgent();
        $c2 = $this->newAgent();
        $conf2 = $this->newConfig();

        $conf = new Setup\ConfigCollection(
            ["sub" => new Setup\ConfigCollection(
                [ "c2" => $conf2 ]
            )]
        );

        $c = new Setup\AgentCollection(
            $refinery,
            [ "sub" => new Setup\AgentCollection(
                $refinery,
                ["c1" => $c1, "c2" => $c2]
            )]
        );

        $c2->expects($this->once())
            ->method("getNamedObjective")
            ->with("the_objective", $conf2)
            ->willReturn($o);

        $res = $c->getNamedObjective("sub.c2.the_objective", $conf);
        $this->assertSame($o, $res);
    }
}
