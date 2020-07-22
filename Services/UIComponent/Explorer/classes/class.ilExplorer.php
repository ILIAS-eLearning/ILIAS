<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_FM_POSITIVE", 1);
define("IL_FM_NEGATIVE", 2);

/**
* Class ilExplorer
* class for explorer view in admin frame
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

class ilExplorer
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $id;

    /**
    * output
    * @var string
    * @access public
    */
    public $output;

    /**
    * contains format options
    * @var array
    * @access public
    */
    public $format_options;

    /**
    * tree
    * @var object Tree
    * @access public
    */
    public $tree;

    /**
    * target
    * @var string
    * @access public
    */
    public $target;

    /**
    * target get parameter
    * @var string
    * @access public
    */
    public $target_get;

    /**
    * additional get parameter
    * @var string
    * @access public
    */
    public $params_get;

    /**
    * expanded
    * @var array
    * @access public
    */
    public $expanded;

    /**
    * order column
    * @var string
    * @access private
    */
    public $order_column;

    /**
    * order direction
    * @var string
    * @access private
    */
    public $order_direction = "asc";

    /**
    * target script for expand icon links
    * @var string
    * @access private
    */
    public $expand_target;

    /**
    * rbac check true/false (default true)
    * @var boolean
    * @access private
    */
    public $rbac_check;


    /**
    * output icons true/false (default true)
    * @var boolean
    * @access private
    */
    public $output_icons;

    /**
    * name of session expand variable
    * @var boolean
    * @access private
    */
    public $expand_variable;

    /**
    * array ($type => clickable (empty means true, "n" means false)
    * @var array
    * @access private
    */
    public $is_clickable;

    /**
    * process post sorting true/false
    * @var boolean
    * @access private
    */
    public $post_sort;

    /**
    * set object type filter true/false
    * @var boolean
    * @access private
    */
    public $filtered = false;

    /**
    * set filter mode
    * @var boolean
    * @access private
    */
    public $filter_mode;

    /**
    * expand entire tree regardless of values in $expanded
    * @var boolean
    * @access private
    */
    public $expand_all = false;
    
    /**
    * Root id. One can set it using setRoot
    * @var boolean
    * @access private
    */
    public $root_id = null;
    
    public $use_standard_frame = false;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    */
    public function __construct($a_target)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->error = $DIC["ilErr"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilErr = $DIC["ilErr"];

        if (!isset($a_target) or !is_string($a_target)) {
            $ilErr->raiseError(get_class($this) . "::Constructor(): No target given!", $ilErr->WARNING);
        }

        // autofilter object types in devmode
        $devtypes = $objDefinition->getDevModeAll();

        if (count($devtypes) > 0) {
            // activate filter if objects found in devmode
            $this->setFiltered(true);

            foreach ($devtypes as $type) {
                $this->addFilter($type);
            }
        }

        $this->ilias = $DIC["ilias"];
        $this->output = array();
        $this->expanded = array();
        $this->target = $a_target;
        $this->target_get = 'ref_id';
        $this->frame_target = "content";
        $this->order_column = "title";
        $this->tree = new ilTree(ROOT_FOLDER_ID);
        $this->tree->initLangCode();
        $this->expand_target = $_SERVER["PATH_INFO"];
        $this->rbac_check = true;
        $this->output_icons = true;
        $this->expand_variable = "expand";
        $this->setTitleLength(50);
        $this->post_sort = true;
        $this->setFilterMode(IL_FM_NEGATIVE);
        $this->highlighted = "";
        $this->show_minus = true;
        $this->counter = 0;
        $this->asnch_expanding = false;
    }

    /**
     * Set id
     *
     * @param string $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get id
     *
     * @return string id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set asynch expanding
     *
     * @param boolean
     */
    public function setAsynchExpanding($a_val)
    {
        $this->asnch_expanding = $a_val;
    }

    /**
     * Get asynch expanding
     *
     * @return boolean
     */
    public function getAsynchExpanding()
    {
        return $this->asnch_expanding;
    }

    /**
     * Init item counter
     *
     * @access public
     * @param int number
     *
     */
    public function initItemCounter($a_number)
    {
        $this->counter = $a_number;
    }
    
    /**
    * Set title
    *
    * @param	title
    */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Set max title length
     * @param object $a_length
     * @return
     */
    public function setTitleLength($a_length)
    {
        $this->textwidth = $a_length;
    }
    
    /**
     * Get max title length
     * @return
     */
    public function getTitleLength()
    {
        return $this->textwidth;
    }
    
    /**
    * Get title
    *
    * @return	title
    */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set root node
     *
     * @access public
     * @param int ref id of root node
     *
     */
    public function setRoot($a_root_id)
    {
        #$this->tree = new ilTree(ROOT_FOLDER_ID,$a_root_id);
        $this->root_id = $a_root_id;
    }
    
    /**
     * get root id
     *
     * @access public
     * @param
     *
     */
    public function getRoot()
    {
        return $this->root_id == null ?
            $this->tree->getRootId() :
            $this->root_id;
    }

    /**
    * set the order column
    * @access	public
    * @param	string		name of order column
    */
    public function setOrderColumn($a_column)
    {
        $this->order_column = $a_column;
    }

    /**
    * set the order direction
    * @access	public
    * @param	string		name of order column
    */
    public function setOrderDirection($a_direction)
    {
        if ($a_direction == "desc") {
            $this->order_direction = $a_direction;
        } else {
            $this->order_direction = "asc";
        }
    }

    /**
    * set the varname in Get-string
    * @access	public
    * @param	string		varname containing Ids to be used in GET-string
    */
    public function setTargetGet($a_target_get)
    {
        $ilErr = $this->error;

        if (!isset($a_target_get) or !is_string($a_target_get)) {
            $ilErr->raiseError(get_class($this) . "::setTargetGet(): No target given!", $ilErr->WARNING);
        }

        $this->target_get = $a_target_get;
    }

    /**
    * set additional params to be passed in Get-string
    * @access	public
    * @param	array
    */
    public function setParamsGet($a_params_get)
    {
        $ilErr = $this->error;

        if (!isset($a_params_get) or !is_array($a_params_get)) {
            $ilErr->raiseError(get_class($this) . "::setTargetGet(): No target given!", $ilErr->WARNING);
        }
        $str = "";
        foreach ($a_params_get as $key => $val) {
            $str .= "&" . $key . "=" . $val;
        }

        $this->params_get = $str;
    }


    /**
    * target script for expand icons
    *
    * @param	string		$a_exp_target	script name of target script(may include parameters)
    *										initially set to $_SERVER["PATH_INFO"]
    */
    public function setExpandTarget($a_exp_target)
    {
        $this->expand_target = $a_exp_target;
    }
    
    /**
    * Set Explorer Updater
    *
    * @param	object	$a_tree	Tree Object
    */
    public function setFrameUpdater($a_up_frame, $a_up_script, $a_params = "")
    {
        $this->up_frame = $a_up_frame;
        $this->up_script = $a_up_script;
        $this->up_params = $a_params;
    }

    
    /**
    * set highlighted node
    */
    public function highlightNode($a_id)
    {
        $this->highlighted = $a_id;
    }

    /**
    * check permissions via rbac
    *
    * @param	boolean		$a_check		check true/false
    */
    public function checkPermissions($a_check)
    {
        $this->rbac_check = $a_check;
    }

    /**
    * set name of expand session variable
    *
    * @param	string		$a_var_name		variable name
    */
    public function setSessionExpandVariable($a_var_name = "expand")
    {
        $this->expand_variable = $a_var_name;
    }

    /**
    * output icons
    *
    * @param	boolean		$a_icons		output icons true/false
    */
    public function outputIcons($a_icons)
    {
        $this->output_icons = $a_icons;
    }


    /**
    * (de-)activates links for a certain object type
    *
    * @param	string		$a_type			object type
    * @param	boolean		$a_clickable	true/false
    */
    public function setClickable($a_type, $a_clickable)
    {
        if ($a_clickable) {
            $this->is_clickable[$a_type] = "";
        } else {
            $this->is_clickable[$a_type] = "n";
        }
    }

    public function isVisible($a_ref_id, $a_type)
    {
        $rbacsystem = $this->rbacsystem;
        
        if (!$this->rbac_check) {
            return true;
        }
        
        $visible = $rbacsystem->checkAccess('visible', $a_ref_id);

        return $visible;
    }

    /**
     * Set tree leading content
     *
     * @param	string	$a_val	tree leading content
     */
    public function setTreeLead($a_val)
    {
        $this->tree_lead = $a_val;
    }

    /**
     * Get tree leading content
     *
     * @return	string	tree leading content
     */
    public function getTreeLead()
    {
        return $this->tree_lead;
    }

    /**
    * check if links for certain object type are activated
    *
    * @param	string		$a_type			object type
    *
    * @return	boolean		true if linking is activated
    */
    public function isClickable($a_type, $a_ref_id = 0)
    {
        // in this standard implementation
        // only the type determines, wether an object should be clickable or not
        // but this method can be overwritten and make $exp->setFilterMode(IL_FM_NEGATIVE);use of the ref id
        // (this happens e.g. in class ilRepositoryExplorerGUI)
        if ($this->is_clickable[$a_type] == "n") {
            return false;
        } else {
            return true;
        }
    }

    /**
    * process post sorting
    * @param	boolean		$a_sort		true / false
    */
    public function setPostSort($a_sort)
    {
        $this->post_sort = $a_sort;
    }

    /**
    * set filter mode
    *
    * @param	int		$a_mode		filter mode IL_FM_NEGATIVE | IL_FM_NEGATIVE
    */
    public function setFilterMode($a_mode = IL_FM_NEGATIVE)
    {
        $this->filter_mode = $a_mode;
    }

    /**
    * get filter mode
    *
    * @return	int		filter mode IL_FM_NEGATIVE | IL_FM_NEGATIVE
    */
    public function getFilterMode()
    {
        return $this->filter_mode;
    }

    /**
    * Set use standard frame. If true, the standard
    * explorer frame (like in the repository) is put around the tree.
    *
    * @param	boolean		use standard explorer frame
    */
    public function setUseStandardFrame($a_val)
    {
        $this->use_standard_frame = $a_val;
    }
    
    /**
    * Get use standard explorer frame
    *
    * @return	boolean		use standard explorer frame
    */
    public function getUseStandardFrame()
    {
        return $this->use_standard_frame;
    }
    
    /**
     * Get childs of node
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_id)
    {
        return $this->tree->getChilds($a_parent_id, $this->order_column);
    }
    
    
    /**
    * Creates output for explorer view in admin menue
    * recursive method
    * @access	public
    * @param	integer		parent_node_id where to start from (default=0, 'root')
    * @param	integer		depth level where to start (default=1)
    * @return	string
    */
    public function setOutput($a_parent_id, $a_depth = 1, $a_obj_id = 0, $a_highlighted_subtree = false)
    {
        $ilErr = $this->error;

        if (!isset($a_parent_id)) {
            $ilErr->raiseError(get_class($this) . "::setOutput(): No node_id given!", $ilErr->WARNING);
        }

        if ($this->showChilds($a_parent_id, $a_obj_id)) {
            $objects = $this->getChildsOfNode($a_parent_id);
        } else {
            $objects = array();
        }

        $objects = $this->modifyChilds($a_parent_id, $objects);

        // force expansion (of single nodes)
        if ($this->forceExpanded($a_parent_id) && !in_array($a_parent_id, $this->expanded)) {
            $this->expanded[] = $a_parent_id;
        }

        if (count($objects) > 0) {
            // Maybe call a lexical sort function for the child objects
            $tab = ++$a_depth - 2;
            if ($this->post_sort) {
                $objects = $this->sortNodes($objects, $a_obj_id);
            }
            $skip_rest = false;
            foreach ($objects as $key => $object) {
                // skip childs, if parent is not expanded
                if (!$this->forceExpanded($object["child"]) && $skip_rest) {
                    continue;
                }
                //echo "<br>-".$object["child"]."-".$this->forceExpanded($object["child"])."-";
                //ask for FILTER
                if ($this->filtered == false or $this->checkFilter($object["type"]) == false) {
                    if ($this->isVisible($object['child'], $object['type'])) {
                        #echo 'CHILD getIndex() '.$object['child'].' parent: '.$this->getRoot();
                        if ($object["child"] != $this->getRoot()) {
                            $parent_index = $this->getIndex($object);
                        }
                        $this->format_options["$this->counter"]["parent"] = $object["parent"];
                        $this->format_options["$this->counter"]["child"] = $object["child"];
                        $this->format_options["$this->counter"]["title"] = $object["title"];
                        $this->format_options["$this->counter"]["type"] = $object["type"];
                        $this->format_options["$this->counter"]["obj_id"] = $object["obj_id"];
                        $this->format_options["$this->counter"]["desc"] = "obj_" . $object["type"];
                        $this->format_options["$this->counter"]["depth"] = $tab;
                        $this->format_options["$this->counter"]["container"] = false;
                        $this->format_options["$this->counter"]["visible"] = true;
                        $this->format_options["$this->counter"]["highlighted_subtree"] = $a_highlighted_subtree;

                        // Create prefix array
                        for ($i = 0; $i < $tab; ++$i) {
                            $this->format_options["$this->counter"]["tab"][] = 'blank';
                        }

                        // fix explorer (sometimes explorer disappears)
                        if ($parent_index == 0) {
                            if (!$this->expand_all and !in_array($object["parent"], $this->expanded)) {
                                $this->expanded[] = $object["parent"];
                            }
                        }

                        // only if parent is expanded and visible, object is visible
                        if ($object["child"] != $this->getRoot() and ((!$this->expand_all and !in_array($object["parent"], $this->expanded))
                           or !$this->format_options["$parent_index"]["visible"])) {
                            if (!$this->forceExpanded($object["child"])) {
                                // if parent is not expanded, and one child is
                                // visible we don't need more information and
                                // can skip the rest of the childs
                                if ($this->format_options["$this->counter"]["visible"]) {
                                    //echo "-setSkipping";
                                    $skip_rest = true;
                                }
                                $this->format_options["$this->counter"]["visible"] = false;
                            }
                        }

                        // if object exists parent is container
                        if ($object["child"] != $this->getRoot()) {
                            $this->format_options["$parent_index"]["container"] = true;

                            if ($this->expand_all or in_array($object["parent"], $this->expanded)) {
                                //echo "<br>-".$object["child"]."-".$this->forceExpanded($object["child"])."-";
                                if ($this->forceExpanded($object["parent"])) {
                                    $this->format_options["$parent_index"]["tab"][($tab - 2)] = 'forceexp';
                                } else {
                                    $this->format_options["$parent_index"]["tab"][($tab - 2)] = 'minus';
                                }
                            } else {
                                $this->format_options["$parent_index"]["tab"][($tab - 2)] = 'plus';
                            }
                        }
                        //echo "-"."$parent_index"."-";
                        //var_dump($this->format_options["$parent_index"]);
                        ++$this->counter;

                        // stop recursion if 2. level beyond expanded nodes is reached
                        if ($this->expand_all or in_array($object["parent"], $this->expanded) or ($object["parent"] == 0)
                            or $this->forceExpanded($object["child"])) {
                            $highlighted_subtree = ($a_highlighted_subtree ||
                                ($object["child"] == $this->highlighted))
                                ? true
                                : false;
                            
                            // recursive
                            $this->setOutput($object["child"], $a_depth, $object['obj_id'], $highlighted_subtree);
                        }
                    } //if
                } //if FILTER
            } //foreach
        } //if
    } //function

    public function modifyChilds($a_parent_id, $a_objects)
    {
        return $a_objects;
    }

    /**
    * determines wether the childs of an object should be shown or not
    * note: this standard implementation always returns true
    * but it could be overwritten by derived classes (e.g. ilRepositoryExplorerGUI)
    */
    public function showChilds($a_parent_id)
    {
        return true;
    }

    /**
    * force expansion of node
    */
    public function forceExpanded($a_obj_id)
    {
        return false;
    }

    /**
     * Get maximum tree depth
     *
     * @param
     * @return
     */
    public function getMaximumTreeDepth()
    {
        $this->tree->getMaximumDepth();
    }
    
    
    /**
    * Creates output
    * recursive method
    * @access	public
    * @return	string
    */
    public function getOutput()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->format_options[0]["tab"] = array();

        $depth = $this->getMaximumTreeDepth();

        for ($i = 0;$i < $depth;++$i) {
            $this->createLines($i);
        }

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initConnection();
        $tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

        //echo "hh";
        // set global body class
        //		$tpl->setBodyClass("il_Explorer");
        
        $tpl_tree = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");
        
        // updater
        if (($_GET["ict"] || $_POST["collapseAll"] != "" || $_POST["expandAll"] != "") && $this->up_frame != "") {
            $tpl_tree->setCurrentBlock("updater");
            $tpl_tree->setVariable("UPDATE_FRAME", $this->up_frame);
            $tpl_tree->setVariable("UPDATE_SCRIPT", $this->up_script);
            if (is_array($this->up_params)) {
                $up_str = $lim = "";
                foreach ($this->up_params as $p) {
                    $up_str .= $lim . "'" . $p . "'";
                    $lim = ",";
                }
                $tpl_tree->setVariable("UPDATE_PARAMS", $up_str);
            }
            $tpl_tree->parseCurrentBlock();
        }
        
        $cur_depth = -1;
        foreach ($this->format_options as $key => $options) {
            //echo "-".$options["depth"]."-";
            if (!$options["visible"]) {
                continue;
            }
            
            // end tags
            $this->handleListEndTags($tpl_tree, $cur_depth, $options["depth"]);
            
            // start tags
            $this->handleListStartTags($tpl_tree, $cur_depth, $options["depth"]);
            
            $cur_depth = $options["depth"];
            
            if ($options["visible"] and $key != 0) {
                $this->formatObject($tpl_tree, $options["child"], $options, $options['obj_id']);
            }
            if ($key == 0) {
                $this->formatHeader($tpl_tree, $options["child"], $options);
            }
        }

        $this->handleListEndTags($tpl_tree, $cur_depth, -1);
        
        $tpl_tree->setVariable("TREE_LEAD", "");
        if ($this->tree_lead != "") {
            $tpl_tree->setCurrentBlock("tree_lead");
            $tpl_tree->setVariable("TREE_LEAD", $this->tree_lead);
            $tpl_tree->parseCurrentBlock();
        }
        if ($this->getId() != "") {
            $tpl_tree->setVariable("TREE_ID", 'id="' . $this->getId() . '_tree"');
        }

        $html = $tpl_tree->get();
        
        if ($this->getUseStandardFrame()) {
            $mtpl = new ilTemplate("tpl.main.html", true, true);
            $mtpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
            $mtpl->setVariable("BODY_CLASS", "il_Explorer");
            $mtpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
            if ($this->getTitle() != "") {
                $mtpl->setVariable("TXT_EXPLORER_HEADER", $this->getTitle());
            }
            if ($this->getId() != "") {
                $mtpl->setVariable("ID", 'id="' . $this->getId() . '"');
            }
            $mtpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
            $mtpl->setCurrentBlock("content");
            $mtpl->setVariable("EXPLORER", $html);
            $mtpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
            $mtpl->parseCurrentBlock();
            $html = $mtpl->get();
        }
        
        return $html;
    }
    
    
    
    /**
    * handle list end tags (</li> and </ul>)
    */
    public function handleListEndTags(&$a_tpl_tree, $a_cur_depth, $a_item_depth)
    {
        if ($a_item_depth < $a_cur_depth) {
            // </li></ul> for ending lists
            for ($i = 0; $i < ($a_cur_depth - $a_item_depth); $i++) {
                $a_tpl_tree->touchBlock("end_list_item");
                $a_tpl_tree->touchBlock("element");

                $a_tpl_tree->touchBlock("end_list");
                $a_tpl_tree->touchBlock("element");
            }
        } elseif ($a_item_depth == $a_cur_depth) {
            // </li> for ending list items
            $a_tpl_tree->touchBlock("end_list_item");
            $a_tpl_tree->touchBlock("element");
        }
    }
    
    /**
    * handle list start tags (<ul> and <li>)
    */
    public function handleListStartTags(&$a_tpl_tree, $a_cur_depth, $a_item_depth)
    {
        // start tags
        if ($a_item_depth > $a_cur_depth) {
            // <ul><li> for new lists
            if ($a_item_depth > 1) {
                $a_tpl_tree->touchBlock("start_list");
            } else {
                $a_tpl_tree->touchBlock("start_list_no_indent");
            }
            $a_tpl_tree->touchBlock("element");
            
            $a_tpl_tree->touchBlock("start_list_item");
            $a_tpl_tree->touchBlock("element");
        } else {
            // <li> items
            $a_tpl_tree->touchBlock("start_list_item");
            $a_tpl_tree->touchBlock("element");
        }
    }

    /**
    * Creates output for header
    * (is empty here but can be overwritten in derived classes)
    *
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
    {
    }

    /**
    * Creates output
    * recursive method
    * @access	private
    * @param	integer
    * @param	array
    * @return	string
    */
    public function formatObject($tpl, $a_node_id, $a_option, $a_obj_id = 0)
    {
        $lng = $this->lng;
        $ilErr = $this->error;

        if (!isset($a_node_id) or !is_array($a_option)) {
            $ilErr->raiseError(get_class($this) . "::formatObject(): Missing parameter or wrong datatype! " .
                                    "node_id: " . $a_node_id . " options:" . var_dump($a_option), $ilErr->WARNING);
        }

        $pic = false;
        foreach ((array) $a_option["tab"] as $picture) {
            if ($picture == 'plus') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("collapsed"));
                $tpl->setVariable("LINK_NAME", $a_node_id);
                if (!$this->getAsynchExpanding()) {
                    $target = $this->createTarget('+', $a_node_id, $a_option["highlighted_subtree"]);
                    $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                } else {
                    $target = $this->createTarget('+', $a_node_id, $a_option["highlighted_subtree"], false);
                    $tpl->setVariable("ONCLICK_TARGET_EXPANDER", " onclick=\"return il.Explorer.refresh('tree_div', '" . $target . "');\"");
                    $tpl->setVariable("LINK_TARGET_EXPANDER", "#");
                }
                $tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture == 'forceexp') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
                $target = $this->createTarget('+', $a_node_id);
                $tpl->setVariable("LINK_NAME", $a_node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/forceexp.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture == 'minus' && $this->show_minus) {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
                $tpl->setVariable("LINK_NAME", $a_node_id);
                if (!$this->getAsynchExpanding()) {
                    $target = $this->createTarget('-', $a_node_id, $a_option["highlighted_subtree"]);
                    $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                } else {
                    $target = $this->createTarget('-', $a_node_id, $a_option["highlighted_subtree"], false);
                    $tpl->setVariable("ONCLICK_TARGET_EXPANDER", " onclick=\"return il.Explorer.refresh('tree_div', '" . $target . "');\"");
                    $tpl->setVariable("LINK_TARGET_EXPANDER", "#");
                }
                $tpl->setVariable("IMGPATH", $this->getImage("browser/minus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }
        }
        
        if (!$pic) {
            $tpl->setCurrentBlock("blank");
            $tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.png"));
            $tpl->parseCurrentBlock();
        }

        if ($this->output_icons) {
            $tpl->setCurrentBlock("icon");
            $tpl->setVariable("ICON_IMAGE", $this->getImage("icon_" . $a_option["type"] . ".svg", $a_option["type"], $a_obj_id));
            
            $tpl->setVariable("TARGET_ID", "iconid_" . $a_node_id);
            $this->iconList[] = "iconid_" . $a_node_id;
            $tpl->setVariable(
                "TXT_ALT_IMG",
                $this->getImageAlt($lng->txt("icon") . " " . $lng->txt($a_option["desc"]), $a_option["type"], $a_obj_id)
            );
            $tpl->parseCurrentBlock();
        }
        
        if (strlen($sel = $this->buildSelect($a_node_id, $a_option['type']))) {
            $tpl->setCurrentBlock('select');
            $tpl->setVariable('OBJ_SEL', $sel);
            $tpl->parseCurrentBlock();
        }

        if ($this->isClickable($a_option["type"], $a_node_id, $a_obj_id)) {	// output link
            $tpl->setCurrentBlock("link");
            //$target = (strpos($this->target, "?") === false) ?
            //	$this->target."?" : $this->target."&";
            //$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
            $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
                
            $style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
            
            if ($style_class != "") {
                $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
            }

            if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "") {
                $tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
            }

            //$tpl->setVariable("LINK_NAME", $a_node_id);
            $tpl->setVariable("TITLE", ilUtil::shortenText(
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $tpl->setVariable("DESC", ilUtil::shortenText(
                $this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
            if ($frame_target != "") {
                $tpl->setVariable("TARGET", " target=\"" . $frame_target . "\"");
            }
            $tpl->parseCurrentBlock();
        } else {			// output text only
            $tpl->setCurrentBlock("text");
            $tpl->setVariable("OBJ_TITLE", ilUtil::shortenText(
                $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $tpl->setVariable("OBJ_DESC", ilUtil::shortenText(
                $this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]),
                $this->textwidth,
                true
            ));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("list_item");
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("element");
    }

    /**
    * get image path (may be overwritten by derived classes)
    */
    public function getImage($a_name, $a_type = "", $a_obj_id = "")
    {
        return ilUtil::getImagePath($a_name);
    }
    
    /**
    * get image alt text
    */
    public function getImageAlt($a_default_text, $a_type = "", $a_obj_id = "")
    {
        return $a_default_text;
    }
    
    /**
    * get style class for node
    */
    public function getNodeStyleClass($a_id, $a_type)
    {
        if ($a_id == $this->highlighted) {
            return "il_HighlightedNode";
        }
        return "";
    }

    /**
    * get link target (may be overwritten by derived classes)
    */
    public function buildLinkTarget($a_node_id, $a_type)
    {
        $target = (strpos($this->target, "?") === false)
            ? $this->target . "?"
            : $this->target . "&";
        return $target . $this->target_get . "=" . $a_node_id . $this->params_get;
    }

    /**
    * get onclick event handling (may be overwritten by derived classes)
    */
    public function buildOnClick($a_node_id, $a_type, $a_title)
    {
        return "";
    }

    /**
    * standard implementation for title, may be overwritten by derived classes
    */
    public function buildTitle($a_title, $a_id, $a_type)
    {
        return $a_title;
    }

    /**
    * standard implementation for description, may be overwritten by derived classes
    */
    public function buildDescription($a_desc, $a_id, $a_type)
    {
        return "";
    }
    
    /**
    * standard implementation for adding an option select box between image and title
    */
    public function buildSelect($a_node_id, $a_type)
    {
        return "";
    }
    

    /**
    * get frame target (may be overwritten by derived classes)
    */
    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return $this->frame_target;
    }


    /**
    * Creates Get Parameter
    * @access	private
    * @param	string
    * @param	integer
    * @return	string
    */
    public function createTarget($a_type, $a_node_id, $a_highlighted_subtree = false, $a_append_anch = true)
    {
        $ilErr = $this->error;

        if (!isset($a_type) or !is_string($a_type) or !isset($a_node_id)) {
            $ilErr->raiseError(get_class($this) . "::createTarget(): Missing parameter or wrong datatype! " .
                                    "type: " . $a_type . " node_id:" . $a_node_id, $ilErr->WARNING);
        }

        // SET expand parameter:
        //     positive if object is expanded
        //     negative if object is compressed
        $a_node_id = $a_type == '+' ? $a_node_id : -(int) $a_node_id;

        $sep = (is_int(strpos($this->expand_target, "?")))
            ? "&"
            : "?";
        
        // in current tree flag
        $ict_str = ($a_highlighted_subtree || $this->highlighted == "")
            ? "&ict=1"
            : "";
        if ($this->getAsynchExpanding()) {
            $ict_str .= "&cmdMode=asynch";
        }
        if ($a_append_anch) {
            return $this->expand_target . $sep . $this->expand_variable . "=" . $a_node_id . $this->params_get . $ict_str . "#" . abs($a_node_id);
        } else {
            return $this->expand_target . $sep . $this->expand_variable . "=" . $a_node_id . $this->params_get . $ict_str;
        }
    }

    /**
    * set target
    * frame or not frame?
    * @param	string
    * @access	public
    */
    public function setFrameTarget($a_target)
    {
        $this->frame_target = $a_target;
    }

    /**
    * Creates lines for explorer view
    * @access	private
    * @param	integer
    */
    public function createLines($a_depth)
    {
        for ($i = 0; $i < count($this->format_options); ++$i) {
            if ($this->format_options[$i]["depth"] == $a_depth + 1
               and !$this->format_options[$i]["container"]
                and $this->format_options[$i]["depth"] != 1) {
                $this->format_options[$i]["tab"]["$a_depth"] = "quer";
            }

            if ($this->format_options[$i]["depth"] == $a_depth + 2) {
                if ($this->is_in_array($i + 1, $this->format_options[$i]["depth"])) {
                    $this->format_options[$i]["tab"]["$a_depth"] = "winkel";
                } else {
                    $this->format_options[$i]["tab"]["$a_depth"] = "ecke";
                }
            }

            if ($this->format_options[$i]["depth"] > $a_depth + 2) {
                if ($this->is_in_array($i + 1, $a_depth + 2)) {
                    $this->format_options[$i]["tab"]["$a_depth"] = "hoch";
                }
            }
        }
    }

    /**
    * DESCRIPTION MISSING
    * @access	private
    * @param	integer
    * @param	integer
    * @return	boolean
    */
    public function is_in_array($a_start, $a_depth)
    {
        for ($i = $a_start;$i < count($this->format_options);++$i) {
            if ($this->format_options[$i]["depth"] < $a_depth) {
                break;
            }

            if ($this->format_options[$i]["depth"] == $a_depth) {
                return true;
            }
        }
        return false;
    }

    /**
    * get index of format_options array from specific ref_id,parent_id
    * @access	private
    * @param	array		object data
    * @return	integer		index
    **/
    public function getIndex($a_data)
    {
        if (!is_array($this->format_options)) {
            return -1;
        }
        
        foreach ($this->format_options as $key => $value) {
            if (($value["child"] == $a_data["parent"])) {
                return $key;
            }
        }
        
        return -1;
    }

    /**
    * adds item to the filter
    * @access	public
    * @param	string		object type to add
    * @return	boolean
    */
    public function addFilter($a_item)
    {
        $ispresent = 0;

        if (is_array($this->filter)) {
            //run through filter
            foreach ($this->filter as $item) {
                if ($item == $a_item) {
                    $is_present = 1;

                    return false;
                }
            }
        } else {
            $this->filter = array();
        }
        if ($is_present == 0) {
            $this->filter[] = $a_item;
        }

        return true;
    }

    /**
    * removes item from the filter
    * @access	public
    * @param	string		object type to remove
    * @return	boolean
    */
    public function delFilter($a_item)
    {
        //check if a filter exists
        if (is_array($this->filter)) {
            //build copy of the existing filter without the given item
            $tmp = array();

            foreach ($this->filter as $item) {
                if ($item != $a_item) {
                    $tmp[] = $item;
                } else {
                    $deleted = 1;
                }
            }

            $this->filter = $tmp;
        } else {
            return false;
        }

        if ($deleted == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * set the expand option
    * this value is stored in a SESSION variable to save it different view (lo view, frm view,...)
    * @access	private
    * @param	string		pipe-separated integer
    */
    public function setExpand($a_node_id)
    {
        // IF ISN'T SET CREATE SESSION VARIABLE
        if (!is_array($_SESSION[$this->expand_variable])) {
            $_SESSION[$this->expand_variable] = array($this->getRoot());
        }
        // IF $_GET["expand"] is positive => expand this node
        if ($a_node_id > 0 && !in_array($a_node_id, $_SESSION[$this->expand_variable])) {
            array_push($_SESSION[$this->expand_variable], $a_node_id);
        }
        // IF $_GET["expand"] is negative => compress this node
        if ($a_node_id < 0) {
            $key = array_keys($_SESSION[$this->expand_variable], -(int) $a_node_id);
            unset($_SESSION[$this->expand_variable][$key[0]]);
        }
        $this->expanded = $_SESSION[$this->expand_variable];
    }

    /**
    * force expandAll. if true all nodes are expanded regardless of the values
    * in $expanded (default: false)
    * @access	public
    * @param	boolean
    */
    public function forceExpandAll($a_mode, $a_show_minus = true)
    {
        $this->expand_all = (bool) $a_mode;
        $this->show_minus = $a_show_minus;
    }

    /**
    * active/deactivate the filter
    * @access	public
    * @param	boolean
    * @return	boolean
    */
    public function setFiltered($a_bool)
    {
        $this->filtered = $a_bool;
        return true;
    }

    /**
    * check if item is in filter
    * @access	private
    * @param	string
    * @return	integer
    */
    public function checkFilter($a_item)
    {
        if (is_array($this->filter)) {
            if (in_array($a_item, $this->filter)) {
                $ret = true;
            } else {
                $ret = false;
            }
        } else {
            $ret = false;
        }

        if ($this->getFilterMode() == IL_FM_NEGATIVE) {
            return $ret;
        } else {
            return !$ret;
        }
    }

    /**
    * sort nodes and put adm object to the end of sorted array
    * @access	private
    * @param	array	node list as returned by iltree::getChilds();
    * @return	array	sorted nodes
    */
    public function sortNodes($a_nodes, $a_parent_obj_id)
    {
        foreach ($a_nodes as $key => $node) {
            if ($node["type"] == "adm") {
                $match = $key;
                $adm_node = $node;
                break;
            }
        }

        // cut off adm node
        isset($match) ? array_splice($a_nodes, $match, 1) : "";

        $a_nodes = ilUtil::sortArray($a_nodes, $this->order_column, $this->order_direction);

        // append adm node to end of list
        isset($match) ? array_push($a_nodes, $adm_node) : "";

        return $a_nodes;
    }
} // END class.ilExplorer
