<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Trigger\Triggerable;
use ILIAS\UI\Component\Trigger\TriggerAction;

/**
 * This describes commonalities between the different modals
 */
interface Modal extends Component, JavaScriptBindable, Triggerable
{

    /**
     * Get the title of the modal
     *
     * @return string
     */
    public function getTitle();


    /**
     * Get the component representing the content of the modal
     *
     * @return \ILIAS\UI\Component\Component
     */
    public function getContent();


    /**
     * Get all buttons of the modal
     *
     * @return \ILIAS\UI\Component\Button\Button[]
     */
    public function getButtons();


    /**
     * Get a modal like this with another title
     *
     * @param string $title
     * @return Modal
     */
    public function withTitle($title);


    /**
     * Get a modal like this with another content
     *
     * @param \ILIAS\UI\Component\Component $content
     * @return Modal
     */
    public function withContent(Component $content);


    /**
     * @return TriggerAction
     */
    public function show();


    /**
     * @return TriggerAction
     */
    public function close();

}
