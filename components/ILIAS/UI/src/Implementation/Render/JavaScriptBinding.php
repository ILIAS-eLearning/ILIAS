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

namespace ILIAS\UI\Implementation\Render;

/**
 * Provides methods to interface with javascript.
 */
interface JavaScriptBinding
{
    /**
     * Create a fresh unique id.
     *
     * This MUST return a new id on every call.
     */
    public function createId(): string;

    /**
     * Add some JavaScript-statements to the on-load handler of the page.
     */
    public function addOnLoadCode(string $code): void;

    /**
     * Get all the registered on-load javascript code for the async context, e.g. return all code
     * inside <script> tags
     */
    public function getOnLoadCodeAsync(): string;

    /**
     * Clears all JavaScript code currently registered for the async context.
     *
     * This is necessary because rendering can register JavaScript as a side effect,
     * even when the rendered output is not required by the current async endpoint.
     *
     * This is caused by ILIAS treating controllers as independent layers, meaning a
     * controller cannot reliably determine what has been executed before it, while
     * each layer may contribute parts of the page. This causes unnecessary rendering
     * and JavaScript registration. This method prevents said JavaScript from leaking
     * into the async response.
     *
     * This is a reminder that we want proper routing inside ILIAS:
     * https://docu.ilias.de/go/wiki/wpage_8780_1357
     */
    public function clearOnLoadCodeAsync(): void;
}
