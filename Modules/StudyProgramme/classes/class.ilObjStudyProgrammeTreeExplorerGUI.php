<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilStudyProgrammeTreeGUI
 * ilObjStudyProgrammeTreeExplorerGUI generates the tree output for StudyProgrammes
 * This class builds the tree with drag & drop functionality and some additional buttons which triggers bootstrap-modals
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjStudyProgrammeTreeExplorerGUI extends ilExplorerBaseGUI
{
    protected ilAccessHandler $access;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected int $tree_root_id;

    /**
     * @var string css-id of the bootstrap modal dialog
     */
    protected string $modal_id;

    /**
     * @var array js configuration for the tree
     */
    protected array $js_conf;

    /**
     * default classes of the tree [key=>class_name]
     */
    protected array $class_configuration = [
        'node' => [
            'node_title' => 'title',
            'node_point' => 'points',
            'node_current' => 'ilHighlighted current_node',
            'node_buttons' => 'tree_button'
        ],
        'lp_object' => 'lp-object',
    ];

    protected string $js_study_programme_path = "./Modules/StudyProgramme/templates/js/ilStudyProgramme.js";
    protected string $css_study_programme_path = "./Modules/StudyProgramme/templates/css/ilStudyProgrammeTree.css";

    /**
     * @param $parent_obj string|object|array
     */
    public function __construct(int $tree_root_id, string $modal_id, string $expl_id, $parent_obj, string $parent_cmd)
    {
        parent::__construct($expl_id, $parent_obj, $parent_cmd);

        global $DIC;
        $this->access = $DIC['ilAccess'];
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();

        $this->tree_root_id = $tree_root_id;
        $this->modal_id = $modal_id;

        $this->js_conf = array();

        $this->lng->loadLanguageModule("prg");

        $this->setAjax(true);

        if ($this->checkAccess('write', $tree_root_id)) {
            $this->setEnableDnd(true);
        }
    }


    /**
     * Return node element
     * @param ilObjStudyProgramme|ilObject $a_node
     * @return string
     */
    public function getNodeContent($a_node) : string
    {
        $current_ref_id = -1;
        if ($this->request_wrapper->has("ref_id")) {
            $current_ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        }

        $is_current_node = ($a_node->getRefId() == $current_ref_id);
        $is_study_programme = ($a_node instanceof ilObjStudyProgramme);
        $is_root_node = ($is_study_programme && $a_node->getRoot() == null);

        // show delete only on not current elements and not root
        $is_delete_enabled = ($is_study_programme && ($is_current_node || $is_root_node))? false : $this->checkAccess("delete", $current_ref_id);

        $is_creation_enabled = ($this->checkAccess("create", $current_ref_id));

        $node_config = array(
            'current_ref_id' => $current_ref_id,
            'is_current_node' => $is_current_node,
            'is_delete_enabled' => $is_delete_enabled,
            'is_creation_enabled' => $is_creation_enabled,
            'is_study_programme' => $is_study_programme,
            'is_root_node' => $is_root_node
        );

        // TODO: find way to remove a-tag around the content, to create valid html
        $tpl = $this->getNodeTemplateInstance();

        // add the tree buttons
        if ($this->checkAccess('write', $a_node->getRefId())) {
            if ($is_study_programme) {
                $this->parseStudyProgrammeNodeButtons($a_node, $node_config, $tpl);
            } else {
                $this->parseLeafNodeButtons($a_node, $node_config, $tpl);
            }
        }

        $tpl->setCurrentBlock('node-content-block');
        $tpl->setVariable('NODE_TITLE_CLASSES', implode(' ', $this->getNodeTitleClasses($node_config)));
        $tpl->setVariable('NODE_TITLE', $a_node->getTitle());

        if ($is_study_programme) {
            $tpl->setVariable('NODE_POINT_CLASSES', $this->class_configuration['node']['node_point']);
            $tpl->setVariable('NODE_POINTS', $this->formatPointValue($a_node->getPoints()));
        }

        $tpl->parseCurrentBlock('node-content-block');

        return $tpl->get();
    }

    /**
     * Returns array with all css classes of the title node element
     */
    protected function getNodeTitleClasses(array $node_config) : array
    {
        $node_title_classes = array($this->class_configuration['node']['node_title']);
        if ($node_config['is_study_programme']) {
            if ($node_config['is_current_node']) {
                $node_title_classes[] = $this->class_configuration['node']['node_current'];
            }
        } else {
            $node_title_classes[] = $this->class_configuration['lp_object'];
        }

        return $node_title_classes;
    }


    /**
     * Generates the buttons for a study-programme node
     *
     * @param ilObjStudyProgramme $node parsed node
     * @param array $node_config configuration of current node
     * @param ilTemplate $tpl current node template
     */
    protected function parseStudyProgrammeNodeButtons(
        ilObjStudyProgramme $node,
        array $node_config,
        ilTemplate $tpl
    ) : void {
        $tpl->setCurrentBlock('enable-tree-buttons');

        // show info button only when it not the current node
        $info_button = $this->getNodeButtonActionLink(
            'ilObjStudyProgrammeSettingsGUI',
            'view',
            array('ref_id' => $node->getRefId(), 'currentNode' => $node_config['is_current_node']),
            ilGlyphGUI::get(ilGlyphGUI::INFO)
        );
        $tpl->setVariable('NODE_INFO_BUTTON', $info_button);

        // only show add button when create permission is set
        if ($node_config['is_creation_enabled']) {
            $create_button = $this->getNodeButtonActionLink(
                'ilObjStudyProgrammeTreeGUI',
                'create',
                array('ref_id' => $node->getRefId()),
                ilGlyphGUI::get(ilGlyphGUI::ADD)
            );
            $tpl->setVariable('NODE_CREATE_BUTTON', $create_button);
        }

        // only show delete button when its not the current node, not the root-node and delete permissions are set
        if ($node_config['is_delete_enabled']) {
            $delete_button = $this->getNodeButtonActionLink(
                'ilObjStudyProgrammeTreeGUI',
                'delete',
                array('ref_id' => $node->getRefId(), 'item_ref_id' => $node_config['current_ref_id']),
                ilGlyphGUI::get(ilGlyphGUI::REMOVE)
            );
            $tpl->setVariable('NODE_DELETE_BUTTON', $delete_button);
        }

        $tpl->parseCurrentBlock('enable-tree-buttons');
    }

    /**
     * Generates the buttons for a study programme leaf
     *
     * @param ilObject $node parsed node
     * @param array $node_config configuration of current node
     * @param ilTemplate $tpl current node template
     */
    protected function parseLeafNodeButtons(ilObject $node, array $node_config, ilTemplate $tpl) : void
    {
        $tpl->setCurrentBlock('enable-tree-buttons');

        // only show delete button when its not the current node
        if ($node_config['is_delete_enabled']) {
            $delete_button = $this->getNodeButtonActionLink(
                'ilObjStudyProgrammeTreeGUI',
                'delete',
                array('ref_id' => $node->getRefId(), 'item_ref_id' => $node_config['current_ref_id']),
                ilGlyphGUI::get(ilGlyphGUI::REMOVE)
            );
            $tpl->setVariable('NODE_DELETE_BUTTON', $delete_button);
        }

        $tpl->parseCurrentBlock('enable-tree-buttons');
    }

    /**
     * Factory method for a new instance of a node template
     */
    protected function getNodeTemplateInstance() : ilTemplate
    {
        return new ilTemplate("tpl.tree_node_content.html", true, true, "Modules/StudyProgramme");
    }

    /**
     * Returns formatted point value
     */
    protected function formatPointValue(int $points) : string
    {
        return '(' . $points . " " . $this->lng->txt('prg_points') . ')';
    }
    
    /**
     * Generate link-element
     */
    protected function getNodeButtonActionLink(
        string $target_class,
        string $cmd,
        array $params,
        string $content,
        bool $async = true
    ) : string {
        foreach ($params as $param_name => $param_value) {
            $this->ctrl->setParameterByClass($target_class, $param_name, $param_value);
        }

        $tpl = $this->getNodeTemplateInstance();
        $tpl->setCurrentBlock('tree-button-block');

        $classes = array($this->class_configuration['node']['node_buttons']);
        $classes[] = 'cmd_' . $cmd;

        $tpl->setVariable('LINK_HREF', $this->ctrl->getLinkTargetByClass($target_class, $cmd, '', true, false));
        $tpl->setVariable('LINK_CLASSES', implode(' ', $classes));

        if ($async) {
            $tpl->touchBlock('enable-async-link');
            $tpl->setVariable('LINK_DATA_TARGET', '#' . $this->modal_id);
        }

        $tpl->setVariable('LINK_CONTENT', $content);

        return $tpl->get();
    }

    /**
     * Return root node of tree
     */
    public function getRootNode() : ilObjStudyProgramme
    {
        $node = ilObjStudyProgramme::getInstanceByRefId($this->tree_root_id);
        return $node;
    }

    public function getNodeIcon($node) : string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $obj_id = ilObject::_lookupObjId($node->getRefId());
        if ($ilias->getSetting('custom_icons')) {
            //TODO: implement custom icon functionality
        }

        return ilObject::_getIcon($obj_id, "tiny");
    }

    public function getNodeHref($node) : string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if ($ilCtrl->getCmd() === "performPaste") {
            $ilCtrl->setParameterByClass("ilObjStudyProgrammeGUI", "target_node", $node->getRefId());
        }

        $ilCtrl->setParameterByClass("ilObjStudyProgrammeGUI", "ref_id", $node->getRefId());

        return '#';
    }

    public function getChildsOfNode($a_parent_node_id) : array
    {
        $parent_obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId((int) $a_parent_node_id);

        $children_with_permission = array();

        // its currently only possible to have children on StudyProgrammes
        if ($parent_obj instanceof ilObjStudyProgramme) {
            $children = ($parent_obj->hasChildren())? $parent_obj->getChildren() : $parent_obj->getLPChildren();

            if (is_array($children)) {
                foreach ($children as $node) {
                    if ($this->checkAccess('visible', $node->getRefId())) {
                        $children_with_permission[] = $node;
                    }
                }
            }
        }

        return $children_with_permission;
    }

    public function getNodeId($a_node) : ?int
    {
        if (!is_null($a_node)) {
            return $a_node->getRefId();
        }
        return null;
    }

    public function listItemStart(ilTemplate $tpl, $a_node) : void
    {
        $tpl->setCurrentBlock("list_item_start");

        if (
            ($this->getAjax() && $this->nodeHasVisibleChilds($a_node)) ||
            ($a_node instanceof ilObjStudyProgramme && $a_node->getParent() === null)
        ) {
            $tpl->touchBlock("li_closed");
        }

        $tpl->setVariable(
            "DOM_NODE_ID",
            $this->getDomNodeIdForNodeId($this->getNodeId($a_node))
        );
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("tag");
    }


    /**
     * Returns the output of the complete tree
     * There are added some additional javascripts before output the parent::getHTML()
     */
    public function getHTML() : string
    {
        $this->tpl->addJavascript($this->js_study_programme_path);
        $this->tpl->addCss($this->css_study_programme_path);

        $this->tpl->addOnLoadCode(
            '$("#' . $this->getContainerId() . '").study_programme_tree(' . json_encode(
                $this->js_conf,
                JSON_THROW_ON_ERROR
            ) . ');'
        );

        return parent::getHTML();
    }


    /**
     * Closes certain node in the tree session
     * The open nodes of a tree are stored in a session. This function closes a certain node by its id.
     *
     * @param mixed $node_id
     */
    public function closeCertainNode($node_id) : void
    {
        if (in_array($node_id, $this->open_nodes)) {
            $k = array_search($node_id, $this->open_nodes);
            unset($this->open_nodes[$k]);
        }
        $this->store->set("on_" . $this->id, serialize($this->open_nodes));
    }

    /**
     * Open certain node in the tree session
     * The open nodes of a tree are stored in a session. This function opens a certain node by its id.
     *
     * @param mixed $node_id
     */
    public function openCertainNode($node_id) : void
    {
        $id = $this->getNodeIdForDomNodeId($node_id);
        if (!in_array($id, $this->open_nodes)) {
            $this->open_nodes[] = $id;
        }
        $this->store->set("on_" . $this->id, serialize($this->open_nodes));
    }


    /**
     * Checks permission of current tree or certain child of it
     */
    protected function checkAccess(string $permission, int $ref_id) : bool
    {
        return $this->access->checkAccess($permission, '', $ref_id);
    }

    /**
     * Checks permission of a object and throws an exception if they are not granted
     */
    protected function checkAccessOrFail(string $permission, int $ref_id) : void
    {
        if (!$this->checkAccess($permission, $ref_id)) {
            throw new ilException("You have no permission for " . $permission . " Object with ref_id " . $ref_id . "!");
        }
    }

    /**
     * Adds configuration to the study-programme-tree jquery plugin
     */
    public function addJsConf(string $key, string $value) : void
    {
        $this->js_conf[$key] = $value;
    }

    /**
     * Returns setting of the study-programme-tree
     */
    public function getJsConf(string $key) : string
    {
        return $this->js_conf[$key];
    }
}
