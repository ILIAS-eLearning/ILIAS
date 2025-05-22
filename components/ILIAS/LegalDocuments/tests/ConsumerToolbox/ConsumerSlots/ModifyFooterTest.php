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

use ILIAS\Data\URI;
use ILIAS\Data\Result\Error;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ilTemplate;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Modal\Modal;

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
            $this->fail(...),
            null,
        ));
    }

    public function testInvoke(): void
    {
        $return = fn() => null;
        $footer = fn() => $return;

        $instance = new ModifyFooter(
            $this->mock(UI::class),
            $this->mockTree(User::class, ['acceptedVersion' => new Ok($this->mock(DocumentContent::class))]),
            $this->mock(Provide::class),
            fn() => 'rendered',
            fn() => $this->mock(ilTemplate::class),
            null,
        );

        $this->assertSame($return, $instance($footer));
    }

    public function testInvokeWithGotoLink(): void
    {
        $dummy_uri = $this->mock(URI::class);
        $return = fn() => null;
        $footer = function ($id, $title, $uri) use ($dummy_uri, $return) {
            $this->assertSame('foo', $id);
            $this->assertSame('translated', $title);
            $this->assertSame($dummy_uri, $uri);
            return $return;
        };

        $instance = new ModifyFooter(
            $this->mockTree(UI::class, ['txt' => 'translated']),
            $this->mockTree(User::class, ['acceptedVersion' => new Error('Not found.'), 'isLoggedIn' => false]),
            $this->mockTree(Provide::class, ['id' => 'foo']),
            fn() => 'rendered',
            fn() => $this->mock(ilTemplate::class),
            fn() => $dummy_uri,
        );

        $this->assertSame($return, $instance($footer));
    }

    public function testRenderModal(): void
    {
        $instance = new ModifyFooter(
            $this->mock(UI::class),
            $this->mock(User::class),
            $this->mock(Provide::class),
            fn() => 'rendered',
            fn() => $this->mock(ilTemplate::class),
            null
        );

        $modal = $instance->renderModal($this->mock(DocumentContent::class));
        $this->assertInstanceOf(Modal::class, $modal);
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
            fn() => $template,
            null
        );

        $this->assertInstanceOf(Component::class, $instance->withdrawalButton());
    }
}
