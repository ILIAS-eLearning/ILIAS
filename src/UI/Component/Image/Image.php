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

namespace ILIAS\UI\Component\Image;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Layout\Alignment\Block;

/**
 * This describes how a glyph could be modified during construction of UI.
 *
 * Interface Image
 * @package ILIAS\UI\Component\Image
 */
interface Image extends Component, JavaScriptBindable, Clickable, Block
{
    /**
     * Types of images
     */
    public const STANDARD = "standard";
    public const RESPONSIVE = "responsive";

    /**
     * Set the source (path) of the image. The complete path to the image has to be provided.
     */
    public function withSource(string $source): Image;

    /**
     * Get the source (path) of the image.
     */
    public function getSource(): string;

    /**
     * Add an additional source (path) pointing to an image of higher resolution
     * than the one set through `withSource()`.
     * The corresponding image will be asynchronously loaded once the size of the
     * final image on the screen is defined. The high res source will be used,
     * if the size of the image shown on the screen is bigger than
     * `$min_width_in_pixels`. If multiple additional high res sources are defined,
     * the one with the biggest `$min_width_in_pixels` that is still
     * smaller than the actual size of the image on the screen will be chosen.
     * To take advantage of this functionality the source set through `withSource()`
     * must be small as it will always be loaded first.
     */
    public function withAdditionalHighResSource(string $source, int $min_width_in_pixels): Image;

    /**
     * Returns an associative array containing all additional resources as
     * `$min_width_in_pixels => $source` entries.
     *
     * @return array<integer, string>
     */
    public function getAdditionalHighResSources(): array;

    /**
     * Get the type of the image
     */
    public function getType(): string;

    /**
     * Set the alternative text for screen readers.
     */
    public function withAlt(string $alt): Image;


    /**
     * Get the alternative text for screen readers.
     */
    public function getAlt(): string;

    /**
     * Get an image like this with an action
     * @param string|Signal[] $action
     */
    public function withAction($action): Image;

    /**
     * Get the action of the image
     * @return string|Signal[]
     */
    public function getAction();
}
