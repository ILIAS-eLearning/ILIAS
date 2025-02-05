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

namespace ILIAS\Data\Description;

use ILIAS\Data\Text;

/**
 * This describes some datastructure in terms of standard data structures such as
 * primitives, lists, maps and objects and helpful (hopefully...) human readable
 * texts.
 * The class is not to be derived but instead acts as a common base class for all
 * classes Description\D* in the subfolder. The set of Description\D* classes is fixed.
 */
abstract class Description
{
    public function __construct(
        protected ?Text\SimpleDocumentMarkdown $description,
    ) {
    }

    public function getDescription(): ?Text\SimpleDocumentMarkdown
    {
        return $this->description;
    }

    /**
     * Each of the types that can be described has a canonical representation created
     * from primitive PHP types. This attempts to transform the provided data into
     * such a representation.
     *
     * If this returns a \Closure, the data cannot be transformed into such a
     * representation and the \Closure will produce a generator that provides a
     * list of defects where $data does not match the description. If this does
     * return something else it will be plain old php data according to the description.
     */
    abstract public function getPrimitiveRepresentation(mixed $data): mixed;

    public function matches(mixed $data): bool
    {
        if ($this->getPrimitiveRepresentation($data) instanceof \Generator) {
            return false;
        }

        return true;
    }
}
