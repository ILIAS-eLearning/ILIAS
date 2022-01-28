<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use InvalidArgumentException;

/**
 * Interface for a factory that provides templates.
 */
interface TemplateFactory
{
    /**
     * Get template instance.
     *
     * @throws InvalidArgumentException	if there is no such template
     */
    public function getTemplate(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks) : Template;
}
