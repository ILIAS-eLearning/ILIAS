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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface MarkdownRenderer
{
    /**
     * Returns an endpoint where the clientside input can submit it's value to
     * and receive the current preview.
     *
     * Note that ilCtrl does not return fully qualified URIs, therefore string
     * is type-hinted.
     */
    public function getAsyncUrl(): string;

    /**
     * Returns the name of the $_POST variable the asynchronous input submits to.
     */
    public function getParameterName(): string;

    /**
     * Sends a JSON-response with the rendered preview of the submitted input.
     */
    public function renderAsync(): void;

    /**
     * Returns the rendered preview of the given string.
     */
    public function render(string $markdown_text): string;
}
