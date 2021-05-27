<?php

namespace ILIAS\UI\Component\Toast;

use Closure;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Toast
 * @package ILIAS\UI\Component\Toast
 */
interface Toast extends Component
{
    public function __construct(string $title, Icon $icon);

    /**
     * Gets the title of the toast
     */
    public function getTitle() : string|Shy|Link;

    /**
     * Create a copy of this toast with an attached description.
     */
    public function withDescription(string $description) : Toast;

    /**
     * Get the description of the toast.
     */
    public function getDescription() : string;

    /**
     * Create a copy of this toast with a new action appended to the array of actions to perform on it.
     */
    public function withAdditionalAction(Link $action) : Toast;

    /**
     * Create a copy of this toast with an empty array of actions.
     */
    public function withoutActions() : Toast;

    /**
     * Get the actions of the toast.
     * @return Link[]
     */
    public function getActions() : array;

    /**
     * Create a copy of this toast with an url, which is called when the item title is clicked.
     */
    public function withTitleAction(string|Closure $action) : Toast;

    /**
     * Get the url, which is called when the user clicks the item title.
     */

    public function getTitleAction() : string|Closure;

    /**
     * Create a copy of this toast with an url, which is called asynchronous when the item vanishes.
     * This action will not trigger if the vanishing is provoked by the user by interacting with the toast.
     */
    public function withVanishAction(string $action) : Toast;

    /**
     * Get the url, which is called when the item vanishes without user interaction.
     */
    public function getVanishAction() : string;

    /**
     * Get icon.
     */
    public function getIcon() : Icon;
}
