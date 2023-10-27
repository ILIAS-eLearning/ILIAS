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

class AdministrationEditLinksTest extends TestCase
{
    public function testConstruct(): void
    {
        $admin = $this->getMockBuilder(Administration::class)->disableOriginalConstructor()->getMock();
        $gui = $this->getMockBuilder(ilLegalDocumentsAdministrationGUI::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(AdministrationEditLinks::class, new AdministrationEditLinks($gui, $admin));
    }

    /**
     * @dataProvider methods
     */
    public function testMethods(string $method, string $target, int $argc): void
    {
        $admin = $this->getMockBuilder(Administration::class)->disableOriginalConstructor()->getMock();
        $gui = $this->getMockBuilder(ilLegalDocumentsAdministrationGUI::class)->disableOriginalConstructor()->getMock();

        $args = [
            $this->getMockBuilder(Document::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Criterion::class)->disableOriginalConstructor()->getMock(),
        ];

        $args = array_slice($args, 0, $argc);

        $admin->expects(self::once())->method($target)->with($gui, ...[...$args, $method])->willReturn('my-link');

        $instance = new AdministrationEditLinks($gui, $admin);
        $this->assertSame('my-link', $instance->$method(...$args));
    }

    public function methods(): array
    {
        return [
            ['addCriterion', 'targetWithDoc', 1],
            ['editDocument', 'targetWithDoc', 1],
            ['deleteDocument', 'targetWithDoc', 1],
            ['editCriterion', 'targetWithDocAndCriterion', 2],
            ['deleteCriterion', 'targetWithDocAndCriterion', 2],
        ];
    }
}
