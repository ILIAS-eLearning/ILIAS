<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\DI\Container;
use ILIAS\Refinery\Factory;

/**
 * Class ilCtrl2
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCtrl2 extends ilCtrl implements ilCtrlInterface
{

    const P_BASE_CLASS = 'baseClass';
    const P_CMD = 'cmd';
    const P_CMD_NODE = 'cmdNode';
    const P_CMD_CLASS = 'cmdClass';
    const CID = 'cid';
    const CLASS_NAME = 'class_name';
    private array $ctrl_structure;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $http;
    private \ILIAS\Refinery\Factory $refinery;
    protected string $fallback_baseclass;
    protected string $current_cid;

    public function __construct()
    {
        global $DIC;
        /**
         * @var $DIC Container
         */

        $this->ctrl_structure = include "./Services/UICore/artifacts/ctrl_structure.php";
        $this->http = $DIC->http()->wrapper();
        $this->refinery = new Factory(new \ILIAS\Data\Factory(), $DIC->language());
    }

    /**
     * @return string
     */
    protected function determineBaseClass() : string
    {
        static $base_class;
        if (!isset($base_class)) {
            if ($this->http->query()->has(self::P_BASE_CLASS)) {
                $base_class = $this->http->query()->retrieve(
                    self::P_BASE_CLASS, $this->refinery->to()->string()
                );
            } else {
                $base_class = $this->fallback_baseclass;
            }
        }

        return $base_class;
    }

    private function toCommandParameter(array $classes) : string
    {
        $base_class = $this->determineBaseClass();
        $path = [
            $this->current_cid
        ];
        $target_class = $base_class;
        foreach ($classes as $class) {
            $path[] = $this->getCidForClassname($class);
            $target_class = $class;
        }
        $parameters = [];

        $parameters[self::P_BASE_CLASS] = strtolower($this->determineBaseClass());
        $parameters[self::P_CMD_NODE] = implode(":", $path);
        $parameters[self::P_CMD_CLASS] = $target_class;
        $url = '';
        array_walk($parameters, function ($value, $key) use (&$url) {
            $url .= "&$key=$value";
        });

        return $url;
    }

    private function toClasses(string $command_parameter) : array
    {

    }

    /**
     * @inheritDoc
     */
    public function callBaseClass() : void
    {
        $base_class = $this->determineBaseClass();
        $class_info = $this->getStructureForClassName($base_class);
        $this->current_cid = $class_info[self::CID];
        $class_name = $class_info[self::CLASS_NAME];
        $base_class_gui = new $class_name();
        $this->forwardCommand($base_class_gui);
    }

    protected function getStructureForCid(string $cid) : array
    {
        return [];
    }

    protected function getStructureForClassName(string $class_name) : ?array
    {
        return $this->ctrl_structure[strtolower($class_name)] ?? null;
    }

    protected function getCidForClassname(string $class_name) : ?string
    {
        return $this->getStructureForClassName($class_name)[self::CID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getModuleDir()
    {
        // TODO: Implement getModuleDir() method.
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object)
    {
        $class_name = strtolower(get_class($a_gui_object));
        $target_cid = $this->getNodeIdForTargetClass($this->current_cid, $class_name);

        $a_gui_object->executeCommand();
    }

    private function getNodeIdForTargetClass(
        string $parent_class_cid,
        string $target_class_name,
        bool $a_check = false
    ) : string {
        $target_class_name = strtolower($target_class_name);
        $target_class_info = $this->ctrl_structure[$target_class_name];
        return $target_cid = $target_class_info[self::CID];

        $x = 1;
    }

    /**
     * @inheritDoc
     */
    public function getHTML($a_gui_object, array $a_parameters = null, array $class_path = [])
    {
        // TODO: Implement getHTML() method.
    }

    /**
     * @inheritDoc
     */
    public function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
    {
        // TODO: Implement setContext() method.
    }

    /**
     * @inheritDoc
     */
    public function getContextObjId()
    {
        // TODO: Implement getContextObjId() method.
    }

    /**
     * @inheritDoc
     */
    public function getContextObjType()
    {
        // TODO: Implement getContextObjType() method.
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjId()
    {
        // TODO: Implement getContextSubObjId() method.
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjType()
    {
        // TODO: Implement getContextSubObjType() method.
    }

    /**
     * @inheritDoc
     */
    public function checkTargetClass($a_class)
    {
        // TODO: Implement checkTargetClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getCmdNode() : string
    {

    }

    /**
     * @inheritDoc
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class)
    {
        // TODO: Implement addTab() method.
    }

    /**
     * @inheritDoc
     */
    public function getTabs()
    {
        // TODO: Implement getTabs() method.
    }

    /**
     * @inheritDoc
     */
    public function getCallHistory()
    {
        // TODO: Implement getCallHistory() method.
    }

    /**
     * @inheritDoc
     */
    public function getCallStructure($a_class)
    {
        // TODO: Implement getCallStructure() method.
    }

    /**
     * @inheritDoc
     */
    public function readCallStructure($a_class, $a_nr = 0, $a_parent = 0)
    {
        // TODO: Implement readCallStructure() method.
    }

    /**
     * @inheritDoc
     */
    public function saveParameter($a_obj, $a_parameter)
    {
        // TODO: Implement saveParameter() method.
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass($a_class, $a_parameter)
    {
        // TODO: Implement saveParameterByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function setParameter($a_obj, $a_parameter, $a_value)
    {
        // TODO: Implement setParameter() method.
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass($a_class, $a_parameter, $a_value)
    {
        // TODO: Implement setParameterByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function clearParameterByClass($a_class, $a_parameter)
    {
        // TODO: Implement clearParameterByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function clearParameters($a_obj)
    {
        // TODO: Implement clearParameters() method.
    }

    /**
     * @inheritDoc
     */
    public function clearParametersByClass($a_class)
    {
        // TODO: Implement clearParametersByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getNextClass($a_gui_class = null)
    {
        // TODO: Implement getNextClass() method.
    }

    /**
     * @inheritDoc
     */
    public function lookupClassPath($a_class_name)
    {
        // TODO: Implement lookupClassPath() method.
    }

    /**
     * @inheritDoc
     */
    public function getClassForClasspath($a_class_path)
    {
        // TODO: Implement getClassForClasspath() method.
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $a_target_script)
    {
        // TODO: Implement setTargetScript() method.
    }

    /**
     * @inheritDoc
     */
    public function getTargetScript() : string
    {
        return $this->target_script ?? 'ilias.php';
    }

    /**
     * @inheritDoc
     */
    public function initBaseClass($a_base_class)
    {
        $this->fallback_baseclass = $a_base_class;
    }

    /**
     * @inheritDoc
     */
    public function getCmd(string $fallback_command = "", array $safe_commands = []) : string
    {
        $cmd = '';
        if ($this->http->query()->has(self::P_CMD)) {
            $cmd = $this->http->query()->retrieve(
                self::P_CMD,
                $this->refinery->to()->string()
            );
        }

        if ($cmd === "post") {
            // Handle POST
        }
        if ($cmd === "") {
            $cmd = $fallback_command;
        }
        return $cmd;
    }

    /**
     * @inheritDoc
     */
    public function setCmd($a_cmd)
    {
        // TODO: Implement setCmd() method.
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass($a_cmd_class)
    {
        // TODO: Implement setCmdClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass()
    {
        // TODO: Implement getCmdClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getFormAction(
        $a_gui_obj,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        // TODO: Implement getFormAction() method.
    }

    /**
     * @inheritDoc
     */
    public function getFormActionByClass(
        $a_class,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        // TODO: Implement getFormActionByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function appendRequestTokenParameterString($a_url, $xml_style = false)
    {
        // TODO: Implement appendRequestTokenParameterString() method.
    }

    /**
     * @inheritDoc
     */
    public function getRequestToken()
    {
        // TODO: Implement getRequestToken() method.
    }

    /**
     * @inheritDoc
     */
    public function redirect($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        // TODO: Implement redirect() method.
    }

    /**
     * @inheritDoc
     */
    public function redirectToURL($a_script)
    {
        // TODO: Implement redirectToURL() method.
    }

    /**
     * @inheritDoc
     */
    public function redirectByClass($a_class, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        // TODO: Implement redirectByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function isAsynch()
    {
        // TODO: Implement isAsynch() method.
    }

    /**
     * @inheritDoc
     */
//    public function getLinkTarget($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false, $xml_style = false)
//    {
//
//    }

    /**
     * @inheritDoc
     */
    public function getLinkTargetByClass(
        $a_class,
        $a_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) : string {
        if ($a_asynch) {
            $xml_style = false;

        }

        $script = $this->getTargetScript() . "?";
        $script .= $this->toCommandParameter(is_array($a_class) ? $a_class : [$a_class]);
        //$script = $this->getUrlParameters($a_class, $script, $a_cmd, $xml_style);

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
     * @inheritDoc
     */
    public function setReturn($a_gui_obj, $a_cmd)
    {
        // TODO: Implement setReturn() method.
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass($a_class, $a_cmd)
    {
        // TODO: Implement setReturnByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function returnToParent($a_gui_obj, $a_anchor = "")
    {
        // TODO: Implement returnToParent() method.
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn($a_gui_obj)
    {
        // TODO: Implement getParentReturn() method.
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass($a_class)
    {
        // TODO: Implement getParentReturnByClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getReturnClass($a_class)
    {
        // TODO: Implement getReturnClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource()
    {
        // TODO: Implement getRedirectSource() method.
    }

    /**
     * @inheritDoc
     */
    public function getUrlParameters($a_class, $a_str, $a_cmd = "", $xml_style = false)
    {
        $params = $this->getParameterArrayByClass($a_class, $a_cmd);

        foreach ($params as $par => $value) {
            if ((string) $value !== '') {
                $a_str = ilUtil::appendUrlParameterString($a_str, $par . "=" . $value, $xml_style);
            }
        }

        return $a_str;
    }

    /**
     * @inheritDoc
     */
    public function getParameterArray($a_gui_obj, $a_cmd = "")
    {
        // TODO: Implement getParameterArray() method.
    }

    /**
     * @inheritDoc
     */
    public function getParameterArrayByClass($classes, $a_cmd = "")
    {
        return [];

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

        $params[self::P_CMD_CLASS] = $target_class;
        $params[self::P_CMD_NODE] = $nr;
        if ($new_baseclass == "") {
            $params["baseClass"] = $current_base_class;
        } else {
            $params["baseClass"] = $new_baseclass;
        }

        return $params;
    }

    /**
     * @inheritDoc
     */
    public function insertCtrlCalls($a_parent, $a_child, $a_comp_prefix)
    {
        // TODO: Implement insertCtrlCalls() method.
    }

    /**
     * @inheritDoc
     */
    public function checkCurrentPathForClass($gui_class)
    {
        // TODO: Implement checkCurrentPathForClass() method.
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath() : array
    {
        // TODO: Implement getCurrentClassPath() method.
    }

}
