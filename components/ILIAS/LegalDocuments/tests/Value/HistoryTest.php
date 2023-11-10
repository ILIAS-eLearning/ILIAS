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

namespace ILIAS\LegalDocuments\test\Value;

use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Edit;
use ILIAS\LegalDocuments\Value\Document;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Value\History;

require_once __DIR__ . '/../ContainerMock.php';

class HistoryTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(History::class, new History($this->mock(Document::class), $this->mock(Edit::class), [], $this->mock(DocumentContent::class)));
    }

    public function testGetter(): void
    {
        $this->assertGetter(History::class, [
            'currentDocument' => $this->mock(Document::class),
            'creation' => $this->mock(Edit::class),
            'criteriaContent' => [$this->mock(CriterionContent::class)],
            'documentContent' => $this->mock(DocumentContent::class),
        ]);
    }
}
