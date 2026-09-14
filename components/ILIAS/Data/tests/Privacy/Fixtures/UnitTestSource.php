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

namespace ILIAS\Data\Privacy\Fixtures;

use ILIAS\Data\Privacy\Source\Source;

/**
 * Source for privacy data type instances constructed in unit tests.
 *
 * Lives in the test fixtures on purpose — production code must state a
 * real source.
 */
final readonly class UnitTestSource implements Source
{
    public function __construct(
        private string $context = 'unit_test',
    ) {
    }

    public function describe(): string
    {
        return "test:{$this->context}";
    }
}
