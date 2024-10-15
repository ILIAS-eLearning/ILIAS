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

namespace Data\src\TextHandling\Shape;

use Data\src\TextHandling\Text\HTML;
use Data\src\TextHandling\Text\PlainText;
use Data\src\TextHandling\Markup\Markup;
use Data\src\TextHandling\Text\Text;
use ILIAS\Refinery\Factory;

class Markdown extends Shape
{
    public function __construct(
        protected Factory $refinery,
        protected Markup $markup
    ) {
        $this->refinery = $refinery;
        $this->markup = $this->markup;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function toHTML($text): HTML
    {
        if (!is_string($text)) {
            throw new \InvalidArgumentException("Text does not match format.");
        }
        return new HTML($this->refinery->string()->markdown()->toHTML()->transform($text));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function toPlainText($text): PlainText
    {
        if (!is_string($text)) {
            throw new \InvalidArgumentException("Text does not match format.");
        }
        return new PlainText($text);
    }

    public function getMarkup(): Markup
    {
        return $this->markup;
    }

    public function fromString(string $text): Text
    {
        return new \Data\src\TextHandling\Text\Markdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        return true;
    }
}
