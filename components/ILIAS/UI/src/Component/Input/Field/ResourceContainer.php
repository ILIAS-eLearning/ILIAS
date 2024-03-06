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
 */

namespace ILIAS\UI\Component\Input\Field;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ResourceContainer extends Resource
{
    /**
     * Returns a container like this, but will indicate to the resource selector
     * to render and load its children asynchronously.
     */
    public function withRenderChildrenAsync(bool $render_async): self;

    /**
     * @return Resource[]
     */
    public function getResources(): array;
}
