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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;
use ILIAS\Help\StandardGUIRequest;

/**
 * Help GUI class.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilHelpGUI: ilLMPageGUI
 */
class ilHelpGUI implements ilCtrlBaseClassInterface
{
    public const ID_PART_SCREEN = "screen";
    public const ID_PART_SUB_SCREEN = "sub_screen";
    public const ID_PART_COMPONENT = "component";
    protected \ILIAS\Repository\InternalGUIService $gui;
    protected static ?\ILIAS\Help\InternalService $internal_service = null;
    protected \ILIAS\Help\Presentation\PresentationManager $presentation;
    protected \ILIAS\Help\Map\MapManager $help_map;
    protected StandardGUIRequest $help_request;

    protected ilCtrl $ctrl;
    protected ilSetting $settings;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    public array $help_sections = array();
    public array $def_screen_id = array();
    public array $screen_id = array();
    protected string $screen_id_component = '';
    protected \ILIAS\DI\UIServices $ui;
    protected ?array $raw_menu_items = null;

    public function __construct()
    {
        $domain = $this->internal()->domain();

        $this->settings = $domain->settings();
        $this->lng = $domain->lng();
        $this->user = $domain->user();

        $this->help_map = $domain->map();
        $this->presentation = $domain->presentation();
    }

    protected function initUI(): void
    {
        $this->ui = $this->internal()->gui()->ui();
        $gui = $this->internal()->gui();
        $this->ctrl = $gui->ctrl();
        $this->help_request = $gui->standardRequest();
    }

    protected function symbol(): \ILIAS\Repository\Symbol\SymbolAdapterGUI
    {
        global $DIC;
        return $DIC->repository()->internal()->gui()->symbol();
    }

    public function setDefaultScreenId(
        string $a_part,
        string $a_id
    ): void {
        $this->def_screen_id[$a_part] = $a_id;
    }

    public function setScreenId(string $a_id): void
    {
        $this->screen_id[self::ID_PART_SCREEN] = $a_id;
    }

    public function setSubScreenId(string $a_id): void
    {
        $this->screen_id[self::ID_PART_SUB_SCREEN] = $a_id;
    }

    public function setScreenIdComponent(string $a_comp): void
    {
        $this->screen_id_component = $a_comp;
    }

    public function getScreenId(): string
    {
        $comp = ($this->screen_id_component != "")
            ? $this->screen_id_component
            : ($this->def_screen_id[self::ID_PART_COMPONENT] ?? '');

        if ($comp == "") {
            return "";
        }

        $scr_id = (isset($this->screen_id[self::ID_PART_SCREEN]) && $this->screen_id[self::ID_PART_SCREEN] != "")
            ? $this->screen_id[self::ID_PART_SCREEN]
            : ($this->def_screen_id[self::ID_PART_SCREEN] ?? '');

        $sub_scr_id = (isset($this->screen_id[self::ID_PART_SUB_SCREEN]) && $this->screen_id[self::ID_PART_SUB_SCREEN] != "")
            ? $this->screen_id[self::ID_PART_SUB_SCREEN]
            : ($this->def_screen_id[self::ID_PART_SUB_SCREEN] ?? '');

        $screen_id = $comp . "/" .
            $scr_id . "/" .
            $sub_scr_id;

        return $screen_id;
    }

    public function addHelpSection(
        string $a_help_id,
        int $a_level = 1
    ): void {
        $this->help_sections[] = array("help_id" => $a_help_id, $a_level);
    }

    public function hasSections(): bool
    {
        return $this->help_map->hasScreenIdSections($this->getScreenId());
    }

    public function getHelpSections(): array
    {
        return $this->help_map->getHelpSectionsForId(
            $this->getScreenId(),
            $this->help_request->getRefId()
        );
    }

    public function setCtrlPar(): void
    {
        $ilCtrl = $this->ctrl;
        $refId = (string) $this->help_request->getRefId();
        $ilCtrl->setParameterByClass("ilhelpgui", "help_screen_id", $this->getScreenId() . "." . $refId);
    }

    public function executeCommand(): string
    {
        $this->initUI();
        $cmd = $this->ctrl->getCmd("showHelp") ?: "showHelp";
        return (string) $this->$cmd();
    }

    public function showHelp(): void
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("help");
        $ui = $this->ui;

        if ($this->help_request->getHelpScreenId() !== "") {
            ilSession::set("help_screen_id", $this->help_request->getHelpScreenId());
            $help_screen_id = $this->help_request->getHelpScreenId();
        } else {
            $help_screen_id = ilSession::get("help_screen_id");
        }

        ilSession::set("help_search_term", "");

        $this->resetCurrentPage();

        $id_arr = explode(".", $help_screen_id);
        $help_arr = $this->help_map->getHelpSectionsForId($id_arr[0], (int) $id_arr[1]);

        if (count($help_arr) > 0) {
            $acc = new ilAccordionGUI();
            $acc->setId("oh_acc");
            $acc->setUseSessionStorage(true);
            $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

            foreach ($help_arr as $h_id) {
                $st_id = $h_id;
                if (!ilLMObject::_exists($st_id)) {
                    continue;
                }
                $oh_lm_id = ilLMObject::_lookupContObjID($st_id);
                $pages = ilLMObject::getPagesOfChapter($oh_lm_id, $st_id);
                $items = [];
                foreach ($pages as $pg) {
                    $items[] = $this->ui->factory()->button()->shy(
                        $this->replaceMenuItemTags(ilLMObject::_lookupTitle($pg["child"])),
                        "#"
                    )->withOnLoadCode(function ($id) use ($pg) {
                        return "document.getElementById('$id').addEventListener('click', () => {return il.Help.showPage(" . $pg["child"] . ");})";
                    });
                }
                $list = $this->ui->factory()->listing()->unordered($items);
                $acc->addItem(ilLMObject::_lookupTitle($st_id), $this->ui->renderer()->renderAsync($list));
            }

            $h_tpl = new ilTemplate("tpl.help.html", true, true, "components/ILIAS/Help");
            $h_tpl->setVariable("HEAD", $lng->txt("help"));

            $h_tpl->setCurrentBlock("search");
            $h_tpl->setVariable("GL_SEARCH", $this->symbol()->glyph("search")->render());
            $h_tpl->setVariable("HELP_SEARCH_LABEL", $this->lng->txt("help_search_label"));
            $h_tpl->parseCurrentBlock();

            if (count($help_arr) > 0) {
                $h_tpl->setVariable("CONTENT", $acc->getHTML(true));
            } else {
                $mess = $ui->factory()->messageBox()->info($lng->txt("help_no_content"));
                $h_tpl->setVariable("CONTENT", $ui->renderer()->render([$mess]));
            }

            $h_tpl->setVariable("CLOSE_IMG", $this->symbol()->glyph("close")->render());
            echo $h_tpl->get();
        }
        exit;
    }

    public function showPage(): void
    {
        $lng = $this->lng;
        $ui = $this->ui;

        $page_id = $this->help_request->getHelpPage();

        $h_tpl = new ilTemplate("tpl.help.html", true, true, "components/ILIAS/Help");

        if (($t = ilSession::get("help_search_term")) != "") {
            $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($t) {
                return
                    "$(\"#$id\").click(function() { return il.Help.search('" . ilLegacyFormElementsUtil::prepareFormOutput(
                        $t
                    ) . "'); return false;});";
            });
        } else {
            $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { return il.Help.listHelp(event, true); return false;});";
            });
        }
        $h_tpl->setVariable("BACKBUTTON", $ui->renderer()->renderAsync($back_button));

        $h_tpl->setVariable(
            "HEAD",
            $this->replaceMenuItemTags(ilLMObject::_lookupTitle($page_id))
        );

        if (!ilPageUtil::_existsAndNotEmpty("lm", $page_id)) {
            exit;
        }

        // get page object
        $page_gui = new ilLMPageGUI($page_id);
        $cfg = $page_gui->getPageConfig();
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $page_gui->setRawPageContent(true);
        $cfg->setEnablePCType("Map", false);
        $cfg->setEnablePCType("Tabs", false);
        $cfg->setEnablePCType("FileList", false);

        $page_gui->getPageObject()->buildDom();
        $int_links = $page_gui->getPageObject()->getInternalLinks();
        $link_xml = $this->getLinkXML($int_links);
        $link_xml .= $this->getLinkTargetsXML();
        //echo htmlentities($link_xml);
        $page_gui->setLinkXml($link_xml);

        $ret = $this->replaceMenuItemTags($page_gui->showPage());

        $h_tpl->setVariable("CONTENT", $ret);
        $h_tpl->setVariable("CLOSE_IMG", $this->symbol()->glyph("close")->render());

        ilSession::set("help_pg", $page_id);

        $page = $h_tpl->get();

        // replace style classes
        //$page = str_replace("ilc_text_inline_Strong", "ilHelpStrong", $page);

        echo $page;
        exit;
    }

    public function resetCurrentPage(): void
    {
        ilSession::clear("help_pg");
    }

    public function getTabTooltipText(string $a_tab_id): string
    {
        if ($this->screen_id_component != "") {
            return $this->internal()->domain()->tooltips()->getTooltipPresentationText($this->screen_id_component . "_" . $a_tab_id);
        }
        return "";
    }

    public function initHelp(
        ilGlobalTemplateInterface $a_tpl,
        string $ajax_url
    ): void {
        global $DIC;

        $this->initUI();
        $ilUser = $DIC->user();
        $ilSetting = $DIC->settings();
        $ctrl = $DIC->ctrl();

        $a_tpl->addJavaScript("assets/js/ilHelp.js");
        $a_tpl->addJavaScript("assets/js/accordion.js");
        iljQueryUtil::initMaphilight();
        $a_tpl->addJavaScript("assets/js/ilCOPagePres.js");

        $this->setCtrlPar();
        $a_tpl->addOnLoadCode(
            "il.Help.setAjaxUrl('" .
            $ctrl->getLinkTargetByClass("ilhelpgui", "", "", true)
            . "');"
        );


        if ($this->presentation->isHelpActive()) {
            if (ilSession::get("help_pg") > 0) {
                $a_tpl->addOnLoadCode("il.Help.showCurrentPage(" . ilSession::get("help_pg") . ");", 3);
            } else {
                $a_tpl->addOnLoadCode("il.Help.listHelp(null);", 3);
            }


            if ($ilUser->getPref("hide_help_tt")) {
                $a_tpl->addOnLoadCode("if (il && il.Help) {il.Help.switchTooltips();}", 3);
            }
        }
    }

    public function isHelpPageActive(): bool
    {
        return (ilSession::get("help_pg") > 0);
    }

    public function deactivateTooltips(): void
    {
        $ilUser = $this->user;

        $ilUser->writePref("hide_help_tt", "1");
    }

    public function activateTooltips(): void
    {
        $ilUser = $this->user;

        $ilUser->writePref("hide_help_tt", "0");
    }

    public function getLinkXML(
        array $a_int_links
    ): string {
        $href = "";
        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link) {
            $target = $int_link["Target"];
            if (strpos($target, "il__") === 0) {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];
                $targetframe = "None";

                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        if ($type === "PageObject") {
                            $href = "#pg_" . $target_id;
                        } else {
                            $href = "#";
                        }
                        break;
                }

                $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                    "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"\" Anchor=\"\"/>";
            }
        }
        $link_info .= "</IntLinkInfos>";

        return $link_info;
    }

    /**
     * Get XMl for Link Targets
     */
    public function getLinkTargetsXML(): string
    {
        $link_info = "<LinkTargets>";
        $link_info .= "<LinkTarget TargetFrame=\"None\" LinkTarget=\"\" OnClick=\"return il.Help.openLink(event);\" />";
        $link_info .= "</LinkTargets>";
        return $link_info;
    }

    public function search(): void
    {
        $lng = $this->lng;
        $ui = $this->ui;

        $term = $this->help_request->getTerm();

        if ($term === "") {
            $term = ilSession::get("help_search_term");
        }

        $this->resetCurrentPage();

        $h_tpl = new ilTemplate("tpl.help.html", true, true, "components/ILIAS/Help");

        $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) {
            return
                "$(\"#$id\").click(function() { return il.Help.listHelp(event, true); return false;});";
        });
        $h_tpl->setVariable("BACKBUTTON", $ui->renderer()->renderAsync($back_button));

        $h_tpl->setVariable("HEAD", $lng->txt("help") . " - " .
            $lng->txt("search_result"));

        $h_tpl->setCurrentBlock("search");
        $h_tpl->setVariable("GL_SEARCH", $this->symbol()->glyph("search")->render());
        $h_tpl->setVariable("HELP_SEARCH_LABEL", $this->lng->txt("help_search_label"));
        $h_tpl->setVariable("VAL_SEARCH", ilLegacyFormElementsUtil::prepareFormOutput($term));
        $h_tpl->parseCurrentBlock();

        $h_tpl->setVariable("CLOSE_IMG", $this->symbol()->glyph("close")->render());

        $items = [];
        $module = $this->internal()->domain()->module();
        foreach ($module->getActiveModules() as $module_id) {
            $lm_id = $module->lookupModuleLmId($module_id);
            $s = new ilRepositoryObjectDetailSearch($lm_id);
            $s->setQueryString($term);
            $result = $s->performSearch();
            foreach ($result->getResults() as $r) {
                $items[] = $this->ui->factory()->button()->shy(
                    ilLMObject::_lookupTitle($r["item_id"]),
                    "#"
                )->withOnLoadCode(function ($id) use ($r) {
                    return "document.getElementById('$id').addEventListener('click', () => {return il.Help.showPage(" . $r["item_id"] . ");})";
                });
            }
        }

        $list = $this->ui->factory()->listing()->unordered($items);
        $h_tpl->setVariable("CONTENT", $this->ui->renderer()->renderAsync($list));

        ilSession::set("help_search_term", $term);

        echo $h_tpl->get();
        exit;
    }

    protected function replaceMenuItemTags(
        string $content
    ): string {
        global $DIC;

        $mmc = $DIC->globalScreen()->collector()->mainmenu();
        if ($this->raw_menu_items == null) {
            $mmc->collectOnce();
            $this->raw_menu_items = iterator_to_array($mmc->getRawItems());
        }

        foreach ($this->raw_menu_items as $item) {
            if ($item instanceof Item\LinkList) {
                foreach ($item->getLinks() as $link) {
                    $content = $this->replaceItemTag($content, $link);
                }
            }
            $content = $this->replaceItemTag($content, $item);
        }
        return $content;
    }

    protected function replaceItemTag(
        string $content,
        \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item
    ): string {
        global $DIC;
        $mmc = $DIC->globalScreen()->collector()->mainmenu();

        $id = $item->getProviderIdentification()->getInternalIdentifier();
        $ws = "[ \t\r\f\v\n]*";

        // menu item path
        while (preg_match("~\[(menu" . $ws . "path$ws=$ws(\"$id\")$ws)/\]~i", $content, $found)) {
            $path = "";
            if ($item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild) {
                $parent = $mmc->getItemInformation()->getParent($item);
                if ($parent !== null) {
                    $parent = $mmc->getSingleItemFromRaw($parent);
                    $path = $this->getTitleForItem($parent) . " > ";
                }
            }
            $path .= $this->getTitleForItem($item);
            $content = preg_replace(
                '~\[' . $found[1] . '/\]~i',
                "<strong>" . $path . "</strong>",
                $content
            );
        }
        // menu item
        while (preg_match("~\[(menu" . $ws . "item$ws=$ws(\"$id\")$ws)/\]~i", $content, $found)) {
            $content = preg_replace(
                '~\[' . $found[1] . '/\]~i',
                "<strong>" . $this->getTitleForItem($item) . "</strong>",
                $content
            );
        }
        return $content;
    }

    /**
     * Get title for item
     * @throws Throwable
     */
    protected function getTitleForItem(
        \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item
    ): string {
        global $DIC;

        /** @var \ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle $i */
        $i = $item;
        $mmc = $DIC->globalScreen()->collector()->mainmenu();
        return $mmc->getItemInformation()->customTranslationForUser($i)->getTitle();
    }

    public function showTooltips(): bool
    {
        return $this->presentation->showTooltips();
    }

    /**
     * temporary move it here until DIC holds help service instead of ilHelpGUI
     */
    public function internal(): \ILIAS\Help\InternalService
    {
        global $DIC;

        if (is_null(self::$internal_service)) {
            self::$internal_service = new \ILIAS\Help\InternalService(
                $DIC
            );
        }
        return self::$internal_service;
    }
}
