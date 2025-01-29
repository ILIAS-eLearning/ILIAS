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

namespace ILIAS\Data\Text\Shape;

use ILIAS\Data\Text\Shape;
use ILIAS\Data\Text\Markup;
use ILIAS\Data\Text;
use ILIAS\Refinery;

class Markdown implements Shape
{
    protected Refinery\Transformation $markdown_to_html_transformation;

    public function __construct(
        Refinery\String\MarkdownFormattingToHTML $markdown_to_html,
    ) {
        $this->markdown_to_html_transformation = $markdown_to_html->toHTML();
    }

    public function toHTML(Text\Text $text): Text\HTML
    {
        if (!$text instanceof Text\Markdown) {
            throw new \LogicException("Text does not match format.");
        }
        return new Text\HTML(
            $this->markdown_to_html_transformation->transform(
                $text->getRawRepresentation()
            )
        );
    }

    public function toPlainText(Text\Text $text): Text\PlainText
    {
        if (!$text instanceof Text\Markdown) {
            throw new \LogicException("Text does not match format.");
        }
        return new Text\PlainText($text->getRawRepresentation());
    }

    public function getMarkup(): Markup\Markdown
    {
        return new Markup\Markdown();
    }

    public function fromString(string $text): Text\Markdown
    {
        return new Text\Markdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        return true;
    }
}
