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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes;

use Attribute;

/**
 * Declares which types a normalizer class supports. Used by Transformations to register
 * and resolve normalizers. Multiple types can be given; the manager will use this normalizer
 * for each of them (no two normalizers may register for the same type).
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Normalizes
{
    /**
     * @param class-string ...$types
     */
    public function __construct(
        string ...$types
    ) {
        $this->types = $types;
    }

    /** @var list<class-string> */
    public array $types;
}
