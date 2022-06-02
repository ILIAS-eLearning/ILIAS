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
 
namespace ILIAS\UI\Component\Input\Container\Filter;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Input\Field\Input;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes commonalities between all filters.
 */
interface Filter extends Component, JavaScriptBindable, Triggerable
{

    /**
     * Get the action which is passed for the activated Toggle Button.
     *
     * @return string|Signal
     */
    public function getToggleOnAction();

    /**
     * Get the action which is passed for the deactivated Toggle Button.
     *
     * @return string|Signal
     */
    public function getToggleOffAction();

    /**
     * Get the action which is passed for the Filter when it is expanded.
     *
     * @return string|Signal
     */
    public function getExpandAction();

    /**
     * Get the action which is passed for the Filter when it is collapsed.
     *
     * @return string|Signal
     */
    public function getCollapseAction();

    /**
     * Get the action which is passed for the Apply Button.
     *
     * @return string|Signal
     */
    public function getApplyAction();

    /**
     * Get the action which is passed for the Reset Button.
     *
     * @return string|Signal
     */
    public function getResetAction();

    /**
     * Get the inputs contained in the Filter.
     *
     * @return    array<mixed,Input>
     */
    public function getInputs() : array;

    /**
     * Get if the inputs are rendered
     *
     * @return    bool[]
     */
    public function isInputRendered() : array;

    /**
     * Get a Filter like this where data from the request is attached.
     */
    public function withRequest(ServerRequestInterface $request);

    /**
     * Get the data in the Filter if all inputs are ok. If data was not ok, this will return null.
     *
     * @return    mixed|null
     */
    public function getData();

    /**
     * Get to know if the Filter is activated or deactivated
     */
    public function isActivated() : bool;

    /**
     * Get a Filter like this, but already activated.
     */
    public function withActivated() : Filter;

    /**
     * Get a Filter like this, but deactivated.
     */
    public function withDeactivated() : Filter;

    /**
     * Get to know if the Filter is expanded or collapsed
     */
    public function isExpanded() : bool;

    /**
     * Get a Filter like this, but already expanded.
     */
    public function withExpanded() : Filter;

    /**
     * Get a Filter like this, but collapsed.
     */
    public function withCollapsed() : Filter;

    /**
     * Get the signal to update this filter
     */
    public function getUpdateSignal() : Signal;
}
