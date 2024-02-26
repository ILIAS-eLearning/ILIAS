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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox\ConsumerSlots;

use ILIAS\LegalDocuments\Value\Document;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\PageFragment\PageContent;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\Agreement;
use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ILIAS\LegalDocuments\ConsumerToolbox\User;

require_once __DIR__ . '/../../ContainerMock.php';

class AgreementTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Agreement::class, new Agreement(
            $this->mock(User::class),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        ));
    }

    public function testShowAgreement(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, ['matchingDocument' => new Ok($this->mock(Document::class))]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertInstanceOf(PageContent::class, $instance->showAgreement('foo', 'bar'));
    }

    public function testShowAgreementForm(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, ['matchingDocument' => new Ok($this->mock(Document::class))]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertInstanceOf(PageContent::class, $instance->showAgreement('foo', 'bar'));
    }

    public function testNeedsToAgree(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, [
                'cannotAgree' => false,
                'neverAgreed' => false,
                'didNotAcceptCurrentVersion' => false,
            ]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertFalse($instance->needsToAgree());
    }

    public function testCannotAgree(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, [
                'cannotAgree' => true,
                'neverAgreed' => true,
                'didNotAcceptCurrentVersion' => true,
            ]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertFalse($instance->needsToAgree());
    }

    public function testNeverAgreed(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, [
                'cannotAgree' => false,
                'neverAgreed' => false,
                'needsToAcceptNewDocument' => true,
            ]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertTrue($instance->needsToAgree());
    }

    public function testDidNotAcceptCurrentVersion(): void
    {
        $instance = new Agreement(
            $this->mockTree(User::class, [
                'cannotAgree' => false,
                'neverAgreed' => true,
                'needsToAcceptNewDocument' => false,
            ]),
            $this->mock(Settings::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->fail(...)
        );

        $this->assertTrue($instance->needsToAgree());
    }
}
