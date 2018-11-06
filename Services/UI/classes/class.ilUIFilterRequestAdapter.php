<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Request adapter for filter
 *
 * @author killing@leifos.de
 * @ingroup ServicesUI
 */
class ilUIFilterRequestAdapter
{
	const CMD_PARAMETER = "cmdFilter";
	const RENDER_INPUT_BASE = "__filter_status_";

	/**
	 * @var \Psr\Http\Message\ServerRequestInterface
	 */
	protected $request;

	/**
	 * query params
	 * @var array
	 */
	protected $params;

	/**
	 * post data
	 * @var array|null
	 */
	protected $post;

	/**
	 * Constructor
	 */
	public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
	{
		$this->request = $request;
		$this->params = $this->request->getQueryParams();
		$this->post = $this->request->getParsedBody();
	}

	/**
	 * Get filter command
	 * @return string
	 */
	public function getFilterCmd(): string
	{
		if (isset($this->params[self::CMD_PARAMETER]))
		{
			return (string) $this->params[self::CMD_PARAMETER];
		}
		return "";
	}

	/**
	 * Has an input field been rendered in current post request?
	 *
	 * @param $input_id
	 * @return bool
	 */
	public function isInputRendered($input_id): bool
	{
		if (isset($this->params[self::RENDER_INPUT_BASE . $input_id]) &&
			$this->params[self::RENDER_INPUT_BASE . $input_id] === "1")
		{
			return true;
		}
		return false;
	}

	/**
	 * Get filter with request data
	 *
	 * @param \ILIAS\UI\Component\Input\Container\Filter\Standard $filter
	 * @return \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	public function getFilterWithRequest(\ILIAS\UI\Component\Input\Container\Filter\Standard $filter): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		return $filter->withRequest($this->request);
	}

	/**
	 * Get action for filter command
	 *
	 * @param string $base_action
	 * @param string $filter_cmd
	 * @return string
	 */
	public function getAction(string $base_action, string $filter_cmd): string
	{
		return $base_action."&".self::CMD_PARAMETER."=".$filter_cmd;
	}

}