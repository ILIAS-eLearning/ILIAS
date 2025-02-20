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

interface LatexResources
{
    /**
     * These resources must be registered on a page for Latex to be rendered
     * @return string[] target path relative to the public directory
     */
    public function toRegister(): array;

    /**
     * These resources must be provided for Latex to be rendered
     * - includes the resources which are registered on the page
     * - includes resources that MathJax dynamically loads when needed
     *
     * This function returns a key/value list
     * - key is the source path relative to the ILIAS directory
     * - value is the target path relative to the public folder
     *
     * The UI framework registers its assets from this list
     * Other components can use it to export the assets needed for offline content
     *
     * Please note that paths can be files or directories
     *
     * @return array<string, string>
     */
    public function toProvide(): array;
}