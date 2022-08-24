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

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Component;

/**
 * This describes how a glyph could be modified during construction of UI.
 *
 * Interface Image
 * @package ILIAS\UI\Component\Image
 */
interface Image extends Component, JavaScriptBindable, Clickable
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
