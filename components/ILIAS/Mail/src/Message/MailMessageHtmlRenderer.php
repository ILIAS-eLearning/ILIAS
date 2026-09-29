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

namespace ILIAS\Mail\Message;

use ILIAS\Refinery\Transformation;

/**
 * Renders a stored mail body for an HTML view.
 *
 * The body is Markdown. Rows written before ILIAS 11 may contain raw HTML
 * such as `<b>`. The Markdown renderer escapes that HTML once, so the same
 * text stays visible and does not become active markup. The result must not
 * be passed through html_entity_decode(): that would turn the escaped tags
 * back into HTML.
 *
 * Curly braces are encoded afterwards so unresolved template placeholders are
 * not consumed by the ILIAS template engine.
 */
readonly class MailMessageHtmlRenderer
{
    public function __construct(
        private Transformation $markdown_to_html,
        private Transformation $make_clickable
    ) {
    }

    public function render(string $message, bool $make_urls_clickable): string
    {
        $html = $this->markdown_to_html->transform($message) ?? '';
        if (!\is_string($html)) {
            $html = '';
        }

        if ($make_urls_clickable) {
            $html = $this->make_clickable->transform($html);
        }

        return str_replace(['{', '}'], ['&#123;', '&#125;'], $html);
    }
}
