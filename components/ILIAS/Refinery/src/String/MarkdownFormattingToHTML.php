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
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use ILIAS\CommonMarkExtension\CommonMarkExtension\CommonMarkCoreExtension;

/**
 * This class provides a transformation that converts Markdown formatting to HTML using the `CommonMark` Library
 */
class MarkdownFormattingToHTML
{
    private MarkdownConverter $converter;

    public function __construct(bool $escape = true)
    {
        $config = [
            'html_input' => 'escape',
            'renderer' => [
                'soft_break' => "<br/>"
            ],
            'allow_unsafe_links' => false,
            'max_nesting_level' => 42 // https://commonmark.thephpleague.com/1.5/security/#nesting-level
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        $this->converter = new MarkDownConverter($environment);
    }

    /**
     * Returns the converted Markdown with HTML tags.
     */
    public function toHTML(): Transformation
    {
        return new \ILIAS\Refinery\Custom\Transformation(
            fn($value) => $this->converter->convert($value)->getContent()
        );
    }
}
