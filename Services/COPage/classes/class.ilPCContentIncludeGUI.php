<?php

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
 * Class ilPCContentIncludeGUI
 * User Interface for Content Includes (Snippets) Editing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCContentIncludeGUI extends ilPageContentGUI
{
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs;


    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Insert new resources component form.
     */
    public function insert() : void
    {
        switch ($this->sub_command) {
            case "selectPool":
                $this->selectPool();
                break;

            case "poolSelection":
                $this->poolSelection();
                break;

            default:
                $this->insertFromPool();
                break;
        }
    }
    
    /**
     * Insert page snippet from media pool
     */
    public function insertFromPool() : void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->edit_repo->getMediaPool() > 0 &&
            $ilAccess->checkAccess("write", "", $this->edit_repo->getMediaPool())
            && ilObject::_lookupType(ilObject::_lookupObjId($this->edit_repo->getMediaPool())) == "mep") {
            $tb = new ilToolbarGUI();

            $ilCtrl->setParameter($this, "subCmd", "poolSelection");

            $tb->addButton(
                $lng->txt("cont_select_media_pool"),
                $ilCtrl->getLinkTarget($this, "insert")
            );
            $html = $tb->getHTML();

            $ilCtrl->setParameter($this, "subCmd", "");

            $pool = new ilObjMediaPool($this->edit_repo->getMediaPool());
            $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
            $mpool_table = new ilMediaPoolTableGUI(
                $this,
                "insert",
                $pool,
                "mep_folder",
                ilMediaPoolTableGUI::IL_MEP_SELECT_CONTENT
            );
            $mpool_table->setInsertCommand("create_incl");

            $html .= $mpool_table->getHTML();

            $tpl->setContent($html);
        } else {
            $this->poolSelection();
        }
    }

    /**
     * Pool Selection
     */
    public function poolSelection() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        $exp = new ilPoolSelectorGUI($this, "insert");

        // filter
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "mep"));
        $exp->setClickableTypes(array('mep'));

        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    /**
     * create new content include in dom and update page in db
     */
    public function create() : void
    {
        $ids = $this->request->getIntArray("id");
        if (count($ids) > 0) {
            for ($i = count($ids) - 1; $i >= 0; $i--) {
                // similar code in ilpageeditorgui::insertFromClipboard
                $this->content_obj = new ilPCContentInclude($this->getPage());
                $this->content_obj->create(
                    $this->pg_obj,
                    $this->request->getHierId(),
                    $this->pc_id
                );
                $this->content_obj->setContentType("mep");
                $this->content_obj->setContentId($ids[$i]);
            }
            $this->updated = $this->pg_obj->update();
        }
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
     * Select concrete pool
     */
    public function selectPool() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->edit_repo->setMediaPool($this->request->getInt("pool_ref_id"));
        $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
        $ilCtrl->redirect($this, "insert");
    }
}
