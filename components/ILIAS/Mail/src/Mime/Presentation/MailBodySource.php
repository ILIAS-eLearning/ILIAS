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

namespace ILIAS\Mail\Mime\Presentation;

final class MailBodySource
{
    /**
     * @param \Closure(string): string|null $to_html
     */
    public function __construct(
        private readonly string $body,
        private readonly ?\Closure $to_html = null
    ) {
    }

    public function raw(): string
    {
        return $this->body;
    }

    public function transformedToHtml(): string
    {
        $body = $this->body === '' ? ' ' : $this->body;

        return $this->to_html === null ? $body : ($this->to_html)($body);
    }
}
