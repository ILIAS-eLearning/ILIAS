<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Component;

/**
 * Interface Card
 * @package ILIAS\UI\Component\Card
 */
interface Card extends Component
{

    /**
     * Sets the title in the heading section of the card
     * @param string|\ILIAS\UI\Component\Button\Shy $title
     * @return Standard
     */
    public function withTitle($title);

    /**
     * Get the title in the heading section of the card
     * @return string|\ILIAS\UI\Component\Button\Shy
     */
    public function getTitle();

    /**
     * Get a Card like this with a title action
     * @param string $url
     * @return Standard
     */
    public function withTitleAction($url);

    /**
     * Returns the title action if given, otherwise null
     * @return string|null
     */
    public function getTitleAction();

    /**
     * Set multiple sections of the card as array
     * @param \ILIAS\UI\Component\Component[] $sections
     * @return Standard
     */
    public function withSections(array $sections);

    /**
     * Get the multiple sections of the card as array
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getSections();

    /**
     * Set the image of the card
     * @param \ILIAS\UI\Component\Image\Image $image
     * @return Standard
     */
    public function withImage(\ILIAS\UI\Component\Image\Image $image);

    /**
     * Get the image of the card
     * @return mixed
     */
    public function getImage();

    /**
     * Get a Card like this with an image action
     * @param string $url
     * @return Standard
     */
    public function withImageAction($url);

    /**
     * Returns the image action if given, otherwise null
     * @return string|null
     */
    public function getImageAction();

    /**
     * Get a Card like this with a highlight
     * @param bool $status
     * @return Standard
     */
    public function withHighlight($status);

    /**
     * Returns whether or not the Card is highlighted
     * @return bool
     */
    public function isHighlighted();
}
