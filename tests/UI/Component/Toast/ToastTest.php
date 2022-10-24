<?php

declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component\Link\Link;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Toast\Toast;

class ToastTest extends ILIAS_UI_TestBase
{
    public function getToastFactory(): \ILIAS\UI\Implementation\Component\Toast\Factory
    {
        return new ILIAS\UI\Implementation\Component\Toast\Factory(new ILIAS\UI\Implementation\Component\SignalGenerator());
    }

    public function getIconFactory(): \ILIAS\UI\Implementation\Component\Symbol\Icon\Factory
    {
        return new ILIAS\UI\Implementation\Component\Symbol\Icon\Factory();
    }

    public function getLinkFactory(): \ILIAS\UI\Implementation\Component\Link\Factory
    {
        return new ILIAS\UI\Implementation\Component\Link\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getToastFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Toast\\Factory", $f);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Toast\\Toast", $f->standard('', $this->getIconFactory()->standard('', '')));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Toast\\Container", $f->container());
    }

    /**
     * @dataProvider toast_provider
     */
    public function test_toast(string $title, string $description, int $vanish_time, int $delay_time, string $action): void
    {
        $toast = $this->getToastFactory()->standard($title, $this->getIconFactory()->standard('', ''))
                      ->withDescription($description)
                      ->withVanishTime($vanish_time)
                      ->withDelayTime($delay_time)
                      ->withAction($action)
                      ->withAdditionalLink($this->getLinkFactory()->standard('', ''));

        $this->assertNotNull($toast);
        $this->assertEquals($title, $toast->getTitle());
        $this->assertEquals($description, $toast->getDescription());
        $this->assertEquals($vanish_time, $toast->getVanishTime());
        $this->assertEquals($delay_time, $toast->getDelayTime());
        $this->assertEquals($action, $toast->getAction());
        $this->assertCount(1, $toast->getLinks());
        $this->assertInstanceOf(Link::class, $toast->getLinks()[0]);
        $this->assertCount(0, $toast->withoutLinks()->getLinks());
        $this->assertInstanceOf(Icon::class, $toast->getIcon());
    }

    /**
     * @dataProvider toast_provider
     */
    public function test_toast_container(string $title, string $description, int $vanish_time): void
    {
        $container = $this->getToastFactory()->container()->withAdditionalToast(
            $this->getToastFactory()->standard('', $this->getIconFactory()->standard('', ''))
        );

        $this->assertNotNull($container);
        $this->assertCount(1, $container->getToasts());
        $this->assertInstanceOf(Toast::class, $container->getToasts()[0]);
        $this->assertCount(0, $container->withoutToasts()->getToasts());
    }

    public function toast_provider(): array
    {
        return [
            ['title', 'description', 5000, 500, 'test.php'],
            ['', '', -5000, -500, ''],
            ['"/><script>alert("hack")</script>', '"/><script>alert("hack")</script>', PHP_INT_MAX, PHP_INT_MIN, 'test.php']
        ];
    }
}
