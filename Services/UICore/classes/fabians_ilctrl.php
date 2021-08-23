<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Refinery\Factory;

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer.raimann.ch>
 */
class ilCtrl implements ilCtrlInterface
{
    const P_BASE_CLASS = 'baseClass';
    const P_CMD = 'cmd';
    const P_CMD_NODE = 'cmdNode';
    const P_CMD_CLASS = 'cmdClass';
    const CID = 'cid';
    const CLASS_NAME = 'class_name';

    private array $ctrl_structure;
    protected string $fallback_baseclass;
    protected string $current_cid;

    /**
     * @var \ILIAS\HTTP\Wrapper\WrapperFactory
     */
    private \ILIAS\HTTP\Wrapper\WrapperFactory $http;

    /**
     * @var Factory
     */
    private \ILIAS\Refinery\Factory $refinery;


    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http()->wrapper();

        $this->ctrl_structure = include "./Services/UICore/artifacts/ctrl_structure.php";
        $this->refinery = new Factory(new \ILIAS\Data\Factory(), $DIC->language());
    }

    /**
     * @return string
     */
    private function determineBaseClass() : string
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
    public function forwardCommand(object $a_gui_object)
    {
        $class_name = strtolower(get_class($a_gui_object));
        $target_cid = $this->getNodeIdForTargetClass($this->current_cid, $class_name);

        $a_gui_object->executeCommand();
    }


    public function getTargetScript() : string
    {
        return $this->target_script ?? 'ilias.php';
    }


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
    public function getFormAction(
        $a_gui_obj,
        $a_fallback_cmd = "",
        $a_anchor = "",
        $a_asynch = false,
        $xml_style = false
    ) {
        throw new ilException('not implemented');
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
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function appendRequestTokenParameterString($a_url, $xml_style = false)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getRequestToken()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function redirect($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function redirectToURL($a_script)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function redirectByClass($a_class, $a_cmd = "", $a_anchor = "", $a_asynch = false)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function isAsynch()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getLinkTarget($a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false, $xml_style = false)
    {
        throw new ilException('not implemented');
    }

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
    public function getUrlParameters($a_class, $a_str, string $a_cmd = "", bool $xml_style = false)
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
        throw new ilException('not implemented');
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































    //
    //
    //
    // TODO
    //
    //
    //
    //
    /**
     * @inheritDoc
     */
    public function getModuleDir()
    {
        throw new ilException('not implemented');
    }
    /**
     * @inheritDoc
     */
    public function getHTML($a_gui_object, array $a_parameters = null, array $class_path = [])
    {
        throw new ilException('not implemented');
    }


    /**
     * @inheritDoc
     */
    public function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getContextObjId()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getContextObjType()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjId()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjType()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function checkTargetClass($a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getCmdNode() : string
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getTabs()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getCallHistory()
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getCallStructure($a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function readCallStructure($a_class, $a_nr = 0, $a_parent = 0)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function saveParameter($a_obj, $a_parameter)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass($a_class, $a_parameter)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setParameter($a_obj, $a_parameter, $a_value)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass($a_class, $a_parameter, $a_value)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function clearParameterByClass($a_class, $a_parameter)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function clearParameters($a_obj)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function clearParametersByClass($a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getNextClass($a_gui_class = null)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function lookupClassPath($a_class_name)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getClassForClasspath($a_class_path)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $a_target_script)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setCmd($a_cmd)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass($a_cmd_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass()
    {
        throw new ilException('not implemented');
    }



    /**
     * @inheritDoc
     */
    public function setReturn($a_gui_obj, $a_cmd)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass($a_class, $a_cmd)
    {
        throw new ilException('not implemented');
    }



    /**
     * @inheritDoc
     */
    public function returnToParent($a_gui_obj, $a_anchor = "")
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn($a_gui_obj)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass($a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getReturnClass($a_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource()
    {
        throw new ilException('not implemented');
    }


    /**
     * @inheritDoc
     */
    public function insertCtrlCalls($a_parent, $a_child, $a_comp_prefix)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function checkCurrentPathForClass($gui_class)
    {
        throw new ilException('not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath() : array
    {
        throw new ilException('not implemented');
    }



















}
