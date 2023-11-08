<?php

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
 ********************************************************************
 */

/**
 * Session data handling for filter ui service
 *
 * @author killing@leifos.de
 * @ingroup ServicesUI
 */
class ilUIFilterServiceSessionGateway
{
    public const TYPE_VALUE = "value";              // value of an input
    public const TYPE_RENDERED = "rendered";        // is input rendered or not?
    public const TYPE_ACTIVATED = "activated";      // is filter activated?
    public const TYPE_EXPANDED = "expanded";        // is filter expanded?

    /**
     * Write session value for an input field
     */
    public function writeValue(string $filter_id, string $input_id, $value): void
    {
        $session = ilSession::get("ui");
        $value = serialize($value);
        $session["filter"][$filter_id][self::TYPE_VALUE][$input_id] = $value;
        ilSession::set("ui", $session);
    }

    public function getValue(string $filter_id, string $input_id)
    {
        $session = ilSession::get("ui");
        if (isset($session["filter"][$filter_id][self::TYPE_VALUE][$input_id])) {
            return unserialize($session["filter"][$filter_id][self::TYPE_VALUE][$input_id]);
        }

        return null;
    }

    public function writeRendered(string $filter_id, string $input_id, bool $value): void
    {
        $session = ilSession::get("ui");
        $session["filter"][$filter_id][self::TYPE_RENDERED][$input_id] = $value;
        ilSession::set("ui", $session);
    }

    public function isRendered(string $filter_id, string $input_id, bool $default): bool
    {
        $session = ilSession::get("ui");
        if (isset($session["filter"][$filter_id][self::TYPE_RENDERED][$input_id])) {
            return (bool) $session["filter"][$filter_id][self::TYPE_RENDERED][$input_id];
        }

        return $default;
    }

    /**
     * Resets filter to its default state
     */
    public function reset(string $filter_id): void
    {
        $session = ilSession::get("ui");
        $session["filter"][$filter_id] = null;
        ilSession::set("ui", $session);
    }

    public function writeActivated(string $filter_id, bool $value): void
    {
        $session = ilSession::get("ui");
        $session["filter"][$filter_id][self::TYPE_ACTIVATED] = $value;
        ilSession::set("ui", $session);
    }

    public function writeExpanded(string $filter_id, bool $value): void
    {
        $session = ilSession::get("ui");
        $session["filter"][$filter_id][self::TYPE_EXPANDED] = $value;
        ilSession::set("ui", $session);
    }

    public function isActivated(string $filter_id, bool $default): bool
    {
        $session = ilSession::get("ui");
        if (isset($session["filter"][$filter_id][self::TYPE_ACTIVATED])) {
            return (bool) $session["filter"][$filter_id][self::TYPE_ACTIVATED];
        }

        return $default;
    }

    public function isExpanded(string $filter_id, bool $default): bool
    {
        $session = ilSession::get("ui");
        if (isset($session["filter"][$filter_id][self::TYPE_EXPANDED])) {
            return (bool) $session["filter"][$filter_id][self::TYPE_EXPANDED];
        }

        return $default;
    }
}
