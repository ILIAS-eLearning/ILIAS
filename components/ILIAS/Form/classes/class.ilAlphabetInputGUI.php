<?php

declare(strict_types=1);

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
 * This class represents a text property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAlphabetInputGUI extends ilFormPropertyGUI implements ilToolbarItem
{
    private string $value = "";
    protected array $letters = [];
    protected object $parent_object;
    protected string $parent_cmd = "";
    protected bool $highlight = false;
    protected string $highlight_letter = "";
    protected bool $fix_db_umlauts = false;
    protected ?bool $db_supports_distinct_umlauts = null;
    protected ilDBInterface $db;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        parent::__construct($a_title, $a_postvar);
    }

    /**
     * Only temp fix for #8603, should go to db classes
     * @deprecated
     */
    private function dbSupportsDisctinctUmlauts(): ?bool
    {
        if (!isset($this->db_supports_distinct_umlauts)) {
            $set = $this->db->query(
                "SELECT (" . $this->db->quote("A", "text") . " = " . $this->db->quote("Ä", "text") . ") t FROM DUAL "
            );
            $rec = $this->db->fetchAssoc($set);
            $this->db_supports_distinct_umlauts = !(bool) $rec["t"];
        }

        return $this->db_supports_distinct_umlauts;
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    public function setLetters(array $a_val): void
    {
        $this->letters = $a_val;
    }

    public function getLetters(): array
    {
        return $this->letters;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        return true;
    }

    public function getInput(): string
    {
        return $this->str($this->getPostVar());
    }

    public function setFixDBUmlauts(bool $a_val): void
    {
        $this->fix_db_umlauts = $a_val;
    }

    public function getFixDBUmlauts(): bool
    {
        return $this->fix_db_umlauts;
    }

    public function fixDBUmlauts(string $l): string
    {
        if ($this->fix_db_umlauts && !$this->dbSupportsDisctinctUmlauts()) {
            $l = str_replace(array("Ä", "Ö", "Ü", "ä", "ö", "ü"), array("A", "O", "U", "a", "o", "u"), $l);
        }
        return $l;
    }


    protected function render(string $a_mode = ""): string
    {
        return "";
    }

    public function setParentCommand(
        object $a_obj,
        string $a_cmd
    ): void {
        $this->parent_object = $a_obj;
        $this->parent_cmd = $a_cmd;
    }

    public function setHighlighted(
        string $a_high_letter
    ): void {
        $this->highlight = ($a_high_letter != "");
        $this->highlight_letter = $a_high_letter;
    }

    public function getToolbarHTML(): string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("form");

        $tpl = new ilTemplate("tpl.prop_alphabet.html", true, true, "Services/Form");
        foreach ($this->getLetters() as $l) {
            if (is_null($l)) {
                continue;
            }
            $l = $this->fixDBUmlauts($l);
            $tpl->setCurrentBlock("letter");
            $tpl->setVariable("TXT_LET", $l);
            $ilCtrl->setParameter($this->parent_object, "letter", rawurlencode($l));
            $tpl->setVariable("TXT_LET", $l);
            $tpl->setVariable("HREF_LET", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
            if ($this->highlight && $this->highlight_letter !== null && $this->highlight_letter == $l) {
                $tpl->setVariable("CLASS", ' class="ilHighlighted" ');
            }
            $tpl->parseCurrentBlock();
        }
        $ilCtrl->setParameter($this->parent_object, "letter", "");
        $tpl->setVariable("TXT_ALL", $lng->txt("form_alphabet_all"));
        $tpl->setVariable("HREF_ALL", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
        if ($this->highlight && $this->highlight_letter === null) {
            $tpl->setVariable("CLASSA", ' class="ilHighlighted" ');
        }
        return $tpl->get();
    }
}
