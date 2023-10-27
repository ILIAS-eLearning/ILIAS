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

namespace ILIAS\LegalDocuments\test\Legacy;

use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Legacy\Confirmation;
use ilLanguage;
use ilConfirmationGUI;

require_once __DIR__ . '/../ContainerMock.php';

class ConfirmationTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Confirmation::class, new Confirmation($this->mock(ilLanguage::class), $this->fail(...)));
    }

    public function testRender(): void
    {
        $confirmation = $this->confirmationGUIReplacement();
        $language = $this->mock(ilLanguage::class);
        $language->expects(self::exactly(2))->method('txt')->willReturnCallback(static fn(string $key): string => 'translated ' . $key);

        $instance = new Confirmation($language, static fn() => $confirmation);
        $this->assertSame('rendered string', $instance->render('dummy link', 'submit', 'cancel', 'Hi', [
            'foo' => 'bar',
            7 => 'baz',
        ]));

        $this->assertSame('dummy link', $confirmation->link);
        $this->assertSame('submit', $confirmation->submit_command);
        $this->assertSame('cancel', $confirmation->cancel_command);
        $this->assertSame('translated confirm', $confirmation->submit_label);
        $this->assertSame('translated cancel', $confirmation->cancel_label);
        $this->assertSame('Hi', $confirmation->header_text);
        $this->assertSame([['ids[]', 'foo', 'bar'], ['ids[]', '7', 'baz']], $confirmation->items);
    }

    private function confirmationGUIReplacement(): object
    {
        return new class () {
            public string $link;
            public string $confirm_command;
            public string $cancel_command;
            public string $header_text;
            public array $items = [];
            public string $cancel_label;
            public string $submit_label;

            public function setFormAction(string $link): void
            {
                $this->link = $link;
            }
            public function setConfirm(string $label, string $command): void
            {
                $this->submit_label = $label;
                $this->submit_command = $command;
            }
            public function setCancel(string $label, string $command): void
            {
                $this->cancel_label = $label;
                $this->cancel_command = $command;
            }
            public function setHeaderText(string $text): void
            {
                $this->header_text = $text;
            }
            public function addItem(string $a, string $b, string $c): void
            {
                $this->items[] = [$a, $b, $c];
            }
            public function getHTML(): string
            {
                return 'rendered string';
            }
        };
    }
}
