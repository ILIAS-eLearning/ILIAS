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

namespace ILIAS\Mail\TemplateEngine\Mustache;

use ILIAS\Mail\TemplateEngine\TemplateEngineInterface;

/**
 * Mustache implementation of the template engine interface.
 * Wraps \Mustache\Engine to decouple Mail component from the concrete library.
 */
class MustacheTemplateEngine implements TemplateEngineInterface
{
    public function __construct(
        private readonly \Mustache\Engine $engine
    ) {
    }

    public function render(string $template, array|object $context): string
    {
        return $this->engine->render($template, $context);
    }
}
