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
 *********************************************************************/

declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Counter\Counter;
use ILIAS\UI\Implementation\Component\Counter\Factory;

/**
 * Defines tests that a counter implementation should pass.
 */
class CounterTest extends ILIAS_UI_TestBase
{
    public function getCounterFactory(): Factory
    {
        return new Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getCounterFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Factory", $f);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Counter", $f->status(0));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Counter", $f->novelty(0));
    }

    /**
     * @dataProvider getNumberProvider
     */
    public function testStatusCounter(int $number): void
    {
        $f = $this->getCounterFactory();

        $c = $f->status($number);

        $this->assertNotNull($c);
        $this->assertEquals(C\Counter\Counter::STATUS, $c->getType());
        $this->assertEquals($number, $c->getNumber());
    }

    /**
     * @dataProvider getNumberProvider
     */
    public function testNoveltyCounter(int $number): void
    {
        $f = $this->getCounterFactory();

        $c = $f->novelty($number);

        $this->assertNotNull($c);
        $this->assertEquals(C\Counter\Counter::NOVELTY, $c->getType());
        $this->assertEquals($number, $c->getNumber());
    }

    public function testKnownCountersOnly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Counter("FOO", 1);
    }

    /**
     * @dataProvider getNoNumberProvider
     */
    public function testIntNumbersOnly($no_number): void
    {
        $this->expectException(TypeError::class);
        $f = $this->getCounterFactory();
        $f->status($no_number);
    }

    public function getNumberProvider(): array
    {
        return [
            [-13],
            [0],
            [23],
            [4]
        ];
    }

    public function getNoNumberProvider(): array
    {
        return [
            ["foo"],
            [9.1],
            [array()],
            [new stdClass()]
        ];
    }

    public static array $canonical_css_classes = [
        "status" => "badge badge-notify il-counter-status",
        "novelty" => "badge badge-notify il-counter-novelty"
    ];

    /**
     * @dataProvider getCounterTypeAndNumberProvider
     */
    public function testRenderStatus(string $type, int $number): void
    {
        $f = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $f->$type($number);

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[$type];
        $expected = "<span class=\"il-counter\"><span class=\"$css_classes\">$number</span></span>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function getCounterTypeAndNumberProvider(): array
    {
        return [
            ["status", 42],
            ["novelty", 13],
            ["status", 1],
            ["novelty", 23],
        ];
    }
}
