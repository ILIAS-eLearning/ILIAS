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

use ILIAS\Data\Text\SimpleDocumentMarkdown;

class Factory
{
    public function int(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::INT);
    }

    public function float(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::FLOAT);
    }

    public function string(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::STRING);
    }

    public function datetime(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::DATETIME);
    }

    public function bool(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::BOOL);
    }

    public function null(SimpleDocumentMarkdown $description): Description
    {
        return new DValue($description, ValueType::NULL);
    }

    public function list(SimpleDocumentMarkdown $description, Description $value_type): Description
    {
        return new DList($description, $value_type);
    }

    public function map(SimpleDocumentMarkdown $description, DValue $key_type, Description $value_type): Description
    {
        return new DMap($description, $key_type, $value_type);
    }

    /**
     * @param array<string, Description> $fields
     */
    public function object(SimpleDocumentMarkdown $description, array $fields)
    {
        return new DObject($description, ...array_map(
            fn($k, $v) => new Field($k, $v),
            array_keys($fields),
            array_values($fields)
        ));
    }
}
