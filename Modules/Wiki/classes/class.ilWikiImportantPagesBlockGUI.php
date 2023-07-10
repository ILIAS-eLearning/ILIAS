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
 * Important pages wiki block
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiImportantPagesBlockGUI extends ilBlockGUI
{
    public static string $block_type = "wikiimppages";
    protected \ILIAS\Wiki\InternalGUIService $gui;
    protected bool $export = false;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $lng = $DIC->language();

        parent::__construct();

        $lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);

        $this->setTitle($lng->txt("wiki_navigation"));
        $this->allow_moving = false;
        $this->gui = $DIC->wiki()->internal()->gui();
    }

    public function getBlockType(): string
    {
        return self::$block_type;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    public function getHTML(bool $a_export = false): string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->export = $a_export;

        if (!$this->export && ilWikiPerm::check("edit_wiki_navigation", $this->requested_ref_id)) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass("ilobjwikigui", "editImportantPages"),
                $lng->txt("edit")
            );
        }

        return parent::getHTML();
    }

    public function fillDataSection(): void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;


    protected function getLegacyContent(): string
    {
        $ilCtrl = $this->ctrl;
        $cpar[1] = 0;

        $listing = $this->gui->listing();

        $cnt = 1;
        $title = ilObjWiki::_lookupStartPage(ilObject::_lookupObjId($this->requested_ref_id));
        if (!$this->export) {
            $listing->node($this->ui->factory()->link()->standard(
                $title,
                $ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage")
            ), "1", "0");
        } else {
            $listing->node($this->ui->factory()->link()->standard(
                $title,
                "index.html"
            ), "1", "0");
        }
        $cpar[0] = 1;

        $ipages = ilObjWiki::_lookupImportantPagesList(ilObject::_lookupObjId($this->requested_ref_id));
        foreach ($ipages as $p) {
            $cnt++;
            $title = ilWikiPage::lookupTitle($p["page_id"]);
            if (!$this->export) {
                $listing->node($this->ui->factory()->link()->standard(
                    $title,
                    ilObjWikiGUI::getGotoLink($this->requested_ref_id, (string) $title)
                ), (string) $cnt, (string) ($cpar[$p["indent"] - 1] ?? 0));
            } else {
                $listing->node($this->ui->factory()->link()->standard(
                    $title,
                    "wpg_" . $p["page_id"] . ".html"
                ), (string) $cnt, (string) ($cpar[$p["indent"] - 1] ?? 0));
            }
            $cpar[$p["indent"]] = $cnt;
        }

        return $listing->render();
    }
}
