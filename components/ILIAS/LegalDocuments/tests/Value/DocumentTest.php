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

use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Meta;
use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Value\Document;

require_once __DIR__ . '/../ContainerMock.php';

class DocumentTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Document::class, new Document(47, $this->mock(Meta::class), $this->mock(DocumentContent::class), []));
    }

    public function testGetter(): void
    {
        $this->assertGetter(Document::class, [
            'id' => 80,
            'meta' => $this->mock(Meta::class),
            'content' => $this->mock(DocumentContent::class),
            'criteria' => [$this->mock(Criterion::class), $this->mock(Criterion::class)],
        ]);
    }
}
