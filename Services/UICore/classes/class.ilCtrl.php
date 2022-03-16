<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('class.ilCachedCtrl.php');

/**
 * This class provides processing control methods.
 * A global instance is available via variable $ilCtrl
 *
 * xml_style parameters: This mode was activated per default in the past, is now set to false but still being
 * used and needed, if link information is passed to the xslt processing e.g. in content pages.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilCtrl
{
    const IL_RTOKEN_NAME = 'rtoken';
    
    /**
     * Maps lowercase class names to lists of parameter names that saved for them.
     *
     * See saveParameter/setParameter for difference to save_parameter.
     *
     * This is used in: saveParameter, saveParameterByClass, getParameterArrayByClass
     *
     * @var	array<string, array<string, mixed>>
     */
    protected $save_parameter;

    /**
     * Maps lowercase class names to lists of parameters set for them.
     *
     * See saveParameter/setParameter for difference to save_parameter.
     *
     * This is used in: setParameter, setParameterByClass, getParameterArrayByClass
     *
     * @var	array<string, mixed[]>
     */
    protected $parameter;

    /**
     * Return commands per class.
     *
     * Return command sare defined by an upper context classes. If a subcontext calls
     * returnToParent() it will redirect to the return command of the next upper context that defined
     * a return command.
     *
     * This is used in: setReturn, setReturnByClass, getParentReturnByClass, searchReturnClass
     *
     * @var	array<string, string>
     */
    protected $return;

    /**
     * Stores the order in which different GUI classes were called.
     *
     * TODO: Might better be called call_stack.
     *
     * This is used in: forwardCommand, getHTML, getCallHistory
     */
    protected $call_hist = array();	// calling history

    /**
     * Stores which class calls which other class.
     *
     * This is used in: getNodeIdForTargetClass, fetchCallsOfClassFromCache, callOfClassNotKnown
     */
    protected $calls = array();

    /**
     * Request token, prevents XSS.
     *
     * This is used in: getRequestToken
     *
     * @var string
     */
    protected $rtoken = false;

    /**
     * Base script for link targets, reloads and so on
     * @var string
     */
    protected $target_script = "ilias.php";

    /**
     * @var string
     */
    protected $module_dir;

    /**
     * Flags nested getHTML processing from other nodes
     *
     * @var bool
     */
    protected $use_current_to_determine_next = false;

    /**
     * Current base class for nested getHTML processing
     *
     * @var string
     */
    protected $inner_base_class = "";

    /**
     * control class constructor
     */
    public function __construct()
    {
        $this->initializeMemberVariables();

        // this information should go to xml files one day
        $this->stored_trees = array("ilrepositorygui", "ildashboardgui",
            "illmpresentationgui", "illmeditorgui",
            "iladministrationgui");
    }
    
    /**
     * Initialize member variables.
     *
     * This is used in __construct and initBaseClass.
     */
    protected function initializeMemberVariables()
    {
        $this->save_parameter = array();
        $this->parameter = array();			// save parameter array
        $this->return = array();			// return commmands
        $this->tab = array();
        $this->current_node = 0;
        $this->call_node = array();
        $this->root_class = "";
    }

    /**
     * Calls base class of current request. The base class is
     * passed via $_GET["baseClass"] and is the first class in
     * the call sequence of the request. Do not call this method
     * within other scripts than ilias.php.
     * @throws ilCtrlException
     */
    public function callBaseClass()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $baseClass = strtolower($_GET["baseClass"]);

        $module_class = ilCachedCtrl::getInstance();
        $mc_rec = $module_class->lookupModuleClass($baseClass);

        $module = $mc_rec["module"];
        $class = $mc_rec["class"];
        $class_dir = $mc_rec["dir"];
        
        if ($module != "") {
            $m_set = $ilDB->query("SELECT * FROM il_component WHERE name = " .
                $ilDB->quote($module, "text"));
            $m_rec = $ilDB->fetchAssoc($m_set);
        } else {		// check whether class belongs to a service
            $mc_rec = $module_class->lookupServiceClass($baseClass);

            $service = $mc_rec["service"];
            $class = $mc_rec["class"];
            $class_dir = $mc_rec["dir"];
            
            if ($service == "") {
                include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
                throw new ilCtrlException("Could not find entry in modules.xml or services.xml for " .
                    $baseClass . " <br/>" . str_replace("&", "<br />&", htmlentities($_SERVER["REQUEST_URI"])));
            }

            $m_rec = ilComponent::getComponentInfo('Services', $service);
        }
        
        // forward processing to base class
        $this->getCallStructure(strtolower($baseClass));
        $base_class_gui = new $class();
        $this->forwardCommand($base_class_gui);
    }

    /**
     * get directory of current module
     * @deprecated
     * @return mixed
     * @throws Exception
     */
    public function getModuleDir()
    {
        throw new Exception("ilCtrl::getModuleDir is deprecated.");
        //return $this->module_dir;
    }
    
    /**
     * Forward flow of control to next gui class
     * this invokes the executeCommand() method of the
     * gui object that is passed via reference
     *
     * @param object $a_gui_object gui object that should receive
     * @return mixed return data of invoked executeCommand() method
     * @throws ilCtrlException
     */
    public function forwardCommand($a_gui_object)
    {
        $class = strtolower(get_class($a_gui_object));
        $nr = $this->getNodeIdForTargetClass($this->current_node, $class);
        $nr = $nr["node_id"];
        if ($nr != "") {
            $current_node = $this->current_node;
            
            $this->current_node = $nr;

            // always populate the call history
            // it will only be displayed in DEVMODE but is needed for UI plugins, too
            $this->call_hist[] = array("class" => get_class($a_gui_object),
                    "mode" => "execComm", "cmd" => $this->getCmd());

            $html = $a_gui_object->executeCommand();
            
            // reset current node
            $this->current_node = $current_node;
            
            return $html;
        }
        
        include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
        throw new ilCtrlException("ERROR: Can't forward to class $class.");
    }

    /**
     * Gets an HTML output from another GUI class and
     * returns the flow of control to the calling class.
     *
     * @param object $a_gui_object GUI class that implements getHTML() method to return its HTML
     * @param array|null $a_parameters parameter array
     * @return string
     * @throws ilCtrlException
     */
    public function getHTML($a_gui_object, array $a_parameters = null, array $class_path = [])
    {
        $class = strtolower(get_class($a_gui_object));

        if (count($class_path) > 0) {
            $class_path = array_merge($class_path, [$class]);
            $p = $this->getParameterArrayByClass($class_path);
            $nr = $p["cmdNode"];
            $baseclass = $p["baseClass"];
            $this->inner_base_class = $p["cmdNode"];
        } else {
            $nr = $this->getNodeIdForTargetClass($this->current_node, $class);
            $nr = $nr["node_id"];
        }
        if ($nr != "") {
            $current_inner_base_class = $this->inner_base_class;
            $this->use_current_to_determine_next = true;

            if ($baseclass != $_GET["baseClass"]) {
                $this->inner_base_class = $baseclass;
            }
            $current_node = $this->current_node;
            
            // set current node to new gui class
            $this->current_node = $nr;
            
            // always populate the call history
            // it will only be displayed in DEVMODE but is needed for UI plugins, too
            $this->call_hist[] = array("class" => get_class($a_gui_object),
                    "mode" => "getHtml", "cmd" => $this->getCmd());
            
            // get block
            if ($a_parameters != null) {
                $html = $a_gui_object->getHTML($a_parameters);
            } else {
                $html = $a_gui_object->getHTML();
            }
            
            // reset current node
            $this->current_node = $current_node;
            $this->inner_base_class = $current_inner_base_class;
            $this->use_current_to_determine_next = false;
            
            // return block
            return $html;
        }

        include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
        throw new ilCtrlException("ERROR: Can't getHTML from class $class.");
    }
    
    /**
     * Set context of current user interface. A context is a ILIAS repository
     * object (obj ID + obj type) with an additional optional subobject (ID + Type)
     *
     * @param	integer		object ID
     * @param	string		object type
     * @param	integer		subobject ID
     * @param	string		subobject type
     */
    public function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
    {
        $this->context_obj_id = $a_obj_id;
        $this->context_obj_type = $a_obj_type;
        $this->context_sub_obj_id = $a_sub_obj_id;
        $this->context_sub_obj_type = $a_sub_obj_type;
    }

    /**
     * Get context object id
     *
     * @return	int		object id
     */
    public function getContextObjId()
    {
        return $this->context_obj_id;
    }

    /**
     * Get context object type
     *
     * @return	string		object type
     */
    public function getContextObjType()
    {
        return $this->context_obj_type;
    }

    /**
     * Get context subobject id
     *
     * @return	int		subobject id
     */
    public function getContextSubObjId()
    {
        return $this->context_sub_obj_id;
    }

    /**
     * Get context subobject type
     *
     * @return	string		subobject type
     */
    public function getContextSubObjType()
    {
        return $this->context_sub_obj_type;
    }

    /**
     * Searches a node for a given class ($a_class) "near" another
     * node ($a_par_node).
     *
     * It first looks if the given class is a child class of the current node.
     * If such a child node has been found, its id is returned.
     *
     * If not, this method determines, whether the given class is a sibling
     * of the current node within the call structure. If this is the case,
     * then the corresponding id is returned.
     *
     * At last the method searches for the given class along the path from
     * the current node to the root class of the call structure.
     *
     * @param string $a_par_node id of starting node for the search
     * @param string $a_class class that should be searched
     * @param bool $a_check
     * @return array|bool id of target node that has been found
     * @throws ilCtrlException
     */
    private function getNodeIdForTargetClass($a_par_node, $a_class, $a_check = false)
    {
        $class = strtolower($a_class);
        $this->readClassInfo($class);
        
        if ($a_par_node === 0 || $a_par_node == "") {
            return array("node_id" => $this->getCidForClass($class),
                "base_class" => $class);
        }
        
        $this->readNodeInfo($a_par_node);
        
        $node_cid = $this->getCurrentCidOfNode($a_par_node);

        // target class is class of current node id
        if ($class == $this->getClassForCid($node_cid)) {
            return array("node_id" => $a_par_node,
                "base_class" => "");
        }

        // target class is child of current node id
        if (isset($this->calls[$this->getClassForCid($node_cid)]) &&
            is_array($this->calls[$this->getClassForCid($node_cid)]) &&
            in_array($a_class, $this->calls[$this->getClassForCid($node_cid)])) {
            return array("node_id" => $a_par_node . ":" . $this->getCidForClass($class),
                "base_class" => "");
        }

        // target class is sibling
        $par_cid = $this->getParentCidOfNode($a_par_node);
        if ($par_cid != "") {
            if (is_array($this->calls[$this->getClassForCid($par_cid)]) &&
                in_array($a_class, $this->calls[$this->getClassForCid($par_cid)])) {
                return array("node_id" =>
                    $this->removeLastCid($a_par_node) . ":" . $this->getCidForClass($class),
                    "base_class" => "");
            }
        }

        // target class is parent
        $temp_node = $this->removeLastCid($a_par_node);
        while ($temp_node != "") {
            $temp_cid = $this->getCurrentCidOfNode($temp_node);
            if ($this->getClassForCid($temp_cid) == $a_class) {
                return array("node_id" => $temp_node,
                    "base_class" => "");
            }
            $temp_node = $this->removeLastCid($temp_node);
        }
        
        // target class is another base class
        $n_class = "";
        if ($a_class != "") {
            $module_class = ilCachedCtrl::getInstance();
            $mc_rec = $module_class->lookupModuleClass($class);
            $n_class = $mc_rec['lower_class'];

            if ($n_class == "") {
                $mc_rec = $module_class->lookupServiceClass($class);
                $n_class = $mc_rec['lower_class'];
            }
            
            if ($n_class != "") {
                $this->getCallStructure($n_class);
                return array("node_id" => $this->getCidForClass($n_class),
                    "base_class" => $class);
            }
        }

        if ($a_check) {
            return false;
        }
        
        // Please do NOT change these lines.
        // Developers must be aware, if they use classes unknown to the controller
        // otherwise certain problem will be extremely hard to track down...

        error_log("ERROR: Can't find target class $a_class for node $a_par_node " .
            "(" . $this->cid_class[$this->getCidOfNode($a_par_node)] . ")");
            
        include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
        throw new ilCtrlException("ERROR: Can't find target class $a_class for node $a_par_node " .
            "(" . $this->cid_class[$this->getCidOfNode($a_par_node)] . ").");
    }

    /**
     * Check whether target is valid
     *
     * @param
     * @return
     */
    public function checkTargetClass($a_class)
    {
        if (!is_array($a_class)) {
            $a_class = array($a_class);
        }

        $nr = $this->current_node;
        foreach ($a_class as $class) {
            $class = strtolower($class);

            if (!$this->getCidForClass($class, true)) {
                return false;
            }

            $nr = $this->getNodeIdForTargetClass($nr, $class, true);
            $nr = $nr["node_id"];
            if ($nr === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get command target node
     *
     * @return	string		id of current command target node
     */
    public function getCmdNode()
    {
        return $_GET["cmdNode"];
    }

    /**
     * Add a tab to tabs array (@deprecated use $ilTabs)
     *
     * @param	string		$a_lang_var		language variable
     * @param	string		$a_link			link
     * @param	string		$a_cmd			command (must be same as in link)
     * @param	string		$a_class		command class (must be same as in link)
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class)
    {
        $a_class = strtolower($a_class);

        $this->tab[] = array("lang_var" => $a_lang_var,
            "link" => $a_link, "cmd" => $a_cmd, "class" => $a_class);
    }

    /**
     * Get tabs array		(@deprecated, use $ilTabs)
     *
     * @return	array		array of tab entries (array("lang_var", "link", "cmd", "class))
     */
    public function getTabs()
    {
        return $this->tab;
    }

    /**
     * Get controller call history.
     *
     * This is used for the developer mode and presented in the footer
     *
     * @return	array		array of call history entries
     */
    public function getCallHistory()
    {
        return $this->call_hist;
    }
    
    /**
     * Get call structure of class context. This method must be called
     * for the top level gui class in the leading php script. It must be
     * called before the the current command is forwarded to the top level
     * gui class. Example:
     *
     *	include_once "classes/class.ilRepositoryGUI.php";
     *	$ilCtrl->getCallStructure("ilrepositorygui");
     *	$repository_gui = new ilRepositoryGUI();
     *	$ilCtrl->forwardCommand($repository_gui);
     *
     * @param	string		$a_class	gui class name
     *
     * @access	public
     */
    public function getCallStructure($a_class)
    {
        $this->readClassInfo($a_class);
    }

    /**
     * Reads call structure from db
     */
    public function readCallStructure($a_class, $a_nr = 0, $a_parent = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $a_class = strtolower($a_class);

        $a_nr++;
        
        // determine call node structure
        $this->call_node[$a_nr] = array("class" => $a_class, "parent" => $a_parent);

        $call_set = $ilDB->query("SELECT * FROM ctrl_calls WHERE parent = " .
            $ilDB->quote(strtolower($a_class), "text") .
            " ORDER BY child", array("text"));
        $a_parent = $a_nr;
        while ($call_rec = $ilDB->fetchAssoc($call_set)) {
            $a_nr = $this->readCallStructure($call_rec["child"], $a_nr, $a_parent);
            $forw[] = $call_rec["child"];
        }

        // determine root class
        $this->root_class = $a_class;
        return $a_nr;
    }

    /**
     * Set parameters that should be passed in every form and link of a
     * gui class. All links that relate to the specified gui object class and
     * are build e.g. by using getLinkTarger() or getFormAction() will include
     * this parameter. This is the mechanism to add url parameters to the standard
     * url target everytime.
     *
     * A typical example is the "ref_id" that should be included in almost every
     * link or form action url. So the constructor of ilRepositoryGUI includes
     * the command:
     *
     *	$this->ctrl->saveParameter($this, array("ref_id"));
     *
     * @param	object	$a_obj			gui object that will process the parameter
     * @param	mixed	$a_parameter	parameter name (string) or array of parameter
     *									names
     *
     * @access	public
     */
    public function saveParameter($a_obj, $a_parameter)
    {
        if (is_object($a_obj)) {
            $this->saveParameterByClass(get_class($a_obj), $a_parameter);
        }
    }
    
    /**
     * Save parameter for a class
     *
     * @param	string	class name
     * @param	string	parameter name
     */
    public function saveParameterByClass($a_class, $a_parameter)
    {
        if (is_array($a_parameter)) {
            foreach ($a_parameter as $parameter) {
                $this->save_parameter[strtolower($a_class)][] = $parameter;
            }
        } else {
            $this->save_parameter[strtolower($a_class)][] = $a_parameter;
        }
    }


    /**
     * Set parameters that should be passed a form and link of a
     * gui class. All links that relate to the specified gui object class and
     * are build e.g. by using getLinkTarger() or getFormAction() will include
     * this parameter. This is the mechanism to add url parameters to the standard
     * url target. The difference to the saveParameter() method is, that setParameter()
     * does not simply forward the url parameter of the last request. You can set
     * a spefific value.
     *
     * If this parameter is also a "saved parameter" (set by saveParameter() method)
     * the saved value will be overwritten.
     *
     * The method is usually used in conjunction with a getFormAction() or getLinkTarget()
     * call. E.g.:
     *
     *		$this->ctrl->setParameter($this, "obj_id", $data_row["obj_id"]);
     *		$obj_link = $this->ctrl->getLinkTarget($this, "view");
     *
     * @param	object		$a_obj			gui object
     * @param	string		$a_parameter	parameter name
     * @param	string		$a_parameter	parameter value
     */
    public function setParameter($a_obj, $a_parameter, $a_value)
    {
        $this->parameter[strtolower(get_class($a_obj))][$a_parameter] = $a_value;
    }


    /**
     * Same as setParameterByClass, except that a class name is passed.
     *
     * @param	string		$a_class		gui class name
     * @param	string		$a_parameter	parameter name
     * @param	string		$a_parameter	parameter value
     */
    public function setParameterByClass($a_class, $a_parameter, $a_value)
    {
        $this->parameter[strtolower($a_class)][$a_parameter] = $a_value;
    }

    /**
     * Same as setParameterByClass, except that a class name is passed.
     *
     * @param	string		$a_class		gui class name
     * @param	string		$a_parameter	parameter name
     * @param	string		$a_parameter	parameter value
     */
    public function clearParameterByClass($a_class, $a_parameter)
    {
        unset($this->parameter[strtolower($a_class)][$a_parameter]);
    }

    /**
     * Clears all parameters that have been set via setParameter for
     * a GUI class.
     *
     * @param	object		$a_obj			gui object
     */
    public function clearParameters($a_obj)
    {
        $this->clearParametersByClass(strtolower(get_class($a_obj)));
    }

    /**
     * Clears all parameters that have been set via setParameter for
     * a GUI class.
     *
     * @param	string		$a_class		gui class name
     */
    public function clearParametersByClass($a_class)
    {
        $this->parameter[strtolower($a_class)] = array();
    }
    
    protected function checkLPSettingsForward($a_gui_obj, $a_cmd_node)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        
        // forward to learning progress settings if possible and accessible
        if ($_GET["gotolp"] &&
            $a_gui_obj) {
            $ref_id = $_GET["ref_id"];
            if (!$ref_id) {
                $ref_id = $_REQUEST["ref_id"];
            }
            
            $gui_class = get_class($a_gui_obj);
            
            if ($gui_class == "ilSAHSEditGUI") {
                // #1625 - because of scorm "sub-types" this is all very special
                include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
                $obj_id = ilObject::_lookupObjectId($ref_id);
                switch (ilObjSAHSLearningModule::_lookupSubType($obj_id)) {
                    case "scorm2004":
                        $class = "ilObjSCORM2004LearningModuleGUI";
                        break;
                
                    case "scorm":
                        $class = "ilObjSCORMLearningModuleGUI";
                        break;

                    case "aicc":
                        $class = "ilObjAICCLearningModuleGUI";
                        break;

                    case "hacp":
                        $class = "ilObjHACPLearningModuleGUI";
                        break;
                }
                if ($GLOBALS["ilAccess"]->checkAccess("edit_learning_progress", "", $ref_id)) {
                    $this->redirectByClass(array($gui_class, $class, "illearningprogressgui", "illplistofsettingsgui"), "");
                }
            }
            // special case: cannot use any presentation GUIs
            elseif ($gui_class == "ilLMPresentationGUI") {
                $this->setParameterByClass("ilObjLearningModuleGUI", "gotolp", 1);
                $this->redirectByClass(array("ilLMEditorGUI", "ilObjLearningModuleGUI"), "");
            }
                        
            include_once "Services/Object/classes/class.ilObjectLP.php";
            $type = ilObject::_lookupType($ref_id, true);
            $class = "ilObj" . $objDefinition->getClassName($type) . "GUI";
            
            if ($gui_class == $class &&
                ilObjectLP::isSupportedObjectType($type) &&
                $GLOBALS["ilAccess"]->checkAccess("edit_learning_progress", "", $ref_id)) {
                // add path to repository object gui if missing from cmdNode
                if (!$a_cmd_node) {
                    $repo_node = $this->getNodeIdForTargetClass(null, "ilrepositorygui");
                    $obj_node = $this->getNodeIdForTargetClass($repo_node["node_id"], $gui_class);
                    $a_cmd_node = $obj_node["node_id"];
                }
                // find path to lp settings
                $lp_node = $this->getNodeIdForTargetClass($a_cmd_node, "illearningprogressgui");
                $lp_settings_node = $this->getNodeIdForTargetClass($lp_node["node_id"], "illplistofsettingsgui");
                $_GET["cmdNode"] = $lp_settings_node["node_id"];
                $_GET["cmdClass"] = "ilLPListOfSettingsGUI";
                $_GET["cmd"] = "";
                return "illearningprogressgui";
            }
        }
    }

    /**
     * Get next class in the control path from the current class
     * to the target command class. This is the class that should
     * be instantiated and be invoked via $ilCtrl->forwardCommand($class)
     * next.
     *
     * @return	string		class name of next class
     */
    public function getNextClass($a_gui_class = null)
    {
        if ($this->use_current_to_determine_next) {
            $cmdNode = $this->current_node;
        } else {
            $cmdNode = $this->getCmdNode();
        }

        if ($cmdNode == "") {
            return ($class = $this->checkLPSettingsForward($a_gui_class, $cmdNode))
                ? $class
                : false;
        } else {
            if ($this->current_node == $cmdNode) {
                return ($class = $this->checkLPSettingsForward($a_gui_class, $cmdNode))
                    ? $class
                    : "";
            } else {
                $path = $this->getPathNew($this->current_node, $cmdNode);
                $this->readCidInfo($this->getCurrentCidOfNode($path[1]));
                return $this->cid_class[$this->getCurrentCidOfNode($path[1])];
            }
        }
    }

    /**
     * Get class path that can be used in include statements
     * for a given class name.
     *
     * @param	string		$a_class_name		class name
     */
    public function lookupClassPath($a_class_name)
    {
        $a_class_name = strtolower($a_class_name);

        $cached_ctrl = ilCachedCtrl::getInstance();
        $class_rec = $cached_ctrl->lookupClassFile($a_class_name);

        if ($class_rec["plugin_path"] != "") {
            return $class_rec["plugin_path"] . "/" . $class_rec["filename"];
        } else {
            return $class_rec["filename"];
        }
    }

    /**
     * this method assumes that the class path has the format "dir/class.<class_name>.php"
     *
     * @param	string		$a_class_path		class path
     * @access	public
     *
     * @return	string		class name
     */
    public function getClassForClasspath($a_class_path)
    {
        $path = pathinfo($a_class_path);
        $file = $path["basename"];
        $class = substr($file, 6, strlen($file) - 10);

        return $class;
    }

    /**
     * Get path in call structure.
     *
     * @param	string		$a_source_node		source node id
     * @param	string		$a_source_node		target node id
     */
    private function getPathNew($a_source_node, $a_target_node)
    {
        if ($a_source_node == "1") {
            $a_source_node = "";
        }
        if (substr($a_target_node, 0, strlen($a_source_node)) != $a_source_node) {
            $failure = "ERROR: Path not found. Source:" . $a_source_node .
                ", Target:" . $a_target_node;
            if (DEVMODE == 1) {
                include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
                throw new ilCtrlException($failure);
            }
            $GLOBALS['ilLog']->write(__METHOD__ . ' ' . $failure);
            $this->redirectToURL('./ilias.php?baseClass=ilRepositoryGUI');
        }
        $temp_node = $a_source_node;
        
        $path = array();
        if ($a_source_node != "") {
            $path = array($a_source_node);
        }
        
        $diffstart = ($a_source_node == "")
            ? 0
            : strlen($a_source_node) + 1;
        $diff = substr($a_target_node, $diffstart);
        $diff_arr = explode(":", $diff);
        foreach ($diff_arr as $cid) {
            if ($temp_node != "") {
                $temp_node .= ":";
            }
            $temp_node .= $cid;
            $path[] = $temp_node;
        }
        return $path;
    }

    /**
     * set target script name
     *
     * @param	string		$a_target_script		target script name
     */
    public function setTargetScript(string $a_target_script)
    {
        $this->target_script = $a_target_script;
    }

    /**
     * Get readable node
     * @param
     * @return
     */
    protected function getReadableNode($node)
    {
        return implode(
            ":",
            array_map(function ($cid) {
                return $this->getClassForCid($cid);
            }, explode(":", $node))
        );
    }

    /**
     * Get target script name
     *
     * @return	string		target script name
     */
    public function getTargetScript() : string
    {
        return $this->target_script;
    }


    /**
     * Initialises new base class
     *
     * Note: this resets the whole current ilCtrl context completely.
     * You can call callBaseClass() after that.
     *
     * @param	string		base class name
     */
    public function initBaseClass($a_base_class)
    {
        $_GET["baseClass"] = $a_base_class;
        $_GET["cmd"] = "";
        $_GET["cmdClass"] = "";
        $_GET["cmdNode"] = "";
        $this->initializeMemberVariables();
    }
    
    /**
     * Determines current get/post command
     *
     * @param	string		default command
     * @param	array		safe commands: for these commands no token
     *						is checked for post requests
     */
    public function getCmd($a_default_cmd = "", $a_safe_commands = "")
    {
        $cmd = "";
        if (isset($_GET["cmd"])) {
            $cmd = $_GET["cmd"];
        }
        if ($cmd == "post") {
            if (isset($_POST["cmd"]) && is_array($_POST["cmd"])) {
                reset($_POST["cmd"]);
            }
            $cmd = @key($_POST["cmd"]);

            // verify command
            if ($this->verified_cmd != "") {
                return $this->verified_cmd;
            } else {
                if (!$this->verifyToken() &&
                    (!is_array($a_safe_commands) || !in_array($cmd, $a_safe_commands))) {
                    return $a_default_cmd;
                }
            }
            
            $this->verified_cmd = $cmd;
            if ($cmd == "" && isset($_POST["table_top_cmd"])) {		// selected command in multi-list (table2)
                $cmd = @key($_POST["table_top_cmd"]);
                $this->verified_cmd = $cmd;
                $_POST[$_POST["cmd_sv"][$cmd]] = $_POST[$_POST["cmd_sv"][$cmd] . "_2"];
            }
            if ($cmd == "" && isset($_POST["select_cmd2"])) {		// selected command in multi-list (table2)
                if (isset($_POST["select_cmd_all2"])) {
                    $_POST["select_cmd_all"] = $_POST["select_cmd_all2"];
                } else {
                    $_POST["select_cmd_all"] = $_POST["select_cmd_all2"] = null;
                }
                $cmd = $_POST["selected_cmd2"];
                $this->verified_cmd = $cmd;
            }
            if ($cmd == "" && isset($_POST["select_cmd"])) {		// selected command in multi-list (table2)
                if (isset($_POST["select_cmd_all"])) {
                    $_POST["select_cmd_all2"] = $_POST["select_cmd_all"];
                } else {
                    $_POST["select_cmd_all"] = $_POST["select_cmd_all2"] = null;
                }
                $cmd = $_POST["selected_cmd"];
                $this->verified_cmd = $cmd;
            }
            if ($cmd == "") {
                $cmd = $_GET["fallbackCmd"];
                $this->verified_cmd = $cmd;
            }
        }
        if ($cmd == "") {
            $cmd = $a_default_cmd;
        }
        return $cmd;
    }

    /**
     * Set the current command
     *
     * IMPORTANT NOTE:
     *
     * please use this function only in exceptional cases
     * it is not intended for setting commands in forms or links!
     * use the corresponding parameters of getFormAction() and
     * getLinkTarget() instead.
     */
    public function setCmd($a_cmd)
    {
        $_GET["cmd"] = $a_cmd;
    }

    /**
     * Set the current command class
     *
     * IMPORTANT NOTE:
     *
     * please use this function only in exceptional cases
     * it is not intended for setting the command class in forms or links!
     * use the corresponding parameters of getFormAction() and
     * getLinkTarget() instead.
     */
    public function setCmdClass($a_cmd_class)
    {
        $a_cmd_class = strtolower($a_cmd_class);
        $nr = $this->getNodeIdForTargetClass($this->current_node, $a_cmd_class);
        $nr = $nr["node_id"];
        $_GET["cmdClass"] = $a_cmd_class;
        $_GET["cmdNode"] = $nr;
    }

    /**
     * Determines class that should execute the current command
     *
     * @return	string		class name
     */
    public function getCmdClass()
    {
        return strtolower($_GET["cmdClass"]);
    }

    /**
     * Get form action url for gui class object
     *
     * @param	object		gui object
     * @param	string		fallback command
     * @param	string		anchor
     * @param	bool		asynchronous call
     * @param	bool		xml style t/f
     * @return	string		script url
     */
    public function getFormAction(
        $a_gui_obj,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        $script = $this->getFormActionByClass(
            strtolower(get_class($a_gui_obj)),
            $a_fallback_cmd,
            $a_anchor,
            $a_asynch,
            $xml_style
        );
        return $script;
    }

    /**
     * Get form action url for gui class name
     *
     * @param	string		gui class name
     * @param	string		fallback command
     * @param	string		anchor
     * @param	bool		asynchronous call
     * @param	bool		xml style t/f
     * @return	string		script url
     */
    public function getFormActionByClass(
        $a_class,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        if (!is_array($a_class)) {
            $a_class = strtolower($a_class);
        }
        
        $tok = $this->getRequestToken();

        if ($a_asynch) {
            $xml_style = false;
        }

        $script = $this->getLinkTargetByClass($a_class, "post", "", $a_asynch, $xml_style);
        if ($a_fallback_cmd != "") {
            $script = ilUtil::appendUrlParameterString($script, "fallbackCmd=" . $a_fallback_cmd, $xml_style);
        }
        $script = ilUtil::appendUrlParameterString(
            $script,
            self::IL_RTOKEN_NAME . '=' . $this->getRequestToken(),
            $xml_style
        );
        if ($a_anchor != "") {
            $script = $script . "#" . $a_anchor;
        }

        return $script;
    }
    
    /**
     * Append request token as url parameter
     *
     * @param	string	url
     * @param	boolean	xml style
     */
    public function appendRequestTokenParameterString($a_url, $xml_style = false)
    {
        return ilUtil::appendUrlParameterString(
            $a_url,
            self::IL_RTOKEN_NAME . '=' . $this->getRequestToken(),
            $xml_style
        );
    }
    
    /**
     * Get request token.
     *
     * @return	string		request token for user and session
     */
    public function getRequestToken()
    {
        global $DIC;

        $ilUser = $DIC["ilUser"];
        $ilDB = $DIC->database();

        
        if ($this->rtoken != "") {
            return $this->rtoken;
        } else {
            if (is_object($ilDB) && is_object($ilUser) && $ilUser->getId() > 0 &&
                $ilUser->getId() != ANONYMOUS_USER_ID) {
                $res = $ilDB->query("SELECT token FROM il_request_token WHERE user_id = " .
                    $ilDB->quote($ilUser->getId(), "integer") .
                    " AND session_id = " . $ilDB->quote(session_id(), "text"));
                $rec = $ilDB->fetchAssoc($res);
                if ($rec["token"] != "") {
                    $this->rtoken = $rec["token"];
                    return $rec["token"];
                }
                //echo "new rtoken, new entry for :".$ilUser->getId().":".session_id().":"; exit;
                $random = new \ilRandom();
                $this->rtoken = md5(uniqid($random->int(), true));
                
                // delete entries older than one and a half days
                if ($random->int(1, 200) == 2) {
                    $dt = new ilDateTime(time(), IL_CAL_UNIX);
                    $dt->increment(IL_CAL_DAY, -1);
                    $dt->increment(IL_CAL_HOUR, -12);
                    $dq = "DELETE FROM il_request_token WHERE " .
                        " stamp < " . $ilDB->quote($dt->get(IL_CAL_DATETIME), "timestamp");
                    $ilDB->manipulate($dq);
                }
                
                // IMPORTANT: Please do NOT try to move this implementation to a
                // session basis. This will fail due to framesets that are used
                // occasionally in ILIAS, e.g. in the chat, where multiple
                // forms are loaded in different frames.
                $ilDB->manipulate("INSERT INTO il_request_token (user_id, token, stamp, session_id) VALUES " .
                    "(" .
                    $ilDB->quote($ilUser->getId(), "integer") . "," .
                    $ilDB->quote($this->rtoken, "text") . "," .
                    $ilDB->now() . "," .
                    $ilDB->quote(session_id(), "text") . ")");
                return $this->rtoken;
            }
        }
        return "";
    }
    
    /**
     * Verify Token
     *
     * @return	boolean		valid t/f
     */
    private function verifyToken()
    {
        global $DIC;

        $ilUser = $DIC["ilUser"];

        $ilDB = $DIC->database();
        ;

        if (is_object($ilUser) && is_object($ilDB) && $ilUser->getId() > 0 &&
            $ilUser->getId() != ANONYMOUS_USER_ID) {
            if ($_GET["rtoken"] == "") {
                return false;
            }

            $set = $ilDB->query("SELECT * FROM il_request_token WHERE " .
                " user_id = " . $ilDB->quote($ilUser->getId(), "integer") . " AND " .
                " token = " . $ilDB->quote($_GET[self::IL_RTOKEN_NAME]), "text");
            if ($ilDB->numRows($set) > 0) {
                // remove tokens from older sessions
                // if we do this immediately, working with multiple windows does not work:
                // - window one: open form (with token a)
                // - window two: open form (with token b)
                // - submit window one: a is verified, but b must not be deleted immediately, otherwise
                // - window two: submit results in invalid token
                // see also bug #13551
                $dt = new ilDateTime(time(), IL_CAL_UNIX);
                $dt->increment(IL_CAL_DAY, -1);
                $dt->increment(IL_CAL_HOUR, -12);
                $ilDB->manipulate("DELETE FROM il_request_token WHERE " .
                    " user_id = " . $ilDB->quote($ilUser->getId(), "integer") . " AND " .
                    " session_id != " . $ilDB->quote(session_id(), "text") . " AND " .
                    " stamp < " . $ilDB->quote($dt->get(IL_CAL_DATETIME), "timestamp"));
                return true;
            } else {
                return false;
            }
            
            if ($_SESSION["rtokens"][$_GET[self::IL_RTOKEN_NAME]] != "") {
                // remove used token
                unset($_SESSION["rtokens"][$_GET[self::IL_RTOKEN_NAME]]);
                
                // remove old tokens
                if (count($_SESSION["rtokens"]) > 100) {
                    $to_remove = array();
                    $sec = 7200;			// two hours

                    foreach ($_SESSION["rtokens"] as $tok => $time) {
                        if (time() - $time > $sec) {
                            $to_remove[] = $tok;
                        }
                    }
                    foreach ($to_remove as $tok) {
                        unset($_SESSION["rtokens"][$tok]);
                    }
                }
                
                return true;
            }
            return false;
        } else {
            return true;		// do not verify, if user or db object is missing
        }
        
        return false;
    }

    /**
     * Redirect to another command
     *
     * @param	object		gui object
     * @param	string		command
     * @param	string		anchor
     */
    public function redirect($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        $script = $this->getLinkTargetByClass(
            strtolower(get_class($a_gui_obj)),
            $a_cmd,
            "",
            $a_asynch,
            false
        );
        if ($a_anchor != "") {
            $script = $script . "#" . $a_anchor;
        }
        $this->redirectToURL($script);
    }


    /**
     * @param $a_script
     */
    public function redirectToURL($a_script)
    {
        global $DIC;

        $ilPluginAdmin = null;
        if (isset($DIC["ilPluginAdmin"])) {
            $ilPluginAdmin = $DIC["ilPluginAdmin"];
        }

        if (!is_int(strpos($a_script, "://"))) {
            if (substr($a_script, 0, 1) != "/" && defined("ILIAS_HTTP_PATH")) {
                if (is_int(strpos($_SERVER["PHP_SELF"], "/setup/"))) {
                    $a_script = "setup/" . $a_script;
                }
                $a_script = ILIAS_HTTP_PATH . "/" . $a_script;
            }
        }

        // include the user interface hook
        if (is_object($ilPluginAdmin)) {
            $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
            foreach ($pl_names as $pl) {
                $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
                $gui_class = $ui_plugin->getUIClassInstance();
                $resp = $gui_class->getHTML("Services/Utilities", "redirect", array( "html" => $a_script ));
                if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                    $a_script = $gui_class->modifyHTML($a_script, $resp);
                }
            }
        }

        // Manually trigger to write and close the session. This has the advantage that if an exception is thrown
        // during the writing of the session (ILIAS writes the session into the database by default) we get an exception
        // if the session_write_close() is triggered by exit() then the exception will be dismissed but the session
        // is never written, which is a nightmare to develop with.
        session_write_close();

        global $DIC;
        $http = $DIC->http();
        switch ($http->request()->getHeaderLine('Accept')) {
            case 'application/json':
                $stream = \ILIAS\Filesystem\Stream\Streams::ofString(json_encode([
                    'success' => true,
                    'message' => 'Called redirect after async fileupload request',
                    "redirect_url" => $a_script,
                ]));
                $http->saveResponse($http->response()->withBody($stream));
                break;
            default:
                $http->saveResponse($http->response()->withAddedHeader("Location", $a_script));
                break;
        }
        $http->sendResponse();
        exit;
    }


    /**
     * Redirect to other gui class using class name
     *
     * @param	string		command target class
     * @param	string		command
     */
    public function redirectByClass($a_class, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        $script = $this->getLinkTargetByClass($a_class, $a_cmd, "", $a_asynch, false);
        if ($a_anchor != "") {
            $script = $script . "#" . $a_anchor;
        }
        $this->redirectToURL($script);
    }
    
    /**
     * Is current command an asynchronous command?
     *
     * @return	boolean		asynchronous t/f
     */
    public function isAsynch()
    {
        if (isset($_GET["cmdMode"]) && $_GET["cmdMode"] == "asynch") {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get link target for command using gui object
     *
     * @param	object		gui object (usually $this)
     * @param	string		command
     * @param	string		# anchor
     * @param	boolean		asynchronous mode
     * @param	boolean		xml style t/f
     *
     * @return	string		target link
     */
    public function getLinkTarget(
        $a_gui_obj,
        $a_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        $script = $this->getLinkTargetByClass(
            strtolower(get_class($a_gui_obj)),
            $a_cmd,
            $a_anchor,
            $a_asynch,
            $xml_style
        );
        return $script;
    }


    /**
     * Get link target for command using gui class name
     *
     * @param	string/array		command target class
     * @param	string		command
     * @param	string		# anchor
     * @param	boolean		asynchronous mode
     * @param	boolean		xml style t/f
     *
     * @return	string		target link
     */
    public function getLinkTargetByClass(
        $a_class,
        $a_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        if ($a_asynch) {
            $xml_style = false;
        }
        
        $script = $this->getTargetScript();
        $script = $this->getUrlParameters($a_class, $script, $a_cmd, $xml_style);

        if ($a_asynch) {
            $amp = "&";
            $script .= $amp . "cmdMode=asynch";
        }
        
        if ($a_anchor != "") {
            $script = $script . "#" . $a_anchor;
        }

        return $script;
    }

    /**
     * Set return command
     */
    public function setReturn($a_gui_obj, $a_cmd)
    {
        $script = $this->getTargetScript();
        $script = $this->getUrlParameters(strtolower(get_class($a_gui_obj)), $script, $a_cmd);
        $this->return[strtolower(get_class($a_gui_obj))] = $script;
    }

    /**
     * Set return command
     */
    public function setReturnByClass($a_class, $a_cmd)
    {
        // may not be an array!
        $a_class = strtolower($a_class);

        $script = $this->getTargetScript();
        $script = $this->getUrlParameters($a_class, $script, $a_cmd);
        $this->return[strtolower($a_class)] = $script;
    }

    /**
     * Redirects to next parent class that used setReturn.
     */
    public function returnToParent($a_gui_obj, $a_anchor = "")
    {
        $script = $this->getParentReturn($a_gui_obj);

        $script = ilUtil::appendUrlParameterString(
            $script,
            "redirectSource=" . strtolower(get_class($a_gui_obj))
        );
        $script = ilUtil::appendUrlParameterString(
            $script,
            "cmdMode=" . $_GET["cmdMode"]
        );
        if ($a_anchor != "") {
            $script = $script . "#" . $a_anchor;
        }

        $this->redirectToURL($script);
    }


    /**
     * Get return script url.
     *
     * Used in conjunction with ilTabs->setBackTarget and ilBlockGUI->addHeaderCommand.
     */
    public function getParentReturn($a_gui_obj)
    {
        return $this->getParentReturnByClass(strtolower(get_class($a_gui_obj)));
    }


    /**
     * Get return script url
     *
     * Only used in getParentReturn.
     */
    public function getParentReturnByClass($a_class)
    {
        $a_class = strtolower($a_class);
        $ret_class = $this->searchReturnClass($a_class);
        if ($ret_class) {
            return $this->return[$ret_class];
        }
    }

    /**
     * Get return class.
     *
     * Only used in COPage/ilPCParagraphGUI and COPage/ilPCPlaceHolderGUI
     *
     * @param	string|object	$class
     * @return	string|bool
     */
    public function getReturnClass($a_class)
    {
        if (is_object($a_class)) {
            $class = strtolower(get_class($a_class));
        } else {
            $class = strtolower($a_class);
        }
        return $this->searchReturnClass($class);
    }
    
    
    /**
     * Determine current return class
     */
    private function searchReturnClass($a_class)
    {
        $a_class = strtolower($a_class);

        $node = $this->getNodeIdForTargetClass($this->current_node, $a_class);
        $node = $node["node_id"];
        $n_arr = explode(":", $node);
        for ($i = count($n_arr) - 2; $i >= 0; $i--) {
            if ($this->return[$this->getClassForCid($n_arr[$i])] != "") {
                return $this->getClassForCid($n_arr[$i]);
            }
        }

        return false;
    }

    /**
     * Get current redirect source
     *
     * @return	string		redirect source class
     */
    public function getRedirectSource()
    {
        return $_GET["redirectSource"];
    }

    /**
     * Get URL parameters for a class and append them to a string
     * @param $a_class
     * @param $a_str
     * @param string $a_cmd command
     * @param bool $xml_style
     * @return string
     */
    public function getUrlParameters($a_class, $a_str, $a_cmd = "", $xml_style = false)
    {
        $params = $this->getParameterArrayByClass($a_class, $a_cmd);

        foreach ($params as $par => $value) {
            if (strlen((string) $value)) {
                $a_str = ilUtil::appendUrlParameterString($a_str, $par . "=" . $value, $xml_style);
            }
        }

        return $a_str;
    }

    /**
     * Get all set/save parameters for a gui object
     */
    public function getParameterArray($a_gui_obj, $a_cmd = "")
    {
        $par_arr = $this->getParameterArrayByClass(strtolower(get_class($a_gui_obj)), $a_cmd);

        return $par_arr;
    }

    /**
     * Get all set/save parameters using gui class name
     *
     * @param	string		class name
     * @param	string		cmd
     $ @return	array		parameter array
     */
    public function getParameterArrayByClass($a_class, $a_cmd = "")
    {
        if ($a_class == "") {
            return array();
        }

        $current_base_class = $_GET["baseClass"];
        if ($this->use_current_to_determine_next && $this->inner_base_class != "") {
            $current_base_class = $this->inner_base_class;
        }

        if (!is_array($a_class)) {
            $a_class = array($a_class);
        }

        $nr = $this->current_node;
        $new_baseclass = "";
        foreach ($a_class as $class) {
            $class = strtolower($class);
            $nr = $this->getNodeIdForTargetClass($nr, $class);
            if ($nr["base_class"] != "") {
                $new_baseclass = $nr["base_class"];
            }
            $nr = $nr["node_id"];
            $target_class = $class;
        }

        $path = $this->getPathNew(1, $nr);
        $params = array();

        // append parameters of parent classes
        foreach ($path as $node_id) {
            $class = ($node_id == "")
                ? strtolower($current_base_class)
                : $this->getClassForCid($this->getCurrentCidOfNode($node_id));
            if (isset($this->save_parameter[$class]) && is_array($this->save_parameter[$class])) {
                foreach ($this->save_parameter[$class] as $par) {
                    if (isset($_GET[$par])) {
                        $params[$par] = $_GET[$par];
                    } elseif (isset($_POST[$par])) {
                        $params[$par] = ilUtil::stripSlashesRecursive($_POST[$par]);
                    }
                }
            }

            if (isset($this->parameter[$class]) && is_array($this->parameter[$class])) {
                foreach ($this->parameter[$class] as $par => $value) {
                    $params[$par] = $value;
                }
            }
        }

        if ($a_cmd != "") {
            $params["cmd"] = $a_cmd;
        }

        $params["cmdClass"] = $target_class;
        $params["cmdNode"] = $nr;
        if ($new_baseclass == "") {
            $params["baseClass"] = $current_base_class;
        } else {
            $params["baseClass"] = $new_baseclass;
        }

        return $params;
    }
    
    private function classCidUnknown($a_class)
    {
        return $this->class_cid[$a_class] == "";
    }

    /**
     * Get class id for class after fetching and storing corresponding information, if necessary.
     */
    private function getCidForClass($a_class, $a_check = false)
    {
        if ($this->classCidUnknown($a_class)) {
            $this->readClassInfo($a_class);
        }
        if ($this->classCidUnknown($a_class)) {
            if ($a_check) {
                return false;
            }
            if (DEVMODE == 1) {
                $add = "<br><br>Please make sure your GUI class name ends with 'GUI' and that the filename is 'class.[YourClassName].php'. In exceptional cases you
					may solve the issue by putting an empty * @ilCtrl_Calls [YourClassName]: into your class header." .
                    " In both cases you need to reload the control structure in the setup.";
            }
            include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
            throw new ilCtrlException("Cannot find cid for class " . $a_class . "." . $add);
        }
        return $this->class_cid[$a_class];
    }

    private function cidClassUnknown($a_cid)
    {
        return $this->cid_class[$a_cid] == "";
    }


    /**
     * Get class for class id after fetching and storing corresponding information, if necessary.
     */
    private function getClassForCid($a_cid)
    {
        if ($this->cidClassUnknown($a_cid)) {
            $this->readCidInfo($a_cid);
        }
        if ($this->cidClassUnknown($a_cid)) {
            include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
            throw new ilCtrlException("Cannot find class for cid " . $a_cid . ".");
        }
        return $this->cid_class[$a_cid];
    }

    private function fetchCallsOfClassFromCache($a_class, ilCachedCtrl $a_cached_ctrl)
    {
        foreach ($a_cached_ctrl->lookupCall($a_class) as $call) {
            if ($call["child"] != "" && $this->callOfClassNotKnown($a_class, $call['child'])) {
                $this->calls[$a_class][] = $call["child"];
            }
        }
    }

    /**
     * Save class respective to $a_cid and store corresponding
     * class calls for future reference.
     *
     * @param object $a_cid		cid
     */
    private function readCidInfo($a_cid)
    {
        if (isset($this->info_read_cid[$a_cid])) {
            return;
        }

        $cached_ctrl = ilCachedCtrl::getInstance();
        $cid_info = $cached_ctrl->lookupCid($a_cid);

        if ($cid_info) {
            $this->updateClassCidMap($cid_info['class'], $a_cid);
            $this->fetchCallsOfClassFromCache($cid_info['class'], $cached_ctrl);
            $this->info_read_class[$cid_info["class"]] = true;
        }
        
        $this->info_read_cid[$a_cid] = true;
    }

    /**
     * Save classes respective to the class id's of a node and store corresponding
     * class calls for future reference.
     *
     * @param	string	$a_node
     */
    private function readNodeInfo($a_node)
    {
        $class_ids = explode(":", $a_node);
        foreach ($class_ids as $cid) {
            $this->readCidInfo($cid);
        }
    }

    /**
     * Save class id respective to $a_class and store corresponding
     * class calls for future reference.
     *
     * @param	object	$a_class	class name
     */
    private function readClassInfo($a_class)
    {
        $a_class = strtolower($a_class);
        if (isset($this->info_read_class[$a_class])) {
            return;
        }

        $cached_ctrl = ilCachedCtrl::getInstance();
        $class_info = $cached_ctrl->lookupClassFile($a_class);

        if ($class_info) {
            $this->updateClassCidMap($a_class, $class_info['cid']);
        }
        $this->fetchCallsOfClassFromCache($a_class, $cached_ctrl);
        
        $this->info_read_class[$a_class] = true;
        $this->info_read_cid[$this->class_cid[$a_class]] = true;
    }

    private function callOfClassNotKnown($a_class, $a_child)
    {
        return !isset($this->calls[$a_class])
                || !is_array($this->calls[$a_class])
                || !in_array($a_child, $this->calls[$a_class]);
    }

    private function updateClassCidMap($a_class, $a_cid)
    {
        $this->cid_class[$a_cid] = $a_class;
        $this->class_cid[$a_class] = $a_cid;
    }

    /**
     * Get 2nd to last class id of node
     */
    private function getParentCidOfNode($a_node)
    {
        $class_ids = explode(":", $a_node);
        return $class_ids[count($class_ids) - 2];
    }

    /**
     * Get last class id of node
     */
    private function getCidOfNode($a_node)
    {
        $class_ids = explode(":", $a_node);
        return $class_ids[count($class_ids) - 1];
    }

    /**
     * Remove the class id that comes at the beginning the sequence.
     */
    private function removeLastCid($a_node)
    {
        $lpos = strrpos($a_node, ":");
        return substr($a_node, 0, $lpos);
    }

    /**
     * Get cid of node
     */
    private function getCurrentCidOfNode($a_node)
    {
        $n_arr = explode(":", $a_node);
        return $n_arr[count($n_arr) - 1];
    }

    /**
     * Insert ctrl calls record
     *
     * @param
     * @return
     */
    public function insertCtrlCalls($a_parent, $a_child, $a_comp_prefix)
    {
        global $DIC;

        $ilDB = $DIC->database();
        ;

        $a_parent = strtolower($a_parent);
        $a_child = strtolower($a_child);
        $a_comp_prefix = strtolower($a_comp_prefix);

        $set = $ilDB->query(
            "SELECT * FROM ctrl_calls WHERE " .
            " parent = " . $ilDB->quote($a_parent, "text") . " AND " .
            " child = " . $ilDB->quote($a_child, "text") . " AND " .
            " comp_prefix = " . $ilDB->quote($a_comp_prefix, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return;
        }
        $ilDB->manipulate("INSERT IGNORE INTO ctrl_calls " .
            "(parent, child, comp_prefix) VALUES (" .
            $ilDB->quote($a_parent, "text") . "," .
            $ilDB->quote($a_child, "text") . "," .
            $ilDB->quote($a_comp_prefix, "text") .
            ")");
    }

    /**
     * Check if current path contains a certain gui class
     *
     * @param $gui_class
     * @return bool
     * @throws ilCtrlException
     */
    public function checkCurrentPathForClass($gui_class)
    {
        foreach (explode(":", $this->getCmdNode()) as $cid) {
            if ($cid != "" && strtolower($this->getClassForCid($cid)) == strtolower($gui_class)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get current class path as array of class file names
     *
     * @return array
     * @throws ilCtrlException
     */
    public function getCurrentClassPath() : array
    {
        $path = [];
        foreach (explode(":", $this->getCmdNode()) as $cid) {
            if ($cid != "") {
                $path[] = $this->getClassForCid($cid);
            }
        }
        if ($this->getCmdNode() == "" && $_GET["baseClass"] != "") {
            $path[] = $_GET["baseClass"];
        }
        return $path;
    }
}
