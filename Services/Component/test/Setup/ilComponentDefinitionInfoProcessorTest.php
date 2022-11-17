<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilComponentDefinitionInfoProcessorTest extends TestCase
{
    protected ilComponentInfoDefinitionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new ilComponentInfoDefinitionProcessor();
    }

    public function testPurge(): void
    {
        $type = "Modules";
        $name = "NAME";
        $id = "ID";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("module", ["id" => $id]);
        $this->processor->purge();

        $this->assertEquals([], $this->processor->getData());
    }

    public function testBeginTag(): void
    {
        $type1 = "Modules";
        $name1 = "NAME1";
        $id1 = "ID1";
        $name2 = "NAME2";
        $id2 = "ID2";

        $type2 = "Services";
        $name3 = "NAME3";
        $id3 = "ID3";
        $name4 = "NAME4";
        $id4 = "ID4";

        $id5 = "id5";
        $name5 = "name5";
        $id6 = "id6";
        $name6 = "name6";
        $id7 = "id7";
        $name7 = "name7";

        $this->processor->beginComponent($name1, $type1);
        $this->processor->beginTag("module", ["id" => $id1]);

        $this->processor->beginComponent($name2, $type1);
        $this->processor->beginTag("module", ["id" => $id2]);
        $this->processor->beginTag("pluginslot", ["id" => $id5, "name" => $name5]);

        $this->processor->beginComponent($name3, $type2);
        $this->processor->beginTag("service", ["id" => $id3]);
        $this->processor->beginTag("pluginslot", ["id" => $id6, "name" => $name6]);
        $this->processor->beginTag("pluginslot", ["id" => $id7, "name" => $name7]);

        $this->processor->beginComponent($name4, $type2);
        $this->processor->beginTag("service", ["id" => $id4]);

        $expected = [
            $id1 => [$type1, $name1, []],
            $id2 => [$type1, $name2, [[$id5, $name5]]],
            $id3 => [$type2, $name3, [[$id6, $name6], [$id7, $name7]]],
            $id4 => [$type2, $name4, []]
        ];

        $this->assertEquals($expected, $this->processor->getData());
    }

    public function testTagComponentTypeMismatch(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Services";
        $name = "NAME";
        $id = "ID";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("module", ["id" => $id]);
    }

    public function testMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Services";
        $name = "NAME";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("service", []);
    }

    public function testDuplicateComponentId(): void
    {
        $this->expectException(\LogicException::class);

        $this->processor->beginComponent("Module1", "Modules");
        $this->processor->beginTag("module", ["id" => "id"]);

        $this->processor->beginComponent("Module2", "Modules");
        $this->processor->beginTag("module", ["id" => "id"]);
    }

    public function testDuplicatePluginId(): void
    {
        $this->expectException(\LogicException::class);

        $this->processor->beginComponent("Module1", "Modules");
        $this->processor->beginTag("module", ["id" => "id1"]);
        $this->processor->beginTag("pluginslot", ["id" => "id", "name" => "name"]);

        $this->processor->beginComponent("Module2", "Modules");
        $this->processor->beginTag("module", ["id" => "id2"]);
        $this->processor->beginTag("pluginslot", ["id" => "id", "name" => "name"]);
    }
}
