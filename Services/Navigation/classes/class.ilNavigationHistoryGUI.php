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

use ILIAS\Navigation\StandardGUIRequest;

/**
 * User Interface Class for Navigation History
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilNavigationHistoryGUI:
 */
class ilNavigationHistoryGUI implements ilCtrlBaseClassInterface
{
    protected StandardGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilNavigationHistory $nav_history;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->lng = $DIC->language();
        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function getHTML(): string
    {
        $ilNavigationHistory = $this->nav_history;
        $lng = $this->lng;

        $selection = new ilAdvancedSelectionListGUI();
        $selection->setFormSelectMode(
            "url_ref_id",
            "ilNavHistorySelect",
            true,
            "goto.php?target=navi_request",
            "ilNavHistory",
            "ilNavHistoryForm",
            "_top",
            $lng->txt("go"),
            "ilNavHistorySubmit"
        );
        $selection->setListTitle($lng->txt("last_visited"));
        $selection->setId("lastvisited");
        $selection->setSelectionHeaderClass("MMInactive");
        $selection->setHeaderIcon(ilAdvancedSelectionListGUI::NO_ICON);
        $selection->setItemLinkClass("small");
        $selection->setUseImages(true);

        $items = $ilNavigationHistory->getItems();
        //$sel_arr = array(0 => "-- ".$lng->txt("last_visited")." --");
        reset($items);
        $cnt = 0;
        foreach ($items as $k => $item) {
            if ($cnt++ > 20) {
                break;
            }
            if (!isset($item["ref_id"]) || $this->request->getRefId() === 0 ||
                $item["ref_id"] != $this->request->getRefId() || $k > 0) {			// do not list current item
                $obj_id = ilObject::_lookupObjId((int) $item["ref_id"]);
                $selection->addItem(
                    $item["title"],
                    $item["ref_id"],
                    $item["link"],
                    ilObject::_getIcon($obj_id, "tiny", $item["type"]),
                    $lng->txt("obj_" . $item["type"]),
                    "_top"
                );
            }
        }
        $html = $selection->getHTML();

        if ($html === "") {
            $selection->addItem(
                $lng->txt("no_items"),
                "",
                "#",
                "",
                "",
                "_top"
            );
            $selection->setUseImages(false);
            $html = $selection->getHTML();
        }
        return $html;
    }

    public function handleNavigationRequest(): void
    {
        $ilNavigationHistory = $this->nav_history;
        $ilCtrl = $this->ctrl;

        if ($this->request->getTarget() === "navi_request") {
            $items = $ilNavigationHistory->getItems();
            foreach ($items as $item) {
                if ($item["ref_id"] == $this->request->getUrlRefId()) {
                    ilUtil::redirect($item["link"]);
                }
            }
            reset($items);
            $item = current($items);
            if ($this->request->getUrlRefId() === 0 && $item["ref_id"] == $this->request->getRefId()) {
                $item = next($items);		// omit current item
            }
            if ($this->request->getUrlRefId() === 0 && $item["link"] != "") {
                ilUtil::redirect($item["link"]);
            }

            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", "");
            $ilCtrl->setParameterByClass("ilrepositorygui", "getlast", "true");
            $ilCtrl->redirectByClass("ilrepositorygui", "");
        }
    }

    public function removeEntries(): void
    {
        $ilNavigationHistory = $this->nav_history;

        $ilNavigationHistory->deleteDBEntries();
        $ilNavigationHistory->deleteSessionEntries();
    }
}
