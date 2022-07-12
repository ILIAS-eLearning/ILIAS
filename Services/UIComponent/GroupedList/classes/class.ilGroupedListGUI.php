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
 * Grouped list GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGroupedListGUI
{
    protected ilCtrl $ctrl;
    protected bool $multi_column = false;
    protected array $items = array();
    protected bool $as_dropdown = false;
    protected bool $dd_pullright = false;
    protected string $id;
    
    public function __construct(string $id = "")
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->id = $id;
        $this->ctrl = $DIC->ctrl();
    }
    
    public function setAsDropDown(
        bool $a_val,
        bool $a_pullright = false
    ) : void {
        $this->as_dropdown = $a_val;
        $this->dd_pullright = $a_pullright;
    }
    
    public function getAsDropDown() : bool
    {
        return $this->as_dropdown;
    }
    
    public function addGroupHeader(
        string $a_content,
        string $a_add_class = ""
    ) : void {
        $this->items[] = array("type" => "group_head", "content" => $a_content,
            "add_class" => $a_add_class);
    }
    
    public function addSeparator() : void
    {
        $this->items[] = array("type" => "sep");
    }
    
    public function nextColumn() : void
    {
        $this->items[] = array("type" => "next_col");
        $this->multi_column = true;
    }

    public function addEntry(
        string $a_content,
        string $a_href = "",
        string $a_target = "",
        string $a_onclick = "",
        string $a_add_class = "",
        string $a_id = "",
        string $a_ttip = "",
        string $a_tt_my = "right center",
        string $a_tt_at = "left center",
        bool $a_tt_use_htmlspecialchars = true
    ) : void {
        $this->items[] = array("type" => "entry", "content" => $a_content,
            "href" => $a_href, "target" => $a_target, "onclick" => $a_onclick,
            "add_class" => $a_add_class, "id" => $a_id, "ttip" => $a_ttip,
            "tt_my" => $a_tt_my, "tt_at" => $a_tt_at,
            "tt_use_htmlspecialchars" => $a_tt_use_htmlspecialchars);
    }
    
    public function getHTML() : string
    {
        $ilCtrl = $this->ctrl;
        
        $tpl = new ilTemplate("tpl.grouped_list.html", true, true, "Services/UIComponent/GroupedList");
        $tt_calls = "";
        foreach ($this->items as $i) {
            switch ($i["type"]) {
                case "sep":
                    $tpl->touchBlock("sep");
                    $tpl->touchBlock("item");
                    break;
                    
                case "next_col":
                    $tpl->touchBlock("next_col");
                    $tpl->touchBlock("item");
                    break;
                    
                case "group_head":
                    $tpl->setCurrentBlock("group_head");
                    if ($i["add_class"] != "") {
                        $tpl->setVariable("ADD_CLASS", $i["add_class"]);
                    }
                    $tpl->setVariable("GROUP_HEAD", $i["content"]);
                    $tpl->parseCurrentBlock();
                    $tpl->touchBlock("item");
                    break;
                    
                case "entry":
                    if ($i["href"] != "") {
                        $tpl->setCurrentBlock("linked_entry");
                        if ($i["add_class"] != "") {
                            $tpl->setVariable("ADD_CLASS", $i["add_class"]);
                        }
                        $tpl->setVariable("HREF", str_replace('&amp;', '&', ilUtil::secureUrl($i["href"])));
                        $tpl->setVariable("TXT_ENTRY", $i["content"]);
                        if ($i["target"] != "") {
                            $tpl->setVariable("TARGET", 'target="' . $i["target"] . '"');
                        } else {
                            $tpl->setVariable("TARGET", 'target="_top"');
                        }
                        if ($i["onclick"] != "") {
                            $tpl->setVariable("ONCLICK", 'onclick="' . $i["onclick"] . '"');
                        }
                        if ($i["id"] != "") {
                            $tpl->setVariable("ID", 'id="' . $i["id"] . '"');
                        }
                        $tpl->parseCurrentBlock();
                        $tpl->touchBlock("item");
                        if ($i["ttip"] != "" && $i["id"] != "") {
                            if ($ilCtrl->isAsynch()) {
                                $tt_calls .= " " . ilTooltipGUI::getToolTip(
                                    $i["id"],
                                    $i["ttip"],
                                    "",
                                    $i["tt_my"],
                                    $i["tt_at"],
                                    $i["tt_use_htmlspecialchars"]
                                );
                            } else {
                                ilTooltipGUI::addTooltip(
                                    $i["id"],
                                    $i["ttip"],
                                    "",
                                    $i["tt_my"],
                                    $i["tt_at"],
                                    $i["tt_use_htmlspecialchars"]
                                );
                            }
                        }
                    } else {
                        $tpl->setCurrentBlock("unlinked_entry");
                        if ($i["add_class"] != "") {
                            $tpl->setVariable("ADD_CLASS2", $i["add_class"]);
                        }
                        $tpl->setVariable("TXT_ENTRY2", $i["content"]);
                        $tpl->parseCurrentBlock();
                    }
                    break;
            }
        }
        
        if ($this->multi_column) {
            $tpl->touchBlock("multi_start");
            $tpl->touchBlock("multi_end");
        }
        
        if ($tt_calls !== "") {
            $tpl->setCurrentBlock("script");
            $tpl->setVariable("TT_CALLS", $tt_calls);
            $tpl->parseCurrentBlock();
        }

        if ($this->id !== "") {
            $tpl->setCurrentBlock("id");
            $tpl->setVariable("ID", $this->id);
            $tpl->parseCurrentBlock();
        }

        if ($this->getAsDropDown()) {
            if ($this->dd_pullright) {
                $tpl->setVariable("LIST_CLASS", "dropdown-menu pull-right");
            } else {
                $tpl->setVariable("LIST_CLASS", "dropdown-menu");
            }
            $tpl->setVariable("LIST_ROLE", 'role="menu"');
        } else {
            $tpl->setVariable("LIST_CLASS", "");
            $tpl->setVariable("LIST_ROLE", "");
        }
        
        return $tpl->get();
    }
}
