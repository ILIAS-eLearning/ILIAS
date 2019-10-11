<?php

namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Implementation\Component\ReplaceContentSignal;

/**
 * Describes the Popover component
 */
interface Popover extends Component, Triggerable
{
    const POS_AUTO = 'auto';
    const POS_VERTICAL = 'vertical';
    const POS_HORIZONTAL = 'horizontal';


    /**
     * Get the same popover displaying a title above the content.
     *
     * @param string $title
     *
     * @return Popover
     */
    public function withTitle($title);


    /**
     * Get the title of the popover.
     *
     * @return string
     */
    public function getTitle();


    /**
     * Get the same popover being rendered below or above the triggerer, based on the available
     * space.
     *
     * @return Popover
     */
    public function withVerticalPosition();


    /**
     * Get the same popover being rendered to the left or right of the triggerer, based on the
     * available space.
     *
     * @return Popover
     */
    public function withHorizontalPosition();


    /**
     * Get the position of the popover.
     *
     * @return string
     */
    public function getPosition();


    /**
     * Get a popover like this who's content is rendered via ajax by the given $url before the
     * popover is shown.
     *
     * Means: After the show signal has been triggered but before the popover is displayed to the
     * user, an ajax request is sent to this url. The request MUST return the rendered content for
     * the popover.
     *
     * @param string $url
     *
     * @return $this
     */
    public function withAsyncContentUrl($url);


    /**
     * Get the url returning the rendered content, if the popovers content is rendered via ajax.
     *
     * @return string
     */
    public function getAsyncContentUrl();


    /**
     * Get the signal to show this popover in the frontend.
     *
     * @return Signal
     */
    public function getShowSignal();


    /**
     * Get the signal to replace the content of this popover.
     *
     * @return ReplaceContentSignal
     */
    public function getReplaceContentSignal();


    /**
     * Get a popover which can be used in fixed places such as the main menu.
     * This popover will stay fixed when scrolling and therefore remain on the screen.
     *
     * @return $this
     */
    public function withFixedPosition();


    /**
     * @return bool whether it's fixed or not
     */
    public function isFixedPosition();
}
