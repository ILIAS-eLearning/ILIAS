<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

require_once(__DIR__."/Helper.php");

use ILIAS\Setup;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input as Input;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class AgentCollectionTest extends \PHPUnit\Framework\TestCase {
	use Helper;

	public function testHasConfig() {
		$ff = $this->createMock(FieldFactory::class);
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$c1 = $this->newAgent();
		$c2 = $this->newAgent();
		$c3 = $this->newAgent();
		$c4 = $this->newAgent();

		$c1->method("hasConfig")->willReturn(true);
		$c2->method("hasConfig")->willReturn(true);
		$c3->method("hasConfig")->willReturn(false);
		$c4->method("hasConfig")->willReturn(false);

		$col1 = new Setup\AgentCollection($ff, $refinery, ["c1" => $c1]);
		$col2 = new Setup\AgentCollection($ff, $refinery, ["c1" => $c1, "c2" => $c2]);
		$col3 = new Setup\AgentCollection($ff, $refinery, ["c1" => $c1, "c3" => $c3]);
		$col4 = new Setup\AgentCollection($ff, $refinery, ["c3" => $c3]);
		$col5 = new Setup\AgentCollection($ff, $refinery, ["c3" => $c3, "c4" => $c4]);

		$this->assertTrue($col1->hasConfig());
		$this->assertTrue($col2->hasConfig());
		$this->assertTrue($col3->hasConfig());
		$this->assertFalse($col4->hasConfig());
		$this->assertFalse($col5->hasConfig());
	}

	public function testGetConfigInput() {
		$ff = $this->createMock(FieldFactory::class);
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$c1 = $this->newAgent();
		$c2 = $this->newAgent();
		$c3 = $this->newAgent();

		$inp1 = $this->newInput();
		$inp3 = $this->newInput();
		$group = $this->newInput();

		foreach([$c1,$c3] as $c) {
			$c
				->expects($this->once())
				->method("hasConfig")
				->willReturn(true);
		}
		$c2
			->expects($this->once())
			->method("hasConfig")
			->willReturn(false);
		$c1
			->expects($this->once())
			->method("getConfigInput")
			->willReturn($inp1);
		$c2
			->expects($this->never())
			->method("getConfigInput");
		$c3
			->expects($this->once())
			->method("getConfigInput")
			->willReturn($inp3);

		$col = new Setup\AgentCollection($ff, $refinery, ["c1"=>$c1,"c2"=>$c2,"c3"=>$c3]);

		$ff
			->expects($this->once())
			->method("group")
			->with(["c1" => $inp1, "c3" => $inp3])
			->willReturn($group);

		$group
			->expects($this->once())
			->method("withAdditionalTransformation")
			->with($this->callback(function(Transformation $t) {
				$conf1 = $this->newConfig();
				$conf3 = $this->newConfig();
				$res = $t->transform(["c1" => $conf1, "c3" => $conf3]);
				$this->assertInstanceOf(Setup\ConfigCollection::class, $res);
				$this->assertEquals(["c1", "c3"], $res->getKeys());
				$this->assertEquals($conf1, $res->getConfig("c1"));
				$this->assertEquals($conf3, $res->getConfig("c3"));
				return true;
			}))
			->willReturn($group);

		$res = $col->getConfigInput();

		$this->assertEquals($group, $res);
	}

	public function testGetConfigInputUsesSuppliedConfig() {
		$ff = $this->createMock(FieldFactory::class);
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$c1 = $this->newAgent();
		$c2 = $this->newAgent();
		$c3 = $this->newAgent();

		$inp = $this->newInput();

		$conf1 = $this->newConfig();
		$conf3 = $this->newConfig();

		foreach([$c1,$c3] as $c) {
			$c
				->method("hasConfig")
				->willReturn(true);
		}
		$c2
			->method("hasConfig")
			->willReturn(false);
		$c1
			->expects($this->once())
			->method("getConfigInput")
			->with($conf1)
			->willReturn($inp);
		$c2
			->expects($this->never())
			->method("getConfigInput");
		$c3
			->expects($this->once())
			->method("getConfigInput")
			->with($conf3)
			->willReturn($inp);

		$col = new Setup\AgentCollection($ff, $refinery, ["c1"=>$c1,"c2"=>$c2,"c3"=>$c3]);

		$ff
			->method("group")
			->willReturn($inp);

		$inp
			->method("withAdditionalTransformation")
			->willReturn($inp);

		$conf = new Setup\ConfigCollection(["c1" => $conf1, "c3" => $conf3]);

		$col->getConfigInput($conf);
	}

	public function testGetArrayToConfigTransformation() {
		$ff = $this->createMock(FieldFactory::class);
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$c1 = $this->newAgent();
		$c2 = $this->newAgent();
		$c3 = $this->newAgent();

		$conf1 = $this->newConfig();
		$conf3 = $this->newConfig();

		foreach([$c1,$c3] as $c) {
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
			->willReturn($refinery->custom()->transformation(function($v) use ($conf1) {
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
			->willReturn($refinery->custom()->transformation(function($v) use ($conf3) {
				$this->assertEquals($v, ["c3_data"]);
				return $conf3;
			 }));

		$col = new Setup\AgentCollection($ff, $refinery, ["c1"=>$c1,"c2"=>$c2,"c3"=>$c3]);
		$trafo = $col->getArrayToConfigTransformation();
		$conf = $trafo($arr);

		$this->assertInstanceOf(Setup\ConfigCollection::class, $conf);
		$this->assertEquals(["c1", "c3"], $conf->getKeys());
		$this->assertEquals($conf1, $conf->getConfig("c1"));
		$this->assertEquals($conf3, $conf->getConfig("c3"));
	}

	public function testGetInstallObjective() {
		$ff = $this->createMock(FieldFactory::class);
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

		$col = new Setup\AgentCollection($ff, $refinery, ["c1"=>$c1,"c2"=>$c2]);
		$conf = new Setup\ConfigCollection(["c1" => $conf1]);

		$g = $col->getInstallObjective($conf);

		$this->assertInstanceOf(Setup\ObjectiveCollection::class, $g);
		$this->assertEquals([$g1, $g2], $g->getObjectives());
	}

	public function testGetUpdateObjective() {
		$ff = $this->createMock(FieldFactory::class);
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
			->method("getUpdateObjective")
			->with($conf1)
			->willReturn($g1);
		$c2
			->expects($this->once())
			->method("getUpdateObjective")
			->with()
			->willReturn($g2);

		$col = new Setup\AgentCollection($ff, $refinery, ["c1"=>$c1,"c2"=>$c2]);
		$conf = new Setup\ConfigCollection(["c1" => $conf1]);

		$g = $col->getUpdateObjective($conf);

		$this->assertInstanceOf(Setup\ObjectiveCollection::class, $g);
		$this->assertEquals([$g1, $g2], $g->getObjectives());
	}
}
