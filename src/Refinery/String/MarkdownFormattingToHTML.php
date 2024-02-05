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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;

/**
 * This class provides a transformation that converts Markdown formatting to HTML using the `CommonMark` Library
 */
class MarkdownFormattingToHTML
{
    private \League\CommonMark\CommonMarkConverter $converter;

    public function __construct(bool $escape = true)
    {
        $config = [
            'allow_unsafe_links' => false,
            'max_nesting_level' => 42 // https://commonmark.thephpleague.com/1.5/security/#nesting-level
        ];

        if ($escape === true) {
            $config['html_input'] = 'escape';
        }

        $this->converter = new \League\CommonMark\CommonMarkConverter($config);
    }

    /**
     * Returns the converted Markdown with HTML tags.
     */
    public function toHTML(): Transformation
    {
        return new \ILIAS\Refinery\Custom\Transformation(
            fn ($value) => $this->converter->convert($value)->getContent()
        );
    }
}
