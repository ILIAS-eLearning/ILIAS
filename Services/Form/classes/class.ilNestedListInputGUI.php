<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * This class represents a (nested) list of checkboxes (could be extended for radio items, too)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNestedListInputGUI extends ilFormPropertyGUI
{
    protected string $value = "1";
    protected array $checked = [];
    protected array $list_nodes = [];
    protected ilNestedList $list;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("nested_list");

        $this->list = new ilNestedList();
        $this->list->setListClass("il_Explorer");
    }

    public function addListNode(
        string $a_id,
        string $a_text,
        string $a_parent = "0",
        bool $a_checked = false,
        bool $a_disabled = false,
        string $a_img_src = "",
        string $a_img_alt = "",
        string $a_post_var = ""
    ) {
        $this->list_nodes[$a_id] = array("text" => $a_text,
            "parent" => $a_parent, "checked" => $a_checked, "disabled" => $a_disabled,
            "img_src" => $a_img_src, "img_alt" => $a_img_alt, "post_var" => $a_post_var);
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values) : void
    {
        //		$this->setChecked($a_values[$this->getPostVar()]);
//		foreach($this->getSubItems() as $item)
//		{
//			$item->setValueByArray($a_values);
//		}
    }
    
    public function checkInput() : bool
    {
        return true;
    }

    public function getInput() : array
    {
        return $this->strArray($this->getPostVar());
    }

    public function render() : string
    {
        foreach ($this->list_nodes as $id => $n) {
            if ($n["post_var"] == "") {
                $post_var = $this->getPostVar() . "[]";
            } else {
                $post_var = $n["post_var"];
            }
            $value = $id;
            $item_html = ilLegacyFormElementsUtil::formCheckbox(
                $n["checked"],
                $post_var,
                (string) $value,
                $n["disabled"]
            );
            if ($n["img_src"] != "") {
                $item_html .= ilUtil::img($n["img_src"], $n["img_alt"]) . " ";
            }
            $item_html .= $n["text"];

            $this->list->addListNode($item_html, (string) $id, $n["parent"]);
        }

        return $this->list->getHTML();
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }
}
