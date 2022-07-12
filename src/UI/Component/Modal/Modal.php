<?php declare(strict_types=1);

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
     */
    public function getAsyncRenderUrl() : string;

    /**
     * Get a modal like this who's content is rendered via ajax by the given $url before the modal is shown
     *
     * Means: After the show signal has been triggered but before the modal is displayed to the user,
     * an ajax request is sent to this url. The request MUST return the rendered output of a modal.
     *
     * @return static
     */
    public function withAsyncRenderUrl(string $url);

    /**
     * Get a modal like this which can or cannot be closed by keyboard (ESC), depending on the given $state
     *
     * @return static
     */
    public function withCloseWithKeyboard(bool $state);

    /**
     * Returns if this modal can be closed with the keyboard (ESC key)
     */
    public function getCloseWithKeyboard() : bool;

    /**
     * Get the signal to show this modal in the frontend
     */
    public function getShowSignal() : Signal;

    /**
     * Get the signal to close this modal in the frontend
     */
    public function getCloseSignal() : Signal;
}
