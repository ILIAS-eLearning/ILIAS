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

namespace ILIAS\LegalDocuments\test\DocumentId;

use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Value\Document;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\DocumentId\NumberId;

require_once __DIR__ . '/../ContainerMock.php';

class NumberIdTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(NumberId::class, new NumberId($this->mock(Document::class)));
    }

    public function testNumber(): void
    {
        $this->assertSame(790, (new NumberId($this->mockMethod(Document::class, 'id', [], 790)))->number());
    }
}
