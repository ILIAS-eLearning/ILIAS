<?php

/**
 * ilCtrlTargetInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface describes how an ilCtrl link target must look
 * like.
 */
interface ilCtrlTargetInterface
{
    /**
     * $_GET request parameter names, used throughout ilCtrl.
     */
    public const PARAM_CSRF_TOKEN      = 'csrf_token';
    public const PARAM_TRACE           = 'trace';
    public const PARAM_REDIRECT        = 'redirect_source';
    public const PARAM_BASE_CLASS      = 'base_class';
    public const PARAM_CMD_CLASS       = 'cmd_class';
    public const PARAM_CMD_MODE        = 'cmd_mode';
    public const PARAM_CMD_FALLBACK    = 'cmd_fallback';
    public const PARAM_CMD             = 'cmd';

    /**
     * different modes used for UI plugins (or in dev-mode).
     */
    public const CMD_MODE_PROCESS = 'execComm';
    public const CMD_MODE_ASYNC   = 'async';
    public const CMD_MODE_HTML    = 'getHtml';

    /**
     * Sets the target- or base-script the target should aim at.
     *
     * @param string $target_script
     * @return ilCtrlTargetInterface
     */
    public function setTargetScript(string $target_script) : ilCtrlTargetInterface;

    /**
     * Appends a command class to the current target.
     *
     * @param string $class_name
     * @return ilCtrlTargetInterface
     */
    public function setCmdClass(string $class_name) : ilCtrlTargetInterface;

    /**
     * Sets the command the current command class should execute.
     *
     * @param string $cmd
     * @return ilCtrlTargetInterface
     */
    public function setCmd(string $cmd) : ilCtrlTargetInterface;

    /**
     * Sets the current targets anchor (to a position on the page).
     *
     * @param string $anchor
     * @return ilCtrlTargetInterface
     */
    public function setAnchor(string $anchor) : ilCtrlTargetInterface;

    /**
     * Sets whether the target link should be appended with an
     * async-flag.
     *
     * @param bool $is_async
     * @return ilCtrlTargetInterface
     */
    public function setAsync(bool $is_async) : ilCtrlTargetInterface;

    /**
     * Sets whether the target link should be generated with escaped
     * special characters (for xml files).
     *
     * @param bool $is_xml
     * @return ilCtrlTargetInterface
     */
    public function setXml(bool $is_xml) : ilCtrlTargetInterface;

    /**
     * Returns an URL generated from the current target's information.
     *
     * @return string|null
     */
    public function getLinkTarget() : ?string;

    /**
     * Returns an URL for form actions generated from the current target's
     * information.
     *
     * @return string|null
     */
    public function getFormAction() : ?string;
}