<?php

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
    public function getHTML($a_gui_object, array $a_parameters = null, array $class_path = []);

    /**
     * Set context of current user interface. A context is a ILIAS repository
     * object (obj ID + obj type) with an additional optional subobject (ID + Type)
     * @param integer        object ID
     * @param string        object type
     * @param integer        subobject ID
     * @param string        subobject type
     */
    public function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "");

    /**
     * Get context object id
     * @return    int        object id
     */
    public function getContextObjId();

    /**
     * Get context object type
     * @return    string        object type
     */
    public function getContextObjType();

    /**
     * Get context subobject id
     * @return    int        subobject id
     */
    public function getContextSubObjId();

    /**
     * Get context subobject type
     * @return    string        subobject type
     */
    public function getContextSubObjType();

    /**
     * Check whether target is valid
     * @param
     * @return
     */
    public function checkTargetClass($a_class);

    /**
     * Get command target node
     * @return    string        id of current command target node
     */
    public function getCmdNode() : string;

    /**
     * Add a tab to tabs array (@param string $a_lang_var language variable
     * @param string $a_link  link
     * @param string $a_cmd   command (must be same as in link)
     * @param string $a_class command class (must be same as in link)
     * @deprecated use $ilTabs)
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class);

    /**
     * Get tabs array        (@return    array        array of tab entries (array("lang_var", "link", "cmd", "class))
     * @deprecated, use $ilTabs)
     */
    public function getTabs();

    /**
     * Get controller call history.
     * This is used for the developer mode and presented in the footer
     * @return    array        array of call history entries
     */
    public function getCallHistory();

    /**
     * Get call structure of class context. This method must be called
     * for the top level gui class in the leading php script. It must be
     * called before the the current command is forwarded to the top level
     * gui class. Example:
     *    $ilCtrl->getCallStructure("ilrepositorygui");
     *    $repository_gui = new ilRepositoryGUI();
     *    $ilCtrl->forwardCommand($repository_gui);
     * @param string $a_class gui class name
     * @access    public
     */
    public function getCallStructure($a_class);

    /**
     * Reads call structure from db
     */
    public function readCallStructure($a_class, $a_nr = 0, $a_parent = 0);

    /**
     * Set parameters that should be passed in every form and link of a
     * gui class. All links that relate to the specified gui object class and
     * are build e.g. by using getLinkTarger() or getFormAction() will include
     * this parameter. This is the mechanism to add url parameters to the standard
     * url target everytime.
     * A typical example is the "ref_id" that should be included in almost every
     * link or form action url. So the constructor of ilRepositoryGUI includes
     * the command:
     *    $this->ctrl->saveParameter($this, array("ref_id"));
     * @param object $a_obj               gui object that will process the parameter
     * @param mixed  $a_parameter         parameter name (string) or array of parameter
     *                                    names
     * @access    public
     */
    public function saveParameter($a_obj, $a_parameter);

    /**
     * Save parameter for a class
     * @param string    class name
     * @param string    parameter name
     */
    public function saveParameterByClass($a_class, $a_parameter);

    /**
     * Set parameters that should be passed a form and link of a
     * gui class. All links that relate to the specified gui object class and
     * are build e.g. by using getLinkTarger() or getFormAction() will include
     * this parameter. This is the mechanism to add url parameters to the standard
     * url target. The difference to the saveParameter() method is, that setParameter()
     * does not simply forward the url parameter of the last request. You can set
     * a spefific value.
     * If this parameter is also a "saved parameter" (set by saveParameter() method)
     * the saved value will be overwritten.
     * The method is usually used in conjunction with a getFormAction() or getLinkTarget()
     * call. E.g.:
     *        $this->ctrl->setParameter($this, "obj_id", $data_row["obj_id"]);
     *        $obj_link = $this->ctrl->getLinkTarget($this, "view");
     * @param object $a_obj       gui object
     * @param string $a_parameter parameter name
     * @param string $a_parameter parameter value
     */
    public function setParameter($a_obj, $a_parameter, $a_value);

    /**
     * Same as setParameterByClass, except that a class name is passed.
     * @param string $a_class     gui class name
     * @param string $a_parameter parameter name
     * @param string $a_parameter parameter value
     */
    public function setParameterByClass($a_class, $a_parameter, $a_value);

    /**
     * Same as setParameterByClass, except that a class name is passed.
     * @param string $a_class     gui class name
     * @param string $a_parameter parameter name
     * @param string $a_parameter parameter value
     */
    public function clearParameterByClass($a_class, $a_parameter);

    /**
     * Clears all parameters that have been set via setParameter for
     * a GUI class.
     * @param object $a_obj gui object
     */
    public function clearParameters($a_obj);

    /**
     * Clears all parameters that have been set via setParameter for
     * a GUI class.
     * @param string $a_class gui class name
     */
    public function clearParametersByClass($a_class);

    /**
     * Get next class in the control path from the current class
     * to the target command class. This is the class that should
     * be instantiated and be invoked via $ilCtrl->forwardCommand($class)
     * next.
     * @return    string        class name of next class
     */
    public function getNextClass($a_gui_class = null);

    /**
     * Get class path that can be used in include statements
     * for a given class name.
     * @param string $a_class_name class name
     */
    public function lookupClassPath($a_class_name);

    /**
     * this method assumes that the class path has the format "dir/class.<class_name>.php"
     * @param string $a_class_path class path
     * @access    public
     * @return    string        class name
     */
    public function getClassForClasspath($a_class_path);

    /**
     * set target script name
     * @param string $a_target_script target script name
     */
    public function setTargetScript(string $a_target_script);

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
    public function initBaseClass($a_base_class);

    /**
     * Determines current get/post command
     * @param string        default command
     * @param array        safe commands: for these commands no token
     *                        is checked for post requests
     */
    public function getCmd(string $fallback_command = "", array $safe_commands = []) : string;

    /**
     * Set the current command
     * IMPORTANT NOTE:
     * please use this function only in exceptional cases
     * it is not intended for setting commands in forms or links!
     * use the corresponding parameters of getFormAction() and
     * getLinkTarget() instead.
     */
    public function setCmd($a_cmd);

    /**
     * Set the current command class
     * IMPORTANT NOTE:
     * please use this function only in exceptional cases
     * it is not intended for setting the command class in forms or links!
     * use the corresponding parameters of getFormAction() and
     * getLinkTarget() instead.
     */
    public function setCmdClass($a_cmd_class);

    /**
     * Determines class that should execute the current command
     * @return    string        class name
     */
    public function getCmdClass();

    /**
     * Get form action url for gui class object
     * @param object        gui object
     * @param string        fallback command
     * @param string        anchor
     * @param bool        asynchronous call
     * @param bool        xml style t/f
     * @return    string        script url
     */
    public function getFormAction(
        $a_gui_obj,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    );

    /**
     * Get form action url for gui class name
     * @param string        gui class name
     * @param string        fallback command
     * @param string        anchor
     * @param bool        asynchronous call
     * @param bool        xml style t/f
     * @return    string        script url
     */
    public function getFormActionByClass(
        $a_class,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    );

    /**
     * Append request token as url parameter
     * @param string    url
     * @param boolean    xml style
     */
    public function appendRequestTokenParameterString($a_url, $xml_style = false);

    /**
     * Get request token.
     * @return    string        request token for user and session
     */
    public function getRequestToken();

    /**
     * Redirect to another command
     * @param object        gui object
     * @param string        command
     * @param string        anchor
     */
    public function redirect($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false);

    /**
     * @param $a_script
     */
    public function redirectToURL($a_script);

    /**
     * Redirect to other gui class using class name
     * @param string        command target class
     * @param string        command
     */
    public function redirectByClass($a_class, $a_cmd = "", $a_anchor = "", $a_asynch = false);

    /**
     * Is current command an asynchronous command?
     * @return    boolean        asynchronous t/f
     */
    public function isAsynch();

    /**
     * Get link target for command using gui object
     * @param object        gui object (usually $this)
     * @param string        command
     * @param string        # anchor
     * @param boolean        asynchronous mode
     * @param boolean        xml style t/f
     * @return    string        target link
     */
    public function getLinkTarget($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false, $xml_style = false);

    /**
     * Get link target for command using gui class name
     * @param string/array        command target class
     * @param string        command
     * @param string        # anchor
     * @param boolean        asynchronous mode
     * @param boolean        xml style t/f
     * @return    string        target link
     */
    public function getLinkTargetByClass($a_class, $a_cmd = "", $a_anchor = "", $a_asynch = false, $xml_style = false) : string;

    /**
     * Set return command
     */
    public function setReturn($a_gui_obj, $a_cmd);

    /**
     * Set return command
     */
    public function setReturnByClass($a_class, $a_cmd);

    /**
     * Redirects to next parent class that used setReturn.
     */
    public function returnToParent($a_gui_obj, $a_anchor = "");

    /**
     * Get return script url.
     * Used in conjunction with ilTabs->setBackTarget and ilBlockGUI->addHeaderCommand.
     */
    public function getParentReturn($a_gui_obj);

    /**
     * Get return script url
     * Only used in getParentReturn.
     */
    public function getParentReturnByClass($a_class);

    /**
     * Get return class.
     * Only used in COPage/ilPCParagraphGUI and COPage/ilPCPlaceHolderGUI
     * @param string|object $class
     * @return    string|bool
     */
    public function getReturnClass($a_class);

    /**
     * Get current redirect source
     * @return    string        redirect source class
     */
    public function getRedirectSource();

    /**
     * Get URL parameters for a class and append them to a string
     * @param        $a_class
     * @param        $a_str
     * @param string $a_cmd command
     * @param bool   $xml_style
     * @return string
     */
    public function getUrlParameters($a_class, $a_str, $a_cmd = "", $xml_style = false);

    /**
     * Get all set/save parameters for a gui object
     */
    public function getParameterArray($a_gui_obj, $a_cmd = "");

    /**
     * Get all set/save parameters using gui class name
     * @param string        class name
     * @param string        cmd
     * $ @return    array        parameter array
     */
    public function getParameterArrayByClass($classes, $a_cmd = "");

    /**
     * Insert ctrl calls record
     * @param
     * @return
     */
    public function insertCtrlCalls($a_parent, $a_child, $a_comp_prefix);

    /**
     * Check if current path contains a certain gui class
     * @param $gui_class
     * @return bool
     * @throws ilCtrlException
     */
    public function checkCurrentPathForClass($gui_class);

    /**
     * Get current class path as array of class file names
     * @return array
     * @throws ilCtrlException
     */
    public function getCurrentClassPath() : array;
}
