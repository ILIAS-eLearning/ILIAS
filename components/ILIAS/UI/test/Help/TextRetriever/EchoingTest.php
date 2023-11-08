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

namespace ILIAS\Tests\UI\Help\TextRetriever;

use ILIAS\UI\Help\TextRetriever\Echoing;
use ILIAS\UI\Help\Topic;
use ILIAS\UI\Help\Purpose;
use PHPUnit\Framework\TestCase;

class EchoingTest extends TestCase
{
    protected Echoing $retriever;

    public function setUp(): void
    {
        $this->retriever = new Echoing();
    }

    public function testGetHelpTextEchoes(): void
    {
        $result = $this->retriever->getHelpText(
            new Purpose(Purpose::PURPOSE_TOOLTIP),
            new Topic("foo"),
            new Topic("bar")
        );

        $this->assertEquals(["tooltip: foo", "tooltip: bar"], $result);
    }
}
