<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlInterface
{
    /**
     * @var string command for POST that must be passed as
     *             $_GET parameter.
     */
    public const CMD_POST = 'post';

    /**
     * $_GET request parameter names, used throughout ilCtrl.
     */
    public const PARAM_CSRF_TOKEN = 'rtoken';
    public const PARAM_CID_PATH = 'cmdNode';
    public const PARAM_REDIRECT = 'redirectSource';
    public const PARAM_BASE_CLASS = 'baseClass';
    public const PARAM_CMD_CLASS = 'cmdClass';
    public const PARAM_CMD_MODE = 'cmdMode';
    public const PARAM_CMD_FALLBACK = 'fallbackCmd';
    public const PARAM_CMD = 'cmd';

    /**
     * @var string[] list of protected $_GET or $_POST parameters.
     */
    public const PROTECTED_PARAMETERS = [
        self::PARAM_BASE_CLASS,
        self::PARAM_CMD_CLASS,
        self::PARAM_CID_PATH,
        self::PARAM_CSRF_TOKEN,
    ];

    /**
     * different modes used for UI plugins (or in dev-mode).
     */
    public const CMD_MODE_PROCESS = 'execComm';
    public const CMD_MODE_ASYNC = 'asynch';
    public const CMD_MODE_HTML = 'getHtml';

    /**
     * Calls the currently provided baseclass.
     * If no baseclass is provided as an argument, the current GET
     * request MUST contain @param string|null $a_base_class
     * @throws ilCtrlException if no valid baseclass is provided.
     * @see ilCtrlInterface::PARAM_BASE_CLASS.
     */
    public function callBaseClass(string $a_base_class = null): void;

    /**
     * Forwards the request by invoking executeCommand() on the
     * given GUI object.
     *
     * If any output was generated in that method, it will be
     * returned by this method as well.
     *
     * @param object $a_gui_object
     * @return mixed
     * @throws ilCtrlException if executeCommand() cannot be invoked.
     */
    public function forwardCommand(object $a_gui_object);

    /**
     * Returns the HTML output of another GUI object by invoking
     * getHTML() with optional parameters on it.
     *
     * @param object     $a_gui_object
     * @param array|null $a_parameters
     * @return string
     * @throws ilCtrlException if getHTML() cannot be invoked.
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null): string;

    /**
     * Returns the command passed with the current POST or GET request.
     *
     * @param string|null $fallback_command
     * @return string|null
     */
    public function getCmd(string $fallback_command = null): ?string;

    /**
     * Sets the current command.
     *
     * @deprecated this method should not be used anymore and will be
     *             removed with ILIAS 10.
     *
     * @param string|null $a_cmd
     */
    public function setCmd(?string $a_cmd): void;

    /**
     * Returns the command class which should be executed next.
     *
     * @return string|null
     */
    public function getCmdClass(): ?string;

    /**
     * Sets the command class that should be executed next.
     *
     * @deprecated this method should not be used anymore and will be
     *             removed with ILIAS 10.
     *
     * @param object|string|null $a_cmd_class
     */
    public function setCmdClass($a_cmd_class): void;

    /**
     * Returns the classname of the next class in the control flow.
     *
     * @param object|string|null $a_gui_class
     * @return string|null
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function getNextClass($a_gui_class = null): ?string;

    /**
     * Sets parameters for the given object.
     *
     * @see ilCtrlInterface::saveParameterByClass()
     *
     * @param object          $a_gui_obj
     * @param string|string[] $a_parameter
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function saveParameter(object $a_gui_obj, $a_parameter): void;

    /**
     * Sets a parameter for the given GUI class that must be passed in every
     * target link generation. This means ilCtrl considers these parameters
     * whenever a link-generation method is called afterwards, and uses the
     * currently given value for these parameters for the next URL as well.
     *
     * Typical examples are ref_id or obj_id, where the constructor of a class
     * can use the statement $ilCtrl->saveParameterByClass(self::class, ['ref_id'])
     *
     * @see ilCtrlInterface::getLinkTargetByClass(), ilCtrlInterface::getFormActionByClass()
     *
     * @param string          $a_class
     * @param string|string[] $a_parameter
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function saveParameterByClass(string $a_class, $a_parameter): void;

    /**
     * Sets a parameter for the given GUI object and appends the given value.
     *
     * @see ilCtrlInterface::setParameterByClass()
     *
     * @param object $a_gui_obj
     * @param string $a_parameter
     * @param mixed  $a_value
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function setParameter(object $a_gui_obj, string $a_parameter, $a_value): void;

    /**
     * Sets a parameter for the given GUI class and appends the given value as well.
     *
     * unlike @see ilCtrlInterface::saveParameterByClass() this method uses the
     * given value for future link generation method calls, instead of using the
     * value already given.
     *
     * @param string $a_class
     * @param string $a_parameter
     * @param mixed  $a_value
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function setParameterByClass(string $a_class, string $a_parameter, $a_value): void;

    /**
     * Returns all parameters that have been saved or set for a GUI object.
     *
     * @param object $a_gui_obj
     * @return array
     * @throws ilCtrlException if the given object cannot be found.
     */
    public function getParameterArray(object $a_gui_obj): array;

    /**
     * Returns all parameters that have been saved or set for a given GUI class.
     *
     * @param string $a_class
     * @return array<string, mixed>
     * @throws ilCtrlException if the given class cannot be found.
     */
    public function getParameterArrayByClass(string $a_class): array;

    /**
     * Removes all currently set or saved parameters for the given GUI object.
     *
     * @param object $a_gui_obj
     */
    public function clearParameters(object $a_gui_obj): void;

    /**
     * Removes all currently set or saved parameters for the given GUI class.
     *
     * @param string $a_class
     */
    public function clearParametersByClass(string $a_class): void;

    /**
     * Removes a specific parameter of a specific class that is currently set or saved.
     *
     * @param string $a_class
     * @param string $a_parameter
     */
    public function clearParameterByClass(string $a_class, string $a_parameter): void;

    /**
     * Returns a link target for the given information.
     *
     * @param object      $a_gui_obj
     * @param string|null $a_cmd
     * @param string|null $a_anchor
     * @param bool        $is_async
     * @param bool        $has_xml_style
     * @return string
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function getLinkTarget(
        object $a_gui_obj,
        string $a_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string;

    /**
     * Returns a link target for the given information.
     *
     * @param string|string[] $a_class
     * @param string|null     $a_cmd
     * @param string|null     $a_anchor
     * @param bool            $is_async
     * @param bool            $has_xml_style
     * @return string
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function getLinkTargetByClass(
        $a_class,
        string $a_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string;

    /**
     * Returns a form action link for the given information.
     * @param object      $a_gui_obj
     * @param string|null $a_fallback_cmd
     * @param string|null $a_anchor
     * @param bool        $is_async
     * @param bool        $has_xml_style
     * @return string
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function getFormAction(
        object $a_gui_obj,
        string $a_fallback_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string;

    /**
     * Returns a form action link for the given information.
     *
     * @param string|string[] $a_class
     * @param string|null     $a_fallback_cmd
     * @param string|null     $a_anchor
     * @param bool            $is_async
     * @param bool            $has_xml_style
     * @return string
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function getFormActionByClass(
        $a_class,
        string $a_fallback_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string;

    /**
     * Redirects to another GUI object.
     *
     * @param object      $a_gui_obj
     * @param string|null $a_cmd
     * @param string|null $a_anchor
     * @param bool        $is_async
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function redirect(
        object $a_gui_obj,
        string $a_cmd = null,
        string $a_anchor = null,
        bool $is_async = false
    ): void;

    /**
     * Redirects to the provided GUI class.
     *
     * @param string|string[] $a_class
     * @param string|null     $a_cmd
     * @param string|null     $a_anchor
     * @param bool            $is_async
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function redirectByClass(
        $a_class,
        string $a_cmd = null,
        string $a_anchor = null,
        bool $is_async = false
    ): void;

    /**
     * Redirects to the given target URL.
     *
     * @param string $target_url
     */
    public function redirectToURL(string $target_url): void;

    /**
     * Sets the current object (id and type) of ilCtrl's context.
     *
     * @deprecated setParameterByClass, setParameter, saveParameterByClass
     *             or saveParameter should be used.
     *
     * @param int    $obj_id
     * @param string $obj_type
     */
    public function setContextObject(int $obj_id, string $obj_type): void;

    /**
     * Returns the current context's object id.
     *
     * @deprecated setParameterByClass, setParameter, saveParameterByClass
     *             or saveParameter should be used.
     *
     * @return int|null
     */
    public function getContextObjId(): ?int;

    /**
     * Returns the current context's object type.
     *
     * @deprecated setParameterByClass, setParameter, saveParameterByClass
     *             or saveParameter should be used.
     *
     * @return string|null
     */
    public function getContextObjType(): ?string;

    /**
     * Returns the descending stacktrace of ilCtrl calls that have been made.
     *
     * @return array<int, array<string, string>>
     */
    public function getCallHistory(): array;

    /**
     * Get class path that can be used in include statements
     * for a given class name.
     *
     * @param string $a_class
     * @return string
     * @throws ilCtrlException if the class cannot be found.
     */
    public function lookupClassPath(string $a_class): string;

    /**
     * Returns the effective classname for a given path.
     *
     * @deprecated if you know the classpath you most likely called
     *             lookupClassPath already, which means you already
     *             know the classname.
     *
     * @param string $a_class_path
     * @return string
     * @throws ilCtrlException in the future.
     */
    public function getClassForClasspath(string $a_class_path): string;

    /**
     * Sets the current ilCtrl target script (default ilias.php).
     *
     * @param string $a_target_script
     */
    public function setTargetScript(string $a_target_script): void;

    /**
     * Returns whether the current request is an asynchronous one.
     *
     * @return bool
     */
    public function isAsynch(): bool;

    /**
     * Sets the return command of a given GUI object.
     *
     * @param object      $a_gui_obj
     * @param string|null $a_cmd
     * @return mixed
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function setReturn(object $a_gui_obj, string $a_cmd = null): void;

    /**
     * Sets the return command of a given class.
     *
     * @param string      $a_class
     * @param string|null $a_cmd
     * @return mixed
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function setReturnByClass(string $a_class, string $a_cmd = null): void;

    /**
     * @see ilCtrlInterface::getReturnClass().
     *
     * @deprecated Used in conjunction with ilTabs->setBackTarget and
     *             ilBlockGUI->addHeaderCommand.
     *
     * @param object $a_gui_obj
     * @return string|null
     */
    public function getParentReturn(object $a_gui_obj): ?string;

    /**
     * @see ilCtrlInterface::getReturnClass().
     *
     * @deprecated @see ilCtrlInterface::getParentReturn().
     *
     * @param string $a_class
     * @return string|null
     */
    public function getParentReturnByClass(string $a_class): ?string;

    /**
     * Redirects to next parent class set with setReturn().
     *
     * @deprecated @see ilCtrlInterface::getParentReturn().
     *
     * @param object      $a_gui_obj
     * @param string|null $a_anchor
     * @throws ilCtrlException if the object was not yet provided with a return target.
     */
    public function returnToParent(object $a_gui_obj, string $a_anchor = null): void;

    /**
     * Returns the current redirect source.
     *
     * @return string|null
     */
    public function getRedirectSource(): ?string;

    /**
     * Inserts an ilCtrl call record into the database.
     *
     * @deprecated Due to the change of storing data in an artifact, this method
     *             should no longer be used. If an error is thrown at this point,
     *             it's probably solved by the correct @ilCtrl_Calls or
     *             @ilCtrl_IsCalledBy statements.
     *
     * @param object|string $a_parent
     * @param object|string $a_child
     * @param string        $a_comp_prefix
     * @throws ilCtrlException due to deprecation.
     */
    public function insertCtrlCalls($a_parent, $a_child, string $a_comp_prefix): void;

    /**
     * Check if current CID trace contains a certain gui class.
     *
     * @param string $gui_class
     * @return bool
     * @throws ilCtrlException
     */
    public function checkCurrentPathForClass(string $gui_class): bool;

    /**
     * Get current class path as array of class file names.
     *
     * @return array
     */
    public function getCurrentClassPath(): array;
}
