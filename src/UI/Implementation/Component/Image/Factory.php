<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Component\Image\Image as I;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Factory implements \ILIAS\UI\Component\Image\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($src, $alt)
    {
        return new Image(I::STANDARD, $src, $alt);
    }

    /**
     * @inheritdoc
     */
    public function responsive($src, $alt)
    {
        return new Image(I::RESPONSIVE, $src, $alt);
    }
}
