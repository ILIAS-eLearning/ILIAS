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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\ShowOnLoginPage;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Factory as UIFactory;
use ilTemplate;

require_once __DIR__ . '/../../ContainerMock.php';

class ShowOnLoginPageTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ShowOnLoginPage::class, new ShowOnLoginPage($this->mock(Provide::class), $this->mock(UI::class), $this->fail(...)));
    }

    public function testInvokeWithoutDocuments(): void
    {
        $instance = new ShowOnLoginPage($this->mockTree(Provide::class, [
            'document' => ['repository' => ['countAll' => 0]],
        ]), $this->mock(UI::class), $this->fail(...));

        $this->assertSame([], $instance());
    }

    public function testInvoke(): void
    {
        $translated = 'Translated<br/>';
        $url = 'Dummy URL';
        $legacy = $this->mock(Legacy::class);

        $template = $this->mock(ilTemplate::class);
        $expected = [
            ['LABEL', htmlentities($translated)],
            ['HREF', $url]
        ];
        $template
            ->expects(self::exactly(2))
            ->method('setVariable')
            ->willReturnCallback(
                function (string $k, string $v) use (&$expected) {
                    [$ek, $ev] = array_shift($expected);
                    $this->assertEquals($ek, $k);
                    $this->assertEquals($ev, $v);
                }
            );

        $template->expects(self::once())->method('get')->willReturn('Rendered');

        $instance = new ShowOnLoginPage($this->mockTree(Provide::class, [
            'document' => ['repository' => ['countAll' => 1]],
            'publicPage' => ['url' => $url],
        ]), $this->mockTree(UI::class, [
            'txt' => $translated,
            'create' => $this->mockMethod(UIFactory::class, 'legacy', ['Rendered'], $legacy),
        ]), fn() => $template);

        $array = $instance();

        $this->assertSame(1, count($array));
        $this->assertSame($legacy, $array[0]);
    }
}
