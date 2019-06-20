<?php namespace ILIAS\GlobalScreen\Scope\Layout\Modifier;

use ILIAS\UI\Component\Image\Image;

/**
 * Interface LogoModifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface LogoModifier
{

    /**
     * @param Image $current
     *
     * @return Image
     */
    public function getLogo(Image $current) : Image;
}
