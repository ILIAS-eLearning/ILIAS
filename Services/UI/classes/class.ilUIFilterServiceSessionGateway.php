<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Session data handling for filter ui service
 *
 * @author killing@leifos.de
 * @ingroup ServicesUI
 */
class ilUIFilterServiceSessionGateway
{
	const TYPE_VALUE = "value";				// value of an input
	const TYPE_RENDERED = "rendered";		// is input rendered or not?
	const TYPE_ACTIVATED = "activated";		// is filter activated?
	const TYPE_EXPANDED = "expanded";		// is filter expanded?

	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Write session value for an input field
	 *
	 * @param string $filter_id
	 * @param string $input_id
	 * @param $value
	 */
	public function writeValue(string $filter_id, string $input_id, $value)
	{
		$value = serialize($value);
		$_SESSION["ui"]["filter"][self::TYPE_VALUE][$filter_id][$input_id] = $value;
	}

	/**
	 * Get value
	 *
	 * @param string $filter_id
	 * @param string $input_id
	 * @return mixed|null
	 */
	public function getValue(string $filter_id, string $input_id)
	{
		if (isset($_SESSION["ui"]["filter"][self::TYPE_VALUE][$filter_id][$input_id]))
		{
			return unserialize($_SESSION["ui"]["filter"][self::TYPE_VALUE][$filter_id][$input_id]);
		}
		return null;
	}


	/**
	 * Write rendered information
	 *
	 * @param string $filter_id
	 * @param string $input_id
	 * @param $value
	 */
	public function writeRendered(string $filter_id, string $input_id, bool $value)
	{
		$_SESSION["ui"]["filter"][self::TYPE_RENDERED][$filter_id][$input_id] = $value;
	}

	/**
	 * Is rendered status active?
	 *
	 * @param string $filter_id
	 * @param string $input_id
	 * @param bool $default
	 * @return bool
	 */
	public function isRendered(string $filter_id, string $input_id, bool $default): bool
	{
		if (isset($_SESSION["ui"]["filter"][self::TYPE_RENDERED][$filter_id][$input_id]))
		{
			return $_SESSION["ui"]["filter"][self::TYPE_RENDERED][$filter_id][$input_id];
		}
		return $default;
	}


	/**
	 * Resets values and rendered status
	 * @param string $filter_id
	 */
	public function reset(string $filter_id)
	{
		if (is_array($_SESSION["ui"]["filter"][self::TYPE_VALUE][$filter_id]))
		{
			unset($_SESSION["ui"]["filter"][self::TYPE_VALUE][$filter_id]);
		}
		unset($_SESSION["ui"]["filter"][self::TYPE_RENDERED][$filter_id]);
	}


	/**
	 * Write activation info of filter
	 *
	 * @param string $filter_id
	 * @param bool $value
	 */
	public function writeActivated(string $filter_id, bool $value)
	{
		$_SESSION["ui"]["filter"][self::TYPE_ACTIVATED][$filter_id] = $value;
	}

	/**
	 * Write expand info of filter
	 *
	 * @param string $filter_id
	 * @param bool $value
	 */
	public function writeExpanded(string $filter_id, bool $value)
	{
		$_SESSION["ui"]["filter"][self::TYPE_EXPANDED][$filter_id] = $value;
	}

	/**
	 * Is activated?
	 *
	 * @param string $filter_id
	 * @param bool $default
	 * @return bool
	 */
	public function isActivated(string $filter_id, bool $default): bool
	{
		if (isset($_SESSION["ui"]["filter"][self::TYPE_ACTIVATED][$filter_id])) {
			return (bool) $_SESSION["ui"]["filter"][self::TYPE_ACTIVATED][$filter_id];
		}
		return $default;
	}

	/**
	 * Is expanded?
	 *
	 * @param string $filter_id
	 * @param bool $default
	 * @return bool
	 */
	public function isExpanded(string $filter_id, bool $default): bool
	{
		if (isset($_SESSION["ui"]["filter"][self::TYPE_EXPANDED][$filter_id])) {
			return (bool) $_SESSION["ui"]["filter"][self::TYPE_EXPANDED][$filter_id];
		}
		return $default;
	}


}