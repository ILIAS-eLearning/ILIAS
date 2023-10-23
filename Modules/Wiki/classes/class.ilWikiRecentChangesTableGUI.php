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
 * TableGUI class for recent changes in wiki
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiRecentChangesTableGUI extends ilTable2GUI
{
    protected int $requested_ref_id;
    protected int $wiki_id = 0;
    protected ilObjectTranslation $ot;
    protected \ILIAS\Wiki\Page\PageManager $pm;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id
    ) {
        global $DIC;

        $service = $DIC->wiki()->internal();
        $gui = $service->gui();
        $domain = $service->domain();
        $this->ctrl = $gui->ctrl();
        $this->lng = $domain->lng();
        $this->requested_ref_id = $gui
            ->request()
            ->getRefId();
        $this->pm = $domain->page()->page($this->requested_ref_id);
        $this->ot = $domain->wiki()->translation($a_wiki_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->wiki_id = $a_wiki_id;

        $this->addColumn($this->lng->txt("wiki_last_changed"));
        $this->addColumn($this->lng->txt("wiki_page"));
        if ($this->ot->getContentActivated()) {
            $this->addColumn($this->lng->txt("language"));
        }
        $this->addColumn($this->lng->txt("wiki_last_changed_by"));
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_row_recent_changes.html",
            "Modules/Wiki"
        );
        $this->getRecentChanges();

        $this->setShowRowsSelector(true);

        $this->setTitle($this->lng->txt("wiki_recent_changes"));
    }

    public function getRecentChanges(): void
    {
        $changes = [];
        foreach ($this->pm->getRecentChanges() as $pi) {
            $changes[] = [
                "date" => $pi->getLastChange(),
                "user" => $pi->getLastChangedUser(),
                "id" => $pi->getId(),
                "title" => $pi->getTitle(),
                "lang" => $pi->getLanguage(),
                "nr" => $pi->getOldNr()
            ];
        }
        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("desc");
        $this->setData($changes);
    }

    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;

        if ($this->ot->getContentActivated()) {
            $l = $a_set["lang"];
            if ($l === "-") {
                $l = $this->ot->getMasterLanguage();
            }
            $this->tpl->setCurrentBlock("lang");
            $this->tpl->setVariable("LANG", $this->lng->txt("meta_l_" . $l));
            $this->tpl->parseCurrentBlock();
        }

        $title = $a_set["title"];
        $this->tpl->setVariable("TXT_PAGE_TITLE", $title);
        $this->tpl->setVariable(
            "DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
        );
        $ilCtrl->setParameterByClass("ilwikipagegui", "wpg_id", $a_set["id"]);
        $ilCtrl->setParameterByClass("ilwikipagegui", "transl", $a_set["lang"]);
        $ilCtrl->setParameterByClass("ilwikipagegui", "old_nr", $a_set["nr"] ?? "");
        $this->tpl->setVariable(
            "HREF_PAGE",
            $ilCtrl->getLinkTargetByClass("ilwikipagegui", "preview")
        );

        // user name
        $this->tpl->setVariable(
            "TXT_USER",
            ilUserUtil::getNamePresentation(
                $a_set["user"],
                true,
                true,
                $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())
            )
        );
    }
}
