<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

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
    public function getInputs();

    /**
     * Get if the inputs are rendered
     *
     * @return    bool[]
     */
    public function isInputRendered();

    /**
     * Get a Filter like this where data from the request is attached.
     *
     * @param    ServerRequestInterface $request
     *
     * @return    Filter
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
     *
     * @return bool
     */
    public function isActivated();

    /**
     * Get a Filter like this, but already activated.
     *
     * @return Filter
     */
    public function withActivated();

    /**
     * Get a Filter like this, but deactivated.
     *
     * @return Filter
     */
    public function withDeactivated();

    /**
     * Get to know if the Filter is expanded or collapsed
     *
     * @return bool
     */
    public function isExpanded();

    /**
     * Get a Filter like this, but already expanded.
     *
     * @return Filter
     */
    public function withExpanded();

    /**
     * Get a Filter like this, but collapsed.
     *
     * @return Filter
     */
    public function withCollapsed();

    /**
     * Get the signal to update this filter
     *
     * @return Signal
     */
    public function getUpdateSignal();
}
