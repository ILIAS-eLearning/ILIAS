<?php

namespace ILIAS\UI\Component\Toast;

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
    /**
     * @param string|Shy|Link $title
     */
    public function __construct($title, Icon $icon);

    /**
     * @return string|Shy|Link
     */
    public function getTitle();

    public function withDescription(string $description) : Toast;

    public function getDescription() : string;

    public function withAdditionalLink(Link $link) : Toast;

    public function withoutLinks() : Toast;

    /**
     * @return Link[]
     */
    public function getLinks() : array;

    /**
     * Create a copy of this toast with an url, which is called asynchronous when the item vanishes.
     * This action will not trigger if the vanishing is provoked by the user by interacting with the toast.
     */
    public function withVanishAction(string $action) : Toast;

    public function getVanishAction() : string;

    public function getIcon() : Icon;
}
