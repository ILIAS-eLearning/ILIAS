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
