<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Image;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: The standard image is used if the image is to be rendered in it's the original size.
     * ----
     * @param string $src
     * @param string $alt
     * @return  \ILIAS\UI\Component\Image\Image
     */
    public function standard(string $src, string $alt): Image;

    /**
     * ---
     * description:
     *   purpose: >
     *     A responsive image is to be used if the image needs to adapt to changing
     *     amount of space available.
     *   composition: >
     *     Responsive images scale nicely to the parent element.
     * ----
     * @param string $src
     * @param string $alt
     * @return  \ILIAS\UI\Component\Image\Image
     */
    public function responsive(string $src, string $alt): Image;
}
