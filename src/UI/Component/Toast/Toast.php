<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Toast;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Interface Toast
 * @package ILIAS\UI\Component\Toast
 */
interface Toast extends Component, JavaScriptBindable
{
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
     * Create a copy of this toast with an url, which is called asynchronous when the user interact with the item.
     */
    public function withAction(string $action) : Toast;

    public function getAction() : string;

    public function getIcon() : Icon;

    /**
     * Init the default signals
     */
    public function initSignals() : void;

    /**
     * Get the signal to show this toast in the frontend
     */
    public function getShowSignal() : Signal;
}
