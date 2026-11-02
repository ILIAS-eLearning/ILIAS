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

namespace ILIAS\LegalDocuments\test;

use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\AdministrationEditLinks;
use ILIAS\LegalDocuments\Administration;
use ilLegalDocumentsAdministrationGUI;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;

class AdministrationEditLinksTest extends TestCase
{
    public function testConstruct(): void
    {
        /** @var Administration&Stub $admin */
        $admin = $this->createStub(Administration::class);
        /** @var ilLegalDocumentsAdministrationGUI&Stub $gui */
        $gui = $this->createStub(ilLegalDocumentsAdministrationGUI::class);
        $this->assertInstanceOf(AdministrationEditLinks::class, new AdministrationEditLinks($gui, $admin));
    }

    #[DataProvider('methods')]
    public function testMethods(string $method, string $target, int $argc): void
    {
        $admin = $this->getMockBuilder(Administration::class)->disableOriginalConstructor()->getMock();
        /** @var ilLegalDocumentsAdministrationGUI&Stub $gui */
        $gui = $this->createStub(ilLegalDocumentsAdministrationGUI::class);

        $args = [
            $this->createStub(Document::class),
            $this->createStub(Criterion::class),
        ];

        $args = array_slice($args, 0, $argc);

        $admin->expects($this->once())->method($target)->with($gui, ...[...$args, $method])->willReturn('my-link');

        $instance = new AdministrationEditLinks($gui, $admin);
        $this->assertSame('my-link', $instance->$method(...$args));
    }

    public static function methods(): array
    {
        return [
            ['editCriterion', 'targetWithDocAndCriterion', 2],
            ['deleteCriterion', 'targetWithDocAndCriterion', 2],
        ];
    }
}
