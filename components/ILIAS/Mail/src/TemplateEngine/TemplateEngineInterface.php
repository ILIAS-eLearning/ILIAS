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

namespace ILIAS\Mail\TemplateEngine;

/**
 * Interface for template engine functionality used in Mail and related components.
 * Abstracts the concrete template engine implementation (e.g., Mustache) to enable
 * dependency inversion and easier testing.
 */
interface TemplateEngineInterface
{
    /**
     * Renders a template string with the given context.
     *
     * @param string $template The template string.
     * @param array<string, mixed>|object $context Data for the template. Either an
     *        associative array of key-value pairs, or an object whose public
     *        properties and methods the engine uses to resolve values. Exact
     *        behavior depends on the engine implementation.
     *
     * Example context object with properties, a method, and a list for repeated
     * blocks:
     * <code>
     * $context = new class () {
     *     public string $recipient = 'Jane';
     *     public int $amount = 100;
     *     public array $lines = [
     *         ['title' => 'Item A', 'qty' => 2],
     *         ['title' => 'Item B', 'qty' => 1],
     *     ];
     *     public function net_amount(): float
     *     {
     *         return $this->amount * 0.9;
     *     }
     * };
     * </code>
     */
    public function render(string $template, array|object $context): string;
}
