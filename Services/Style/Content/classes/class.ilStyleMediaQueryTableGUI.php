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

use ILIAS\Style\Content;

/**
 * TableGUI class for style editor (image list)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleMediaQueryTableGUI extends ilTable2GUI
{
    protected ilObjStyleSheet $style_obj;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected Content\Access\StyleAccessManager $access_manager;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjStyleSheet $a_style_obj,
        Content\Access\StyleAccessManager $access_manager
    ) {
        global $DIC;

        $this->access_manager = $access_manager;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("sty_media_queries"));
        $this->setDescription($lng->txt("sty_media_query_info"));
        $this->style_obj = $a_style_obj;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_order"));
        $this->addColumn($this->lng->txt("sty_query"), "");
        $this->addColumn($this->lng->txt("actions"), "");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_media_query_row.html", "Services/Style/Content");
        //$this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->access_manager->checkWrite()) {
            $this->addCommandButton("saveMediaQueryOrder", $lng->txt("sty_save_order"));
            $this->addMultiCommand("deleteMediaQueryConfirmation", $lng->txt("delete"));
        }
        
        $this->setEnableTitle(true);
    }

    public function getItems() : void
    {
        $this->setData($this->style_obj->getMediaQueries());
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("MQUERY", $a_set["mquery"]);
        $this->tpl->setVariable("MQID", $a_set["id"]);
        $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);

        if ($this->access_manager->checkWrite()) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "mq_id", $a_set["id"]);
            $this->tpl->setVariable(
                "LINK_EDIT_MQUERY",
                $ilCtrl->getLinkTarget($this->parent_obj, "editMediaQuery")
            );
        }
    }
}
