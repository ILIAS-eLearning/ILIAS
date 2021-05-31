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
    public const TYPE_VALUE = "value";                // value of an input
    public const TYPE_RENDERED = "rendered";        // is input rendered or not?
    public const TYPE_ACTIVATED = "activated";        // is filter activated?
    public const TYPE_EXPANDED = "expanded";        // is filter expanded?

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
        $_SESSION["ui"]["filter"][$filter_id][self::TYPE_VALUE][$input_id] = $value;
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
        if (isset($_SESSION["ui"]["filter"][$filter_id][self::TYPE_VALUE][$input_id])) {
            return unserialize($_SESSION["ui"]["filter"][$filter_id][self::TYPE_VALUE][$input_id]);
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
        $_SESSION["ui"]["filter"][$filter_id][self::TYPE_RENDERED][$input_id] = $value;
    }

    /**
     * Is rendered status active?
     *
     * @param string $filter_id
     * @param string $input_id
     * @param bool $default
     * @return bool
     */
    public function isRendered(string $filter_id, string $input_id, bool $default) : bool
    {
        if (isset($_SESSION["ui"]["filter"][$filter_id][self::TYPE_RENDERED][$input_id])) {
            return $_SESSION["ui"]["filter"][$filter_id][self::TYPE_RENDERED][$input_id];
        }
        return $default;
    }


    /**
     * Resets filter to its default state
     * @param string $filter_id
     */
    public function reset(string $filter_id)
    {
        unset($_SESSION["ui"]["filter"][$filter_id]);
    }


    /**
     * Write activation info of filter
     *
     * @param string $filter_id
     * @param bool $value
     */
    public function writeActivated(string $filter_id, bool $value)
    {
        $_SESSION["ui"]["filter"][$filter_id][self::TYPE_ACTIVATED] = $value;
    }

    /**
     * Write expand info of filter
     *
     * @param string $filter_id
     * @param bool $value
     */
    public function writeExpanded(string $filter_id, bool $value)
    {
        $_SESSION["ui"]["filter"][$filter_id][self::TYPE_EXPANDED] = $value;
    }

    /**
     * Is activated?
     *
     * @param string $filter_id
     * @param bool $default
     * @return bool
     */
    public function isActivated(string $filter_id, bool $default) : bool
    {
        if (isset($_SESSION["ui"]["filter"][$filter_id][self::TYPE_ACTIVATED])) {
            return (bool) $_SESSION["ui"]["filter"][$filter_id][self::TYPE_ACTIVATED];
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
    public function isExpanded(string $filter_id, bool $default) : bool
    {
        if (isset($_SESSION["ui"]["filter"][$filter_id][self::TYPE_EXPANDED])) {
            return (bool) $_SESSION["ui"]["filter"][$filter_id][self::TYPE_EXPANDED];
        }
        return $default;
    }
}
