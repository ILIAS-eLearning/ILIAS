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

namespace ILIAS\UI\Help\TextRetriever;

use ILIAS\UI\HelpTextRetriever;
use ILIAS\UI\Help;

/**
 * This HelpTextRetriever merges results from various other retrievers (and removes
 * duplicates).
 */
class Chaining implements HelpTextRetriever
{
    /** @var HelpTextRetriever[] */
    protected array $retrievers;

    public function __construct(
        HelpTextRetriever ...$retrievers
    ) {
        $this->retrievers = $retrievers;
    }

    public function getHelpText(Help\Purpose $purpose, Help\Topic ...$topics): array
    {
        return array_unique(
            array_merge(
                ...array_map(
                    fn ($r) => $r->getHelpText($purpose, ...$topics),
                    $this->retrievers
                )
            )
        );
    }
}
