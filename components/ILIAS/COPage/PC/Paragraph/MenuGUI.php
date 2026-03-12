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

declare(strict_types=1);

namespace ILIAS\COPage\PC\Paragraph;

use ILIAS\COPage\InternalGUIService;
use ILIAS\COPage\InternalDomainService;

class MenuGUI
{
    public function __construct(
        protected InternalDomainService $domain_service,
        protected InternalGUIService $gui_service,
        protected \ILIAS\Style\Content\InternalService $style_service
    ) {
    }

    public function render(
        string $a_par_type,
        bool $a_int_links = false,
        bool $a_wiki_links = false,
        bool $a_keywords = false,
        $a_style_id = 0,
        $a_paragraph_styles = true,
        $a_save_return = true,
        $a_anchors = false,
        $a_save_new = true,
        $a_user_links = false
    ): string {
        $style_service = $this->style_service;

        $user_id = $this->domain_service->user()->getId();
        $ui_wrapper = $this->gui_service->edit()->uiWrapper();
        $lng = $this->domain_service->lng();
        $lng->loadLanguageModule("copg");
        $ctrl = $this->gui_service->ctrl();
        $ui = $this->gui_service->ui();

        $ui->renderer()->renderAsync($ui->factory()->legacy()->content(""));

        $page_config = $this->domain_service->pageConfig($a_par_type);

        $style_access_manager = $style_service->domain()->access(
            0,
            $user_id
        );
        $char_manager = $style_service->domain()->characteristic(
            $a_style_id,
            $style_access_manager
        );

        $aset = new \ilSetting("adve");

        $f = static function (string $type, string $code) use ($char_manager, $lng): string {
            $title = $char_manager->getPresentationTitle("text_inline", $type);
            if ($title === $type) {
                $title = $lng->txt("cont_char_style_" . $code);
            }
            return $title;
        };

        // character styles
        $chars = [];
        if ($a_style_id === 0) {
            $chars = array(
                "Comment" => array("code" => "com", "txt" => $f("Comment", "com")),
                "Quotation" => array("code" => "quot", "txt" => $f("Quotation", "quot")),
                "Accent" => array("code" => "acc", "txt" => $f("Accent", "acc")),
                "Code" => array("code" => "code", "txt" => $f("Code", "code"))
            );
        }
        foreach (\ilPCParagraphGUI::_getTextCharacteristics($a_style_id, true) as $c) {
            if (in_array($c, ["Strong", "Important", "Emph"])) {
                continue;
            }
            if (!isset($chars[$c])) {
                $title = $char_manager->getPresentationTitle("text_inline", $c);
                switch ($c) {
                    case "CodeInline":
                        $chars["Code"] = array("code" => "code", "txt" => $f("Code", "code"));
                        break;
                    case "Comment":
                        $chars["Comment"] = array("code" => "com", "txt" => $f("Comment", "com"));
                        break;
                    case "Quotation":
                        $chars["Quotation"] = array("code" => "quot", "txt" => $f("Quotation", "quot"));
                        break;
                    case "Accent":
                        $chars["Accent"] = array("code" => "acc", "txt" => $f("Accent", "acc"));
                        break;
                    default:
                        $chars[$c] = array("code" => "", "txt" => $title);
                        break;
                }
            }
        }
        $char_formats = [];
        foreach ($chars as $key => $char) {
            if (\ilPageEditorSettings::lookupSettingByParentType(
                $a_par_type,
                "active_" . $char["code"],
                "1"
            )) {
                $t = "text_inline";
                $tag = "span";
                switch ($key) {
                    case "Code": $tag = "code";
                        break;
                }
                $html = '<' . $tag . ' class="ilc_' . $t . '_' . $key . '" style="font-size:90%; margin-top:2px; margin-bottom:2px; position:static;">' . $char["txt"] . "</" . $tag . ">";
                $char_formats[] = ["text" => $html, "action" => "selection.format", "data" => ["format" => $key]];
            }
        }


        $numbered_list = $ui->renderer()->render(
            $ui->factory()->symbol()->glyph()->numberedlist()
        );

        $bullet_list = $ui->renderer()->render(
            $ui->factory()->symbol()->glyph()->bulletlist()
        );

        $indent = $ui->renderer()->render(
            $ui->factory()->symbol()->glyph()->listindent()
        );

        $outdent = $ui->renderer()->render(
            $ui->factory()->symbol()->glyph()->listoutdent()
        );

        // menu
        $str = "str";
        $emp = "emp";
        $imp = "imp";
        if ($aset->get("use_physical")) {
            $str = "B";
            $emp = "I";
            $imp = "U";
        }
        $c_formats = [];
        foreach (["str", "emp", "imp", "sup", "sub"] as $c) {
            if (\ilPageEditorSettings::lookupSettingByParentType(
                $a_par_type,
                "active_" . $c,
                "1"
            )) {
                switch ($c) {
                    case "str":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Strong">' . $str . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Strong"],
                                        "aria-label" => $lng->txt("cont_text_str")
                        ];
                        break;
                    case "emp":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Emph">' . $emp . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Emph"],
                                        "aria-label" => $lng->txt("cont_text_emp")
                        ];
                        break;
                    case "imp":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Important">' . $imp . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Important"],
                                        "aria-label" => $lng->txt("cont_text_imp")
                        ];
                        break;
                    case "sup":
                        $c_formats[] = ["text" => 'x<sup>2</sup>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Sup"],
                                        "aria-label" => $lng->txt("cont_text_sup")
                        ];
                        break;
                    case "sub":
                        $c_formats[] = ["text" => 'x<sub>2</sub>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Sub"],
                                        "aria-label" => $lng->txt("cont_text_sub")
                        ];
                        break;
                }
            }
        }
        $c_formats[] = ["text" => "<i>A</i>",
                        "action" => $char_formats,
                        "aria-label" => $lng->txt("copg_more_character_formats")
        ];
        $c_formats[] = ["text" => '<i><strong><u>T</u></strong><sub>x</sub></i>',
                        "action" => "selection.removeFormat",
                        "data" => [],
                        "aria-label" => $lng->txt("copg_remove_formats")
        ];
        $menu = [
            "cont_char_format" => $c_formats,
            "cont_lists" => [
                [
                    "text" => $bullet_list,
                    "action" => "list.bullet",
                    "data" => [],
                    "aria-label" => $lng->txt("cont_bullet_list")
                ],
                [
                    "text" => $numbered_list,
                    "action" => "list.number",
                    "data" => [],
                    "aria-label" => $lng->txt("cont_numbered_list")
                ],
                [
                    "text" => $outdent,
                    "action" => "list.outdent",
                    "data" => [],
                    "aria-label" => $lng->txt("cont_list_outdent")
                ],
                [
                    "text" => $indent,
                    "action" => "list.indent",
                    "data" => [],
                    "aria-label" => $lng->txt("cont_list_indent")
                ]
            ]
        ];

        // bullet lists
        $ulists = \ilPCListGUI::_getListCharacteristics($a_style_id, "list_u");
        $olists = \ilPCListGUI::_getListCharacteristics($a_style_id, "list_o");
        $ilists = \ilPCListGUI::_getListCharacteristics($a_style_id, "list_item");
        if (count($ulists) > 1) {
            $la = [];
            foreach ($ulists as $c) {
                $la[] = ["action" => "list.bulletStyle", "text" => $c, "data" => ["format" => $c]];
            }
            $menu["copg_list_styles"][] = ["text" => $lng->txt("copg_list_style"), "action" => $la];
        }
        if (count($olists) > 1) {
            $la = [];
            foreach ($olists as $c) {
                $la[] = ["action" => "list.numberStyle", "text" => $c, "data" => ["format" => $c]];
            }
            $menu["copg_list_styles"][] = ["text" => $lng->txt("copg_list_style"), "action" => $la];
        }
        if (count($ilists) > 1) {
            $la = [];
            foreach ($ilists as $c) {
                $la[] = ["action" => "list.itemStyle", "text" => $c, "data" => ["format" => $c]];
            }
            $menu["copg_list_styles"][] = ["text" => $lng->txt("copg_list_item_style"), "action" => $la];
        }

        // more...

        // links
        $links = [];
        if ($a_wiki_links) {
            $links[] = ["text" => $lng->txt("cont_wiki_link_dialog"), "action" => "link.wikiSelection", "data" => [
                "url" => $ctrl->getLinkTargetByClass("ilwikipagegui", "")]];
            $links[] = ["text" => "[[" . $lng->txt("cont_wiki_page") . "]]", "action" => "link.wiki", "data" => []];
        }
        if ($a_int_links) {
            $links[] = ["text" => $lng->txt("cont_text_iln_link"), "action" => "link.internal", "data" => []];
        }
        if (\ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_xln",
            "1"
        )) {
            $links[] = ["text" => $lng->txt("cont_text_xln"), "action" => "link.external", "data" => []];
        }
        if ($a_user_links) {
            $links[] = ["text" => $lng->txt("cont_link_user"), "action" => "link.user", "data" => []];
        }


        // more
        $menu["cont_more_functions"] = [];
        $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_link") . '<i class="mce-ico mce-i-link"></i>', "action" => $links];

        if ($a_keywords) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_keyword"), "action" => "selection.keyword", "data" => []];
        }
        $mathJaxSetting = new \ilSetting("MathJax");
        if (\ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_tex",
            "1"
        )) {
            $menu["cont_more_functions"][] = ["text" => 'Tex', "action" => "selection.tex", "data" => []];
        }
        if (\ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_fn",
            "1"
        )) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_footnote"), "action" => "selection.fn", "data" => []];
        }
        if ($a_anchors) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_anchor"), "action" => "selection.anchor", "data" => []];
        }

        // text templates
        $templates = [];
        foreach ($page_config->getTextTemplates() as $k => $v) {
            $k = $this->gui_service->html()->escape($k);
            $v = $this->gui_service->html()->escape($v);
            $templates[] = ["text" => $k, "action" => "text.template", "data" => [
                "content" => $v
            ]];
        }
        if (count($templates) > 0) {
            $menu[""][] = ["text" => $page_config->getTextTemplatesDropdownCaption() . '<i class="mce-ico mce-i-link"></i>', "action" => $templates];
        }

        $btpl = new \ilTemplate("tpl.tiny_menu.html", true, true, "components/ILIAS/COPage");

        foreach ($menu as $section_title => $section) {
            foreach ($section as $item) {
                if (is_array($item["action"])) {
                    $buttons = [];
                    foreach ($item["action"] as $i) {
                        $buttons[] = $ui_wrapper->getButton(
                            $i["text"],
                            "par-action",
                            $i["action"],
                            $i["data"],
                            "",
                            false,
                            $i["aria-label"] ?? ""
                        );
                    }
                    $dd = $ui->factory()->dropdown()->standard($buttons)->withLabel($item["text"])
                             ->withAriaLabel($item["aria-label"] ?? "");
                    $btpl->setCurrentBlock("button");
                    $btpl->setVariable("BUTTON", $ui->renderer()->renderAsync($dd));
                } else {
                    $b = $ui_wrapper->getRenderedButton(
                        $item["text"],
                        "par-action",
                        $item["action"],
                        $item["data"],
                        "",
                        false,
                        $item["aria-label"] ?? ""
                    );
                    $btpl->setCurrentBlock("button");
                    $btpl->setVariable("BUTTON", $b);
                }
                $btpl->parseCurrentBlock();
            }
            if ($section_title !== "") {
                $btpl->setCurrentBlock("sec_head");
                $btpl->setVariable("TXT_SECTION", $lng->txt($section_title));
                $btpl->parseCurrentBlock();
            }
            $btpl->setCurrentBlock("section");
            $btpl->parseCurrentBlock();
        }


        if ($a_paragraph_styles) {
            $sel = new \ParagraphStyleSelector($ui_wrapper, $a_style_id);
            $dd = $sel->getStyleSelector(" ");
            $btpl->setCurrentBlock("par_edit");
            $btpl->setVariable("TXT_PAR_FORMAT", $lng->txt("cont_par_format"));

            $btpl->setVariable("STYLE_SELECTOR", $ui->renderer()->renderAsync($dd));

            $btpl->parseCurrentBlock();
        }

        // block styles
        $sel = new \SectionStyleSelector($ui_wrapper, $a_style_id);
        $dd = $sel->getStyleSelector(" ", $type = "par-action", $action = "sec.class", $attr = "class", true);
        $btpl->setVariable("TXT_BLOCK", $lng->txt("cont_sur_block_format"));
        $btpl->setVariable("BLOCK_STYLE_SELECTOR", $ui->renderer()->renderAsync($dd));

        $btpl->setVariable("TINY_HEADER", $lng->txt("cont_text_editing"));
        $btpl->setVariable(
            "SPLIT_BUTTON",
            $ui_wrapper->getRenderedButton($lng->txt("cont_quit_text_editing"), "par-action", "save.return")
        );

        $btpl->setVariable("TXT_SAVING", $lng->txt("cont_saving"));
        $btpl->setVariable("SRC_LOADER", \ilUtil::getImagePath("media/loader.svg"));

        return $btpl->get();
    }
}
