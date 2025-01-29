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

namespace ILIAS\Data\Text;

use ILIAS\Data\Text\Shape;
use ILIAS\Data\Text\Markup;

abstract class TextBase implements Text
{
    public function __construct(
        protected Shape $shape,
        protected string $raw
    ) {
        if (!$shape->isRawStringCompliant($raw)) {
            throw new \InvalidArgumentException("The provided string is not compliant with the supported structure!");
        }
    }

    public function getShape(): Shape
    {
        return $this->shape;
    }

    public function getMarkup(): Markup
    {
        return $this->shape->getMarkup();
    }

    /**
     * @return Structure[]
     */
    public function getSupportedStructure(): array
    {
        return $this->shape->getSupportedStructure();
    }

    public function toHTML(): Text\HTML
    {
        return $this->shape->toHTML($this);
    }

    public function toPlainText(): Text\PlainText
    {
        return $this->shape->toPlainText($this);
    }

    public function getRawRepresentation(): string
    {
        return $this->raw;
    }
}
