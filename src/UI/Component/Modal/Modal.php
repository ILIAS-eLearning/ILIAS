<?php
namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Onloadable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 * This describes commonalities between the different modals
 */
interface Modal extends Component, JavaScriptBindable, Triggerable, Onloadable
{


    /**
     * Get the url returning the rendered modal, if the modals content should be rendered via ajax
     *
     * @return string
     */
    public function getAsyncRenderUrl();


    /**
     * Get a modal like this who's content is rendered via ajax by the given $url before the modal is shown
     *
     * Means: After the show signal has been triggered but before the modal is displayed to the user,
     * an ajax request is sent to this url. The request MUST return the rendered output of a modal.
     *
     * @param string $url
     * @return $this
     */
    public function withAsyncRenderUrl($url);


    /**
     * Get a modal like this which can or cannot be closed by keyboard (ESC), depending on the given $state
     *
     * @param bool $state
     * @return $this
     */
    public function withCloseWithKeyboard($state);


    /**
     * Returns if this modal can be closed with the keyboard (ESC key)
     *
     * @return bool
     */
    public function getCloseWithKeyboard();


    /**
     * Get the signal to show this modal in the frontend
     *
     * @return Signal
     */
    public function getShowSignal();


    /**
     * Get the signal to close this modal in the frontend
     *
     * @return Signal
     */
    public function getCloseSignal();
}
