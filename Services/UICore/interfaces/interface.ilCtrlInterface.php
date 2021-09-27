<?php

use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlInterface
{
    /**
     * different modes used for UI plugins (or in dev-mode).
     */
    public const CMD_MODE_PROCESS = 'execComm';
    public const CMD_MODE_HTML    = 'getHtml';
    public const CMD_MODE_ASYNC   = 'asynch';

    /**
     * Initializes ilCtrl with a new baseclass.
     *
     * Note that this means, the URL information will be removed
     * and only the given baseclass is appended for future link
     * target generations from this point on.
     *
     * @param string $a_base_class
     * @throws ilCtrlException if the baseclass is unknown.
     */
    public function initBaseClass(string $a_base_class) : void;

    /**
     * Calls the currently provided baseclass.
     *
     * This method cannot be called until either:
     *
     *      a) @see ilCtrlInterface::initBaseClass() is called, or
     *      b) @see ilCtrlInterface::PARAM_BASE_CLASS is provided as $_GET parameter.
     *
     * @throws ilCtrlException if neither of those options are true OR
     *                         the current baseclass was not yet read by
     *                         the @see ilCtrlStructureReader.
     */
    public function callBaseClass() : void;

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
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string;

    /**
     * Returns the command passed with the current POST or GET request.
     *
     * Note that $safe_commands will need no CSRF token validation, all
     * other commands have to check-pass the validation.
     *
     * The optional command handler is used for exceptional cases within
     * some sort of transitioning phase, because $_POST and $_GET
     * manipulations were made within this method. Since they will be
     * prohibited in the future, a handler allows to still take advantage
     * of that.
     *
     * @param string|null               $fallback_command
     * @return string|null
     */
    public function getCmd(string $fallback_command = null) : ?string;

    /**
     * Sets the current command.
     *
     * @depracated this method should not be used anymore, as all commands
     *             should be passed as $_GET or $_POST parameters.
     *
     * @param string $a_cmd
     */
    public function setCmd(string $a_cmd) : void;

    /**
     * Returns the command class which should be executed next.
     *
     * @return string|null
     */
    public function getCmdClass() : ?string;

    /**
     * Sets the command class that should be executed next.
     *
     * @deprecated this method should not be used anymore, as all command
     *             classes should be passed by $_GET or $_POST parameters.
     *
     * @param object|string $a_cmd_class
     */
    public function setCmdClass($a_cmd_class) : void;

    /**
     * Returns the classname of the next class in the control flow.
     *
     * @param object|string|null $a_gui_class
     * @return string|null
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function getNextClass($a_gui_class = null) : ?string;

    /**
     * Sets parameters for the given object.
     *
     * @see ilCtrlInterface::saveParameterByClass()
     *
     * @param object          $a_gui_obj
     * @param string|string[] $a_parameter
     * @throws ilCtrlException if an invalid parameter name is given.
     */
    public function saveParameter(object $a_gui_obj, $a_parameter) : void;

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
    public function saveParameterByClass(string $a_class, $a_parameter) : void;

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
    public function setParameter(object $a_gui_obj, string $a_parameter, mixed $a_value) : void;

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
    public function setParameterByClass(string $a_class, string $a_parameter, mixed $a_value) : void;

    /**
     * Returns all parameters that have been saved or set for a GUI object.
     *
     * @param object $a_gui_obj
     * @return array
     * @throws ilCtrlException if the given object cannot be found.
     */
    public function getParameterArray(object $a_gui_obj) : array;

    /**
     * Returns all parameters that have been saved or set for a given GUI class.
     *
     * @param string      $a_class
     * @param string|null $a_cmd
     * @return array
     * @throws ilCtrlException if the given class cannot be found.
     */
    public function getParameterArrayByClass(string $a_class, string $a_cmd = null) : array;

    /**
     * Removes all currently set or saved parameters for the given GUI object.
     *
     * @param object $a_gui_obj
     */
    public function clearParameters(object $a_gui_obj) : void;

    /**
     * Removes all currently set or saved parameters for the given GUI class.
     *
     * @param string $a_class
     */
    public function clearParametersByClass(string $a_class) : void;

    /**
     * Removes a specific parameter of a specific class that is currently set or saved.
     *
     * @param string $a_class
     * @param string $a_parameter
     */
    public function clearParameterByClass(string $a_class, string $a_parameter) : void;

    /**
     * Returns a link target for the given information.
     *
     * @param object $a_gui_obj
     * @param string $a_cmd
     * @param string $a_anchor
     * @param bool   $is_async
     * @param bool   $has_xml_style
     * @return string
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function getLinkTarget(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string;

    /**
     * Returns a link target for the given information.
     *
     * @param string|string[] $a_class
     * @param string          $a_cmd
     * @param string          $a_anchor
     * @param bool            $is_async
     * @param bool            $has_xml_style
     * @return string
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function getLinkTargetByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string;

    /**
     * Returns a form action link for the given information.
     *
     * @param object $a_gui_obj
     * @param string $a_fallback_cmd
     * @param string $a_anchor
     * @param bool   $is_async
     * @param bool   $has_xml_style
     * @return string
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function getFormAction(
        object $a_gui_obj,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string;

    /**
     * Returns a form action link for the given information.
     *
     * @param string|string[] $a_class
     * @param string          $a_fallback_cmd
     * @param string          $a_anchor
     * @param bool            $is_async
     * @param bool            $has_xml_style
     * @return string
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function getFormActionByClass(
        $a_class,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string;

    /**
     * Redirects to another GUI object.
     *
     * @param object $a_gui_obj
     * @param string $a_cmd
     * @param string $a_anchor
     * @param bool   $is_async
     * @throws ilCtrlException if the provided class cannot be found.
     */
    public function redirect(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false
    ) : void;

    /**
     * Redirects to the provided GUI class.
     * @param string|string[] $a_class
     * @param string          $a_cmd
     * @param string          $a_anchor
     * @param bool            $is_async
     * @throws ilCtrlException if a provided class cannot be found.
     */
    public function redirectByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false
    ) : void;

    /**
     * Redirects to the given target URL.
     *
     * @param string $target_url
     */
    public function redirectToURL(string $target_url) : void;

    /**
     * Sets the current ilCtrl context. A context consist of an obj-id and -type
     * with possible sub-id and -type.
     *
     * @param ilCtrlContext $context
     */
    public function setContext(ilCtrlContext $context) : void;

    /**
     * Returns the current ilCtrl context.
     *
     * @return ilCtrlContext
     */
    public function getContext() : ilCtrlContext;

    /**
     * Get context object id
     *
     * @deprecated use getContext() instead.
     *
     * @return int|null
     */
    public function getContextObjId() : ?int;

    /**
     * Get context object type
     *
     * @deprecated use getContext() instead.
     *
     * @return string|null
     */
    public function getContextObjType() : ?string;

    /**
     * Get context subobject id
     *
     * @deprecated use getContext() instead.
     *
     * @return int|null
     */
    public function getContextSubObjId() : ?int;

    /**
     * Get context subobject type
     *
     * @deprecated use getContext() instead.
     *
     * @return string|null
     */
    public function getContextSubObjType() : ?string;

    /**
     * Returns the descending stacktrace of ilCtrl calls that have been made.
     *
     * @return array<int, string>
     */
    public function getCallHistory() : array;

    /**
     * Get class path that can be used in include statements
     * for a given class name.
     *
     * @param string $a_class
     * @return string
     * @throws ilCtrlException if the class cannot be found.
     */
    public function lookupClassPath(string $a_class) : string;

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
    public function getClassForClasspath(string $a_class_path) : string;

    /**
     * Sets the current ilCtrl target script (default ilias.php).
     *
     * @param string $a_target_script
     */
    public function setTargetScript(string $a_target_script) : void;

    /**
     * Returns whether the current request is an asynchronous one.
     *
     * @return bool
     */
    public function isAsynch() : bool;

    /**
     * Sets the return command of a given GUI object.
     *
     * @param object $a_gui_obj
     * @param string $a_cmd
     * @return mixed
     */
    public function setReturn(object $a_gui_obj, string $a_cmd) : void;

    /**
     * Sets the return command of a given class.
     *
     * @param string $a_class
     * @param string $a_cmd
     * @return mixed
     */
    public function setReturnByClass(string $a_class, string $a_cmd) : void;

    /**
     * Redirects to next parent class set with setReturn().
     *
     * @param object      $a_gui_obj
     * @param string|null $a_anchor
     * @return mixed
     */
    public function returnToParent(object $a_gui_obj, string $a_anchor = null) : void;

    /**
     * @see ilCtrlInterface::getReturnClass().
     *
     * @deprecated Used in conjunction with ilTabs->setBackTarget and
     *             ilBlockGUI->addHeaderCommand.
     *
     * @param object $a_gui_obj
     * @return string|bool
     * @throws ilCtrlException if the cid trace cannot be generated.
     */
    public function getParentReturn(object $a_gui_obj);

    /**
     * @see ilCtrlInterface::getReturnClass().
     *
     * @deprecated
     *
     * @param string $a_class
     * @return string|bool
     * @throws ilCtrlException if the cid trace cannot be generated.
     */
    public function getParentReturnByClass(string $a_class);

    /**
     * Returns the name of the current return-class.
     *
     * @deprecated This method is only used by COPage/ilPCParagraphGUI and
     *             COPage/ilPCPlaceHolderGUI. As these are edge-cases this
     *             method should not be used anymore.
     *
     * @param string|object $a_class
     * @return string|bool
     * @throws ilCtrlException if the cid trace cannot be generated.
     */
    public function getReturnClass($a_class);

    /**
     * Returns the current redirect source.
     *
     * @return string|null
     */
    public function getRedirectSource() : ?string;

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
    public function insertCtrlCalls($a_parent, $a_child, string $a_comp_prefix) : void;

    /**
     * Check if current CID trace contains a certain gui class.
     *
     * @param string $gui_class
     * @return bool
     * @throws ilCtrlException
     */
    public function checkCurrentPathForClass(string $gui_class) : bool;

    /**
     * Get current class path as array of class file names.
     *
     * @return array
     */
    public function getCurrentClassPath() : array;
}
