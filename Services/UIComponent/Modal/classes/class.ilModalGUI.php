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
 * Modal class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 11
 */
class ilModalGUI
{
    protected string $heading = "";
    protected string $body = "";
    protected string $id = "";

    public const TYPE_LARGE = "large";
    public const TYPE_MEDIUM = "medium";
    public const TYPE_SMALL = "small";

    protected string $type = self::TYPE_MEDIUM;
    protected array $buttons = array();

    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        return new ilModalGUI();
    }

    public function setId(string $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setHeading(string $a_val): void
    {
        $this->heading = $a_val;
    }


    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setBody(string $a_val): void
    {
        $this->body = $a_val;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Set type
     *
     * @param string $a_val type const ilModalGUI::TYPE_SMALL|ilModalGUI::TYPE_MEDIUM|ilModalGUI::TYPE_LARGE
     */
    public function setType(string $a_val): void
    {
        $this->type = $a_val;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function addButton(ilButtonBase $but): void
    {
        $this->buttons[] = $but;
    }

    /**
     * Get buttons
     * @return ilButtonBase[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function getHTML(): string
    {
        $tpl = new ilTemplate("tpl.modal.html", true, true, "Services/UIComponent/Modal");

        if (count($this->getButtons()) > 0) {
            foreach ($this->getButtons() as $b) {
                $tpl->setCurrentBlock("button");
                $tpl->setVariable("BUTTON", $b->render());
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("footer");
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("HEADING", $this->getHeading());

        $tpl->setVariable("MOD_ID", $this->getId());
        $tpl->setVariable("BODY", $this->getBody());

        switch ($this->getType()) {
            case self::TYPE_LARGE:
                $tpl->setVariable("CLASS", "modal-lg");
                break;

            case self::TYPE_SMALL:
                $tpl->setVariable("CLASS", "modal-sm");
                break;
        }

        return $tpl->get();
    }

    public static function initJS(ilGlobalTemplateInterface $a_main_tpl = null): void
    {
        global $DIC;

        $tpl = $a_main_tpl ?? $DIC["tpl"];

        $tpl->addJavaScript("./Services/UIComponent/Modal/js/Modal.js");
    }
}
