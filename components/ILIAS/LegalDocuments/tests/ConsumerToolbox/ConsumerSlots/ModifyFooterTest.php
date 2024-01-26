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
use ILIAS\LegalDocuments\Value\DocumentContent;
use ilTemplate;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\ModifyFooter;
use PHPUnit\Framework\TestCase;
use Closure;

require_once __DIR__ . '/../../ContainerMock.php';

class ModifyFooterTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ModifyFooter::class, new ModifyFooter(
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            $this->fail(...),
            $this->fail(...)
        ));
    }

    public function testInvoke(): void
    {
        $footer = $this->mock(Footer::class);
        $footer->expects(self::once())->method('withAdditionalModalAndTrigger')->willReturn($footer);

        $instance = new ModifyFooter(
            $this->mock(UI::class),
            $this->mockTree(User::class, ['acceptedVersion' => new Ok($this->mock(DocumentContent::class))]),
            $this->mock(Provide::class),
            fn() => 'rendered',
            fn() => $this->mock(ilTemplate::class)
        );

        $this->assertSame($footer, $instance($footer));
    }

    public function testRenderModal(): void
    {
        $footer = $this->mock(Footer::class);
        $footer->expects(self::once())->method('withAdditionalModalAndTrigger')->willReturn($footer);

        $instance = new ModifyFooter(
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            fn() => 'rendered',
            fn() => $this->mock(ilTemplate::class)
        );

        $proc = $instance->renderModal($footer);
        $this->assertSame($footer, $proc($this->mock(DocumentContent::class)));
    }

    public function testWithdrawalButton(): void
    {
        $template = $this->mock(ilTemplate::class);
        $template->expects(self::exactly(3))->method('setVariable');
        $template->expects(self::once())->method('get');

        $instance = new ModifyFooter(
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            fn() => 'rendered',
            fn() => $template
        );

        $this->assertInstanceOf(Component::class, $instance->withdrawalButton());
    }
}
