<?php declare(strict_types=1);

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
 *********************************************************************/
 
use PHPUnit\Framework\TestCase;
use ILIAS\KioskMode\ControlBuilder;

class LSTOCBuilderTest extends TestCase
{
    public function testConstruction() : void
    {
        $cb = $this->createMock(LSControlBuilder::class);
        $tb = new LSTOCBuilder($cb, '');
        $this->assertEquals(
            json_encode(["label" => "","command" => "","parameter" => null,"state" => null,"childs" => []]),
            $tb->toJSON()
        );
    }

    public function testRecursion() : void
    {
        $cb = $this->createMock(LSControlBuilder::class);
        $tb = new LSTOCBuilder($cb, '');
        $tb
            ->node('node1')
                ->item('item1.1', 1)
                ->item('item1.2', 2)
            ->end()
            ->item('item2', 3)
            ->node('node3')
                ->item('item3.1', 4)
                ->node('node3.2', 5)
                    ->item('item3.2.1', 6)
                ->end()
            ->end()
        ->end();

        $expected = [
            "label" => "","command" => "","parameter" => null,"state" => null,"childs" => [
                ["label" => "node1","command" => "","parameter" => null,"state" => null,"childs" => [
                    ["label" => "item1.1","command" => "","parameter" => 1,"state" => null,"current" => false],
                    ["label" => "item1.2","command" => "","parameter" => 2,"state" => null,"current" => false]
                ]],
            ["label" => "item2","command" => "","parameter" => 3,"state" => null, "current" => false],
            ["label" => "node3","command" => "","parameter" => null,"state" => null,"childs" => [
                ["label" => "item3.1","command" => "","parameter" => 4,"state" => null,"current" => false],
                ["label" => "node3.2","command" => "","parameter" => 5,"state" => null,"childs" => [
                    ["label" => "item3.2.1","command" => "","parameter" => 6,"state" => null,"current" => false]
                ]
            ]]]]];

        $this->assertEquals(
            json_encode($expected),
            $tb->toJSON()
        );
    }

    public function testToCEnd() : void
    {
        $cb = $this->createMock(LSControlBuilder::class);
        $tb = new LSTOCBuilder($cb, '');
        $tb = $tb->end();
        $this->assertInstanceOf(ControlBuilder::class, $tb);
    }
}
