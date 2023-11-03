<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use \ILIAS\DI\UIServices;
use \ILIAS\UI\Component\Input\Container\Filter;
use \ILIAS\UI\Component\Input\Field\FilterInput;

/**
 * Filter service. Wraps around KS filter container.
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIFilterService
{
    // command constants
    public const CMD_TOGGLE_ON = "toggleOn";
    public const CMD_TOGGLE_OFF = "toggleOff";
    public const CMD_EXPAND = "expand";
    public const CMD_COLLAPSE = "collapse";
    public const CMD_APPLY = "apply";
    public const CMD_RESET = "reset";


    /**
     * @var ilUIService
     */
    protected $service;

    /**
     * @var UIServices
     */
    protected $ui;

    /**
     * @var ilUIFilterServiceSessionGateway
     */
    protected $session;

    /**
     * @var ilUIFilterRequestAdapter
     */
    protected $request;

    /**
     * Constructor
     * @param ilUIService $service
     * @param ilUIServiceDependencies $deps
     */
    public function __construct(ilUIService $service, ilUIServiceDependencies $deps)
    {
        $this->service = $service;
        $this->session = $deps->getSession();
        $this->request = $deps->getRequest();
        $this->ui = $deps->ui();
    }


    /**
     * Get standard filter instance
     *
     * @param string $filter_id
     * @param string $base_action
     * @param FilterInput[] $inputs
     * @param bool[] $is_input_initially_rendered
     * @param bool $is_activated
     * @param bool $is_expanded
     * @return Filter\Standard
     */
    public function standard(
        string $filter_id,
        string $base_action,
        array $inputs,
        array $is_input_initially_rendered,
        bool $is_activated = false,
        bool $is_expanded = false
    ) : Filter\Standard {
        $ui = $this->ui->factory();

        // write expand, activation, rendered inputs info to session
        $this->writeFilterStatusToSession($filter_id, $inputs);

        // handle the reset command
        $this->handleReset($filter_id);

        // determine activation/expand status
        $is_activated = $this->session->isActivated($filter_id, $is_activated);
        $is_expanded = $this->session->isExpanded($filter_id, $is_expanded);

        // put data from session into filter
        $inputs_with_session_data = [];
        $is_input_initially_rendered_with_session = [];

        if (count($inputs) != count($is_input_initially_rendered)) {
            throw new \ArgumentCountError(
                "Inputs and boolean values for initial rendering must be arrays of same size."
            );
        }

        foreach ($inputs as $input_id => $i) {
            // rendering information
            $rendered =
                $this->session->isRendered($filter_id, $input_id, current($is_input_initially_rendered));
            $is_input_initially_rendered_with_session[] = $rendered;
            next($is_input_initially_rendered);

            // values
            $val = $this->session->getValue($filter_id, $input_id);
            if (!is_null($val)) {
                try {
                    $i = $i->withValue($val);
                } catch (InvalidArgumentException $e) {
                }
            }
            $inputs_with_session_data[$input_id] = $i;
        }

        // get the filter
        $filter = $ui->input()->container()->filter()->standard(
            $this->request->getAction($base_action, self::CMD_TOGGLE_ON, true),
            $this->request->getAction($base_action, self::CMD_TOGGLE_OFF, true),
            $this->request->getAction($base_action, self::CMD_EXPAND),
            $this->request->getAction($base_action, self::CMD_COLLAPSE),
            $this->request->getAction($base_action, self::CMD_APPLY, true),
            $this->request->getAction($base_action, self::CMD_RESET, true),
            $inputs_with_session_data,
            $is_input_initially_rendered_with_session,
            $is_activated,
            $is_expanded
        );

        // handle apply and toggle commands
        $filter = $this->handleApplyAndToggle($filter_id, $filter);

        return $filter;
    }

    /**
     * Get data
     *
     * @param Filter\Standard $filter
     * @return array|null
     */
    public function getData(Filter\Standard $filter) : ?array
    {
        $filter_data = null;
        if ($filter->isActivated()) {
            foreach ($filter->getInputs() as $k => $i) {
                $filter_data[$k] = $i->getValue();
            }
        }
        return $filter_data;
    }

    /**
     * Write filter status to session (filter activated/expanded, inputs being rendered or not)
     * @param string $filter_id
     * @param array $inputs
     */
    protected function writeFilterStatusToSession($filter_id, $inputs)
    {
        if ($this->request->getFilterCmd() == self::CMD_TOGGLE_ON) {
            $this->handleRendering($filter_id, $inputs);
            $this->session->writeActivated($filter_id, true);
        }

        if ($this->request->getFilterCmd() == self::CMD_TOGGLE_OFF) {
            $this->handleRendering($filter_id, $inputs);
            $this->session->writeActivated($filter_id, false);
        }

        if ($this->request->getFilterCmd() == self::CMD_EXPAND) {
            $this->session->writeExpanded($filter_id, true);
        }

        if ($this->request->getFilterCmd() == self::CMD_COLLAPSE) {
            $this->session->writeExpanded($filter_id, false);
        }

        if ($this->request->getFilterCmd() == self::CMD_APPLY) {
            $this->handleRendering($filter_id, $inputs);
            // always activate the filter when it is applied
            $this->session->writeActivated($filter_id, true);
        }
    }

    /**
     * Handle rendering of inputs to session
     * @param string $filter_id
     * @param array $inputs
     */
    protected function handleRendering($filter_id, $inputs)
    {
        foreach ($inputs as $input_id => $i) {
            if ($this->request->isInputRendered($input_id)) {
                $this->session->writeRendered($filter_id, $input_id, true);
            } else {
                $this->session->writeRendered($filter_id, $input_id, false);
            }
        }
    }

    /**
     * Handle reset command
     *
     * @param string $filter_id
     */
    protected function handleReset(string $filter_id)
    {
        // clear session, if reset is pressed
        if ($this->request->getFilterCmd() == self::CMD_RESET) {
            $this->session->reset($filter_id);
        }
    }


    /**
     * Handle apply and toggle commands
     *
     * @param string $filter_id
     * @param Filter\Standard $filter
     * @return Filter\Standard
     */
    protected function handleApplyAndToggle(string $filter_id, Filter\Standard $filter) : Filter\Standard
    {
        if ((in_array(
            $this->request->getFilterCmd(),
            [self::CMD_APPLY, self::CMD_TOGGLE_ON, self::CMD_TOGGLE_OFF]
        ))) {
            $filter = $this->request->getFilterWithRequest($filter);

            // always expand the filter, when it is activated with empty input values
            if ($this->request->getFilterCmd() == self::CMD_TOGGLE_ON) {
                $result = $filter->getData();
                $expand = true;
                foreach ($result as $k => $v) {
                    if (!empty($v) || $v === 0 || $v === "0") {
                        $expand = false;
                    }
                }
                if ($expand) {
                    $this->session->writeExpanded($filter_id, true);
                    $filter = $filter->withExpanded();
                }
            }

            foreach ($filter->getInputs() as $input_id => $i) {
                $this->session->writeValue($filter_id, $input_id, $i->getValue());
            }
        }
        return $filter;
    }
}
