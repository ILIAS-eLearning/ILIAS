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
 * Clipboard for editing
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilEditClipboardGUI: ilObjMediaObjectGUI
 */
class ilEditClipboardGUI
{
    public string $mode = "";
    protected string $page_back_title = "";
    protected bool $multiple = false;
    protected \ILIAS\MediaPool\Clipboard\ClipboardGUIRequest $request;
    protected \ILIAS\MediaPool\Clipboard\ClipboardManager $clipboard_manager;
    protected string $insertbuttontitle = "";
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilTree $tree;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;

    protected string $requested_return_cmd = "";
    protected int $requested_clip_item_id = 0;
    protected string $requested_pcid = "";

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->clipboard()
            ->request();

        $this->multiple = false;
        $this->page_back_title = $lng->txt("cont_back");
        $this->requested_return_cmd = $this->request->getReturnCmd();
        $this->requested_clip_item_id = $this->request->getItemId();
        $this->requested_pcid = $this->request->getPCId();
        $this->clipboard_manager = $DIC->mediaPool()
           ->internal()
           ->domain()
           ->clipboard();

        if ($this->requested_return_cmd !== "") {
            $this->mode = "getObject";
        } else {
            $this->mode = "";
        }

        $ilCtrl->setParameter(
            $this,
            "returnCommand",
            rawurlencode($this->requested_return_cmd)
        );

        $ilCtrl->saveParameter($this, array("clip_item_id", "pcid"));
    }

    public function executeCommand(): void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        switch ($next_class) {
            case "ilobjmediaobjectgui":
                $ilCtrl->setReturn($this, "view");
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "view")
                );
                $mob_gui = new ilObjMediaObjectGUI("", $this->requested_clip_item_id, false, false);
                $mob_gui->setTabs();
                $ilCtrl->forwardCommand($mob_gui);
                switch ($cmd) {
                    case "save":
                        $ilUser->addObjectToClipboard(
                            $mob_gui->getObject()->getId(),
                            "mob",
                            $mob_gui->getObject()->getTitle()
                        );
                        $ilCtrl->redirect($this, "view");
                        break;
                }
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    public function setMultipleSelections(bool $a_multiple = true): void
    {
        $this->multiple = $a_multiple;
    }

    public function getMultipleSelections(): bool
    {
        return $this->multiple;
    }

    public function setInsertButtonTitle(string $a_insertbuttontitle): void
    {
        $this->insertbuttontitle = $a_insertbuttontitle;
    }

    public function getInsertButtonTitle(): string
    {
        $lng = $this->lng;

        if ($this->insertbuttontitle === "") {
            return $lng->txt("insert");
        }

        return $this->insertbuttontitle;
    }

    public function view(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        $but = ilLinkButton::getInstance();
        $but->setUrl($ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "create"));
        $but->setCaption("cont_create_mob");
        $ilToolbar->addButtonInstance($but);

        $table_gui = new ilClipboardTableGUI($this, "view");
        $tpl->setContent($table_gui->getHTML());
    }


    public function getObject(): void
    {
        $this->mode = "getObject";
        $this->view();
    }


    /**
     * remove item from clipboard
     */
    public function remove(): void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // check number of objects
        $ids = $this->request->getItemIds();

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "view");
        }

        foreach ($ids as $obj_id) {
            $id = explode(":", $obj_id);
            if ($id[0] === "mob") {
                $ilUser->removeObjectFromClipboard($id[1], "mob");
                include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                $mob = new ilObjMediaObject($id[1]);
                $mob->delete();			// this method don't delete, if mob is used elsewhere
            }
            if ($id[0] === "incl") {
                $ilUser->removeObjectFromClipboard($id[1], "incl");
            }
        }
        $ilCtrl->redirect($this, "view");
    }

    public function insert(): void
    {
        $lng = $this->lng;

        $return = $this->requested_return_cmd;
        if ($this->requested_pcid !== "") {
            $return .= "&pc_id=" . $this->requested_pcid;
        }

        $ids = $this->request->getItemIds();

        // check number of objects
        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            ilUtil::redirect($return);
        }

        if (!$this->getMultipleSelections() && count($ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("cont_select_max_one_item"), true);
            ilUtil::redirect($return);
        }

        $this->clipboard_manager->setIds($ids);
        ilUtil::redirect($return);
    }

    public static function _getSelectedIDs(): array
    {
        global $DIC;
        $clipboard_manager = $DIC->mediaPool()
            ->internal()
            ->domain()
            ->clipboard();

        return $clipboard_manager->getIds();
    }

    public function setTabs(): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $tpl->setTitle($lng->txt("clipboard"));
        $this->getTabs($ilTabs);
    }

    public function setPageBackTitle(string $a_title): void
    {
        $this->page_back_title = $a_title;
    }

    public function getTabs($tabs_gui): void
    {
        $ilCtrl = $this->ctrl;

        // back to upper context
        $tabs_gui->setBackTarget(
            $this->page_back_title,
            $ilCtrl->getParentReturn($this)
        );
    }
}
