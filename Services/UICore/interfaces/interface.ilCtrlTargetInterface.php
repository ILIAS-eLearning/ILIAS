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
     * Returns the baseclass the target was initialized with.
     *
     * @return string
     */
    public function getBaseClass() : string;

    /**
     * Sets the target- or base-script the target should aim at.
     *
     * @param string $target_script
     * @return ilCtrlTargetInterface
     */
    public function setTargetScript(string $target_script) : ilCtrlTargetInterface;

    /**
     * Returns the target script the target should aim at.
     *
     * @return string
     */
    public function getTargetScript() : string;

    /**
     * Appends a command class to the current target.
     *
     * @param string $class_name
     * @return ilCtrlTargetInterface
     */
    public function appendCmdClass(string $class_name) : ilCtrlTargetInterface;

    /**
     * Appends multiple command classes to the current target.
     *
     * Note that the class array MUST contain the whole trace inc.
     * the baseclass.
     *
     * @param array $classes
     * @return ilCtrlTargetInterface
     */
    public function appendCmdClassArray(array $classes) : ilCtrlTargetInterface;

    /**
     * Returns the current command class.
     *
     * @return string
     */
    public function getCurrentCmdClass() : string;

    /**
     * Sets the command the current command class should execute.
     *
     * @param string $cmd
     * @return ilCtrlTargetInterface
     */
    public function setCmd(string $cmd) : ilCtrlTargetInterface;

    /**
     * Returns the command which the target should be executing.
     *
     * @return string|null
     */
    public function getCmd() : ?string;

    /**
     * Sets whether the current target is executed asynchronously or not.
     *
     * @param bool $is_async
     * @return ilCtrlTargetInterface
     */
    public function setAsync(bool $is_async) : ilCtrlTargetInterface;

    /**
     * Sets whether appended link parameters should be escaped or not.
     *
     * @param bool $is_escaped
     * @return ilCtrlTargetInterface
     */
    public function setEscaped(bool $is_escaped) : ilCtrlTargetInterface;

    /**
     * Sets the current targets ilCtrl token.
     *
     * @param ilCtrlTokenInterface $token
     * @return ilCtrlTargetInterface
     */
    public function setToken(ilCtrlTokenInterface $token) : ilCtrlTargetInterface;

    /**
     * Appends an anchor to the target URL.
     *
     * @param string $anchor
     * @return ilCtrlTargetInterface
     */
    public function setAnchor(string $anchor) : ilCtrlTargetInterface;

    /**
     * Sets the current targets parameters (as key => value pairs).
     *
     * This method can be called multiple times, duplicate keys will
     * be overwritten.
     *
     * @param array $parameters
     * @return ilCtrlTargetInterface
     */
    public function setParameters(array $parameters) : ilCtrlTargetInterface;

    /**
     * Returns the URL with information currently set.
     *
     * @param bool $is_post
     * @return string|null
     */
    public function getTargetUrl(bool $is_post = false) : ?string;

    /**
     * Returns the trace of the current target.
     *
     * @return ilCtrlTraceInterface
     */
    public function getTrace() : ilCtrlTraceInterface;
}