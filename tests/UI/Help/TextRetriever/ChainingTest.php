<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\UI\Help\TextRetriever;

use ILIAS\UI\HelpTextRetriever;
use ILIAS\UI\Help\TextRetriever\Chaining;
use ILIAS\UI\Help\Topic;
use ILIAS\UI\Help\Purpose;
use PHPUnit\Framework\TestCase;

class ChainingTest extends TestCase
{
    public function setUp(): void
    {
        $this->retriever_a = new class () implements HelpTextRetriever {
            public function getHelpText(Purpose $purpose, Topic ...$topics): array
            {
                return ["a"];
            }
        };

        $this->retriever_b = new class () implements HelpTextRetriever {
            public function getHelpText(Purpose $purpose, Topic ...$topics): array
            {
                return ["b"];
            }
        };
    }

    public function testGetHelpTextChaining(): void
    {
        $retriever = new Chaining($this->retriever_a, $this->retriever_b);

        $result = $retriever->getHelpText(new Purpose(Purpose::PURPOSE_TOOLTIP));

        $this->assertEquals(["a", "b"], $result);
    }

    public function testGetHelpTextRemovesDuplicates(): void
    {
        $retriever = new Chaining($this->retriever_a, $this->retriever_a);

        $result = $retriever->getHelpText(new Purpose(Purpose::PURPOSE_TOOLTIP));

        $this->assertEquals(["a"], $result);
    }
}
