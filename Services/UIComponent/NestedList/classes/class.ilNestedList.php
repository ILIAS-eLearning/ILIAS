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
 * Nested List
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 11
 */
class ilNestedList
{
    protected string $item_class = "il_Explorer";
    protected array $list_class = array();
    protected bool $auto_numbering = false;
    protected array $nr = array();
    protected array $nodes = [];
    protected array $childs = [];

    public function __construct()
    {
        $this->list_class[0] = "il_Explorer";
        $this->childs[0] = array();
    }

    // Set li class
    public function setItemClass(string $a_val) : void
    {
        $this->item_class = $a_val;
    }

    public function getItemClass() : string
    {
        return $this->item_class;
    }

    // Set list class
    public function setListClass(string $a_val, int $a_depth = 0) : void
    {
        $this->list_class[$a_depth] = $a_val;
    }

    public function getListClass(int $a_depth = 0) : string
    {
        return $this->list_class[$a_depth] ?? "";
    }

    public function addListNode(
        string $a_content,
        string $a_id,
        $a_parent = 0
    ) : void {
        $this->nodes[$a_id] = $a_content;
        $this->childs[$a_parent][] = $a_id;
    }

    public function setAutoNumbering(bool $a_val) : void
    {
        $this->auto_numbering = $a_val;
    }

    public function getAutoNumbering() : bool
    {
        return $this->auto_numbering;
    }

    public function getNumbers() : array
    {
        return $this->nr;
    }

    public function getHTML() : string
    {
        $tpl = new ilTemplate("tpl.nested_list.html", true, true, "Services/UIComponent/NestedList");

        $nr = array();
        $depth = 1;
        if (isset($this->childs[0]) && count($this->childs[0]) > 0) {
            $this->listStart($tpl, $depth);
            foreach ($this->childs[0] as $child) {
                $this->renderNode($child, $tpl, $depth, $nr);
            }
            $this->listEnd($tpl);
        }

        return $tpl->get();
    }

    public function renderNode(
        $a_id,
        ilTemplate $tpl,
        int $depth,
        array &$nr
    ) : void {
        if (!isset($nr[$depth])) {
            $nr[$depth] = 1;
        } else {
            $nr[$depth]++;
        }

        $nr_str = $sep = "";
        if ($this->getAutoNumbering()) {
            for ($i = 1; $i <= $depth; $i++) {
                $nr_str .= $sep . $nr[$i];
                $sep = ".";
            }
        }

        $this->listItemStart($tpl);
        $tpl->setCurrentBlock("content");
        $tpl->setVariable("CONTENT", $nr_str . " " . $this->nodes[$a_id]);
        $this->nr[$a_id] = $nr_str;
        //echo "<br>".$this->nodes[$a_id];
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("tag");

        if (isset($this->childs[$a_id]) && count($this->childs[$a_id]) > 0) {
            $this->listStart($tpl, $depth + 1);
            foreach ($this->childs[$a_id] as $child) {
                $this->renderNode($child, $tpl, $depth + 1, $nr);
            }
            $this->listEnd($tpl);
        }
        unset($nr[$depth + 1]);

        $this->listItemEnd($tpl);
    }

    public function listItemStart(ilTemplate $tpl) : void
    {
        if ($this->getItemClass() !== "") {
            $tpl->setCurrentBlock("list_item_start");
            $tpl->setVariable("LI_CLASS", ' class="' . $this->getItemClass() . '" ');
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock("list_item_start");
        }
        $tpl->touchBlock("tag");
    }

    public function listItemEnd(ilTemplate $tpl) : void
    {
        $tpl->touchBlock("list_item_end");
        $tpl->touchBlock("tag");
    }

    public function listStart(ilTemplate $tpl, int $depth) : void
    {
        //echo "<br>listStart";

        $class = ($this->getListClass($depth) !== "")
            ? $this->getListClass($depth)
            : $this->getListClass();
        //echo "-$class-";
        if ($class !== "") {
            $tpl->setCurrentBlock("list_start");
            $tpl->setVariable("UL_CLASS", ' class="' . $class . '" ');
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock("list_start");
        }
        $tpl->touchBlock("tag");
    }

    public function listEnd(ilTemplate $tpl) : void
    {
        //echo "<br>listEnd";
        $tpl->touchBlock("list_end");
        $tpl->touchBlock("tag");
    }
}
