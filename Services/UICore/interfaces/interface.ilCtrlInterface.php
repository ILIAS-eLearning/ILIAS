<?php

use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * This class provides processing control methods.
 * A global instance is available via variable $ilCtrl
 * xml_style parameters: This mode was activated per default in the past, is now set to false but still being
 * used and needed, if link information is passed to the xslt processing e.g. in content pages.
 * @author Alex Killing <alex.killing@gmx.de>
 */
interface ilCtrlInterface
{
    /**
     * Calls base class of current request. The base class is
     * passed via $_GET["baseClass"] and is the first class in
     * the call sequence of the request. Do not call this method
     * within other scripts than ilias.php.
     * @throws ilCtrlException
     */
    public function callBaseClass() : void;

    /**
     * get directory of current module
     * @return mixed
     * @throws Exception
     * @deprecated
     */
    public function getModuleDir();

    /**
     * Forward flow of control to next gui class
     * this invokes the executeCommand() method of the
     * gui object that is passed via reference
     * @param object $a_gui_object gui object that should receive
     * @return mixed return data of invoked executeCommand() method
     * @throws ilCtrlException
     */
    public function forwardCommand(object $a_gui_object);

    /**
     * Gets an HTML output from another GUI class and
     * returns the flow of control to the calling class.
     * @param object     $a_gui_object GUI class that implements getHTML() method to return its HTML
     * @param array|null $a_parameters parameter array
     * @return string
     * @throws ilCtrlException
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string;

    /**
     * Set context of current user interface. A context is a ILIAS repository
     * object (obj ID + obj type) with an additional optional subobject (ID + Type)
     *
     * @param int         $a_obj_id
     * @param string      $a_obj_type
     * @param int         $a_sub_obj_id
     * @param string|null $a_sub_obj_type
     */
    public function setContext(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id = 0,
        string $a_sub_obj_type = null
    ) : void;

    /**
     * Get context object id
     *
     * @return int|null
     */
    public function getContextObjId() : ?int;

    /**
     * Get context object type
     *
     * @return string|null
     */
    public function getContextObjType() : ?string;

    /**
     * Get context subobject id
     *
     * @return int|null
     */
    public function getContextSubObjId() : ?int;

    /**
     * Get context subobject type
     *
     * @return string|null
     */
    public function getContextSubObjType() : ?string;

    /**
     * Check whether target is valid
     *
     * @deprecated
     *
     * @param string|object $a_class
     * @return bool
     */
    public function checkTargetClass($a_class) : bool;

    /**
     * Get command target node
     * @return    string        id of current command target node
     */
    public function getCmdNode() : string;

    /**
     * Add a tab to tabs array
     *
     * @deprecated use $ilTabs
     *
     * @param string $a_lang_var language variable
     * @param string $a_link  link
     * @param string $a_cmd   command (must be same as in link)
     * @param string $a_class command class (must be same as in link)
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class);

    /**
     * Get tabs array
     *
     * @deprecated use $ilTabs
     *
     * @return array array("lang_var", "link", "cmd", "class)
     */
    public function getTabs();

    /**
     * Returns the descending stacktrace of ilCtrl calls that have been made.
     *
     * @return array<int, string>
     */
    public function getCallHistory() : array;

    /**
     * Sets parameters for the given object.
     *
     * @see ilCtrlInterface::saveParameterByClass()
     *
     * @param object          $a_obj
     * @param string|string[] $a_parameter
     */
    public function saveParameter(object $a_obj, $a_parameter) : void;

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
     */
    public function saveParameterByClass(string $a_class, $a_parameter) : void;

    /**
     * Sets a parameter for the given GUI object and appends the given value.
     *
     * @see ilCtrlInterface::setParameterByClass()
     *
     * @param object $a_obj
     * @param string $a_parameter
     * @param mixed  $a_value
     * @return mixed
     */
    public function setParameter(object $a_obj, string $a_parameter, $a_value) : void;

    /**
     * Sets a parameter for the given GUI class and appends the given value as well.
     * unlike @see ilCtrlInterface::saveParameterByClass() this method uses the
     * given value for future link generation method calls, instead of using the
     * value already given.
     *
     * @param string $a_class
     * @param string $a_parameter
     * @param mixed  $a_value
     * @return mixed
     */
    public function setParameterByClass(string $a_class, string $a_parameter, $a_value);

    /**
     * Removes all currently set or saved parameters for the given GUI object.
     *
     * @param object $a_obj
     */
    public function clearParameters(object $a_obj) : void;

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
     * Get next class in the control path from the current class
     * to the target command class. This is the class that should
     * be instantiated and be invoked via $ilCtrl->forwardCommand($class)
     * next.
     * @param object|string $a_gui_class
     * @return string|bool class name of next class or false on failure.
     */
    public function getNextClass($a_gui_class = null);

    /**
     * Get class path that can be used in include statements
     * for a given class name.
     *
     * @param string $a_class
     * @return string
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
     */
    public function getClassForClasspath(string $a_class_path) : string;

    /**
     * set target script name
     * @param string $a_target_script target script name
     */
    public function setTargetScript(string $a_target_script) : void;

    /**
     * Get target script name
     * @return    string        target script name
     */
    public function getTargetScript() : string;

    /**
     * Initialises new base class
     * Note: this resets the whole current ilCtrl context completely.
     * You can call callBaseClass() after that.
     * @param string        base class name
     */
    public function initBaseClass(string $a_base_class) : void;

    /**
     * Returns the command passed with the current POST or GET request.
     *
     * Note that $safe_commands will need no CSRF token validation.
     *
     * @param string                    $fallback_command
     * @param array                     $safe_commands
     * @param ilCtrlCommandHandler|null $handler
     * @return string
     */
    public function getCmd(string $fallback_command = "", array $safe_commands = [], ilCtrlCommandHandler $handler = null) : string;

    /**
     * Set the current command
     *
     * @TODO: argue why it should not be used.
     *
     * @deprecated
     *
     * @param string $a_cmd
     */
    public function setCmd(string $a_cmd) : void;

    /**
     * Sets the current command class
     *
     * @TODO: argue why it should not be used.
     *
     * @deprecated
     *
     * @param object|string $a_cmd_class
     */
    public function setCmdClass($a_cmd_class) : void;

    /**
     * Returns the command class which should be executed next.
     *
     * @return string
     */
    public function getCmdClass() : string;

    /**
     * Returns a form action link for the given information.
     *
     * @param object $a_gui_object
     * @param string $a_fallback_cmd
     * @param string $a_anchor
     * @param bool   $a_asynch
     * @param bool   $xml_style
     * @return string
     */
    public function getFormAction(
        object $a_gui_object,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string;

    /**
     * Returns a form action link for the given information.
     *
     * @param string|string[] $a_class
     * @param string          $a_fallback_cmd
     * @param string          $a_anchor
     * @param bool            $a_asynch
     * @param bool            $xml_style
     * @return string
     */
    public function getFormActionByClass(
        $a_class,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string;

    /**
     * Returns the given URL appended with a CSRF token.
     *
     * @param string $a_url
     * @param bool   $xml_style
     * @return string
     */
    public function appendRequestTokenParameterString(string $a_url, bool $xml_style = false) : string;

    /**
     * Returns a new or stored unique CSRF token.
     *
     * @return string
     */
    public function getRequestToken() : string;

    /**
     * Validates a CSRF token from the given (POST or GET) request.
     *
     * @param RequestWrapper $request
     * @return bool
     * @throws ilDateTimeException
     * @throws ilException
     */
    public function verifyToken(RequestWrapper $request) : bool;

    /**
     * Redirects to another GUI object.
     *
     * @param object $a_gui_obj
     * @param string $a_cmd
     * @param string $a_anchor
     * @param bool   $a_asynch
     */
    public function redirect(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false
    ) : void;

    /**
     * Redirects to an URL.
     *
     * @param string $a_url
     */
    public function redirectToURL(string $a_url) : void;

    /**
     * Redirects to the provided GUI class.
     *
     * @param string|string[] $a_class
     * @param string          $a_cmd
     * @param string          $a_anchor
     * @param bool            $a_asynch
     */
    public function redirectByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false
    ) : void;

    /**
     * Returns whether the current request is an asynchronous one.
     *
     * @TODO: rename to isAsync() one day.
     *
     * @return bool
     */
    public function isAsynch() : bool;

    /**
     * Returns a link target for the given information.
     *
     * @param object $a_gui_obj
     * @param string $a_cmd
     * @param string $a_anchor
     * @param bool   $a_asynch
     * @param bool   $xml_style
     * @return string
     */
    public function getLinkTarget(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string;

    /**
     * Returns a link target for the given information.
     *
     * @param string|string[] $a_class
     * @param string          $a_cmd
     * @param string          $a_anchor
     * @param bool            $a_asynch
     * @param bool            $xml_style
     * @return string
     */
    public function getLinkTargetByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string;

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
     * @return string
     */
    public function getRedirectSource() : string;

    /**
     * Get URL parameters for a class and append them to a string.
     *
     * @param string|string[] $a_classes
     * @param string          $a_str
     * @param string|null     $a_cmd
     * @param bool            $xml_style
     * @return string
     */
    public function getUrlParameters($a_classes, string $a_str, string $a_cmd = null, bool $xml_style = false) : string;

    /**
     * Returns all parameters that have been saved or set for a GUI object.
     *
     * @param object $a_gui_obj
     * @param null   $a_cmd
     * @return array
     */
    public function getParameterArray(object $a_gui_obj, $a_cmd = null) : array;

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
     * Check if current path contains a certain gui class
     *
     * @param string $gui_class
     * @return bool
     * @throws ilCtrlException
     */
    public function checkCurrentPathForClass(string $gui_class) : bool;

    /**
     * Get current class path as array of class file names
     *
     * @return array
     * @throws ilCtrlException
     */
    public function getCurrentClassPath() : array;
}
