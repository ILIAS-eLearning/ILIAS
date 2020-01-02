<?php
namespace ILIAS\UI\Component;

/**
 * Interface Signal
 *
 * A signal describes an event of a component which can be triggered by another component acting as triggerer.
 * For example, a modal offers signals for showing and closing itself. A button (which is a triggerer component)
 * can trigger the show signal of a modal on click, which will open the modal on button click.
 *
 * @package ILIAS\UI\Component
 */
interface Signal
{

    /**
     * Get the ID of this signal
     *
     * @return string
     */
    public function getId();

    /**
     * Get the options of this signal
     *
     * @return array
     */
    public function getOptions();
}
