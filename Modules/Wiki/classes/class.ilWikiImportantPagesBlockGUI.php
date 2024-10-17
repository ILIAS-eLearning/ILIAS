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
    protected \ILIAS\Wiki\Navigation\ImportantPageManager $imp_pages;
    protected \ILIAS\Wiki\WikiGUIRequest $wiki_request;
    protected \ILIAS\Wiki\InternalGUIService $gui;
    protected \ILIAS\Wiki\Wiki\DomainService $wiki_manager;
    protected string $lang;
    protected \ILIAS\Wiki\Page\PageManager $pm;
    protected \ILIAS\Wiki\InternalDomainService $domain;
    protected \ILIAS\Wiki\InternalService $service;
    protected bool $export = false;

    public function __construct()
    {
        global $DIC;

        $this->service = $DIC->wiki()->internal();

        $this->domain = $this->service->domain();
        $this->gui = $this->service->gui();
        $this->wiki_request = $this->gui->request();

        $ref_id = $this->wiki_request->getRefId();
        $this->pm = $this->domain->page()->page($ref_id);
        $this->wiki_manager = $this->domain->wiki();
        $this->imp_pages = $this->domain->importantPage($ref_id);
        $this->lang = $this->wiki_request->getTranslation();

        $this->ctrl = $this->gui->ctrl();
        $this->lng = $this->domain->lng();
        $this->access = $this->domain->access();

        parent::__construct();

        $this->lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);

        $this->setTitle($this->lng->txt("wiki_navigation"));
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
    protected function getLegacyContent(): string
    {
        $ilCtrl = $this->ctrl;
        $cpar[1] = 0;

        $listing = $this->gui->listing();

        $cnt = 1;
        $start_page_id = $this->wiki_manager->getStartingPageId($this->requested_ref_id);

        if ($this->pm->exists($start_page_id, $this->lang)) {
            $title = $this->pm->getTitle($start_page_id, $this->lang);
            if (!$this->export) {
                $listing->node($this->ui->factory()->link()->standard(
                    $title,
                    $this->pm->getPermaLink($start_page_id, $this->lang)
                ), "1", "0");
            } else {
                $listing->node($this->ui->factory()->link()->standard(
                    $title,
                    "index.html"
                ), "1", "0");
            }
        }

        $cpar[0] = 1;

        foreach ($this->imp_pages->getList() as $p) {
            if ($this->pm->exists($p->getId(), $this->lang)) {
                $cnt++;
                $title = $this->pm->getTitle($p->getId(), $this->lang);
                if (!$this->export) {
                    $listing->node($this->ui->factory()->link()->standard(
                        $title,
                        $this->pm->getPermaLink($p->getId(), $this->lang)
                    ), (string) $cnt, (string) ($cpar[$p->getIndent() - 1] ?? 0));
                } else {
                    $listing->node($this->ui->factory()->link()->standard(
                        $title,
                        "wpg_" . $p->getId() . ".html"
                    ), (string) $cnt, (string) ($cpar[$p->getIndent() - 1] ?? 0));
                }
                $cpar[$p->getIndent()] = $cnt;
            }
        }

        return $listing->render();
    }
}
