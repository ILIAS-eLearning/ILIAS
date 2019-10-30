<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

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
    public function standard($src, $alt);

    /**
     * ---
     * description:
     *   purpose: >
     *     A responsive image is to be used if the image needs to adapt to changing
     *     amount of space available.
     *   composition: >
     *     Responsive images scale nicely to the parent element.
     *
     * ----
     * @param string $src
     * @param string $alt
     * @return  \ILIAS\UI\Component\Image\Image
     */
    public function responsive($src, $alt);
}
