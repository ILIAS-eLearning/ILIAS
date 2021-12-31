<?php declare(strict_types=1);

class ilSystemStyleDeleteGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected array $styles = [];

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public function addStyle(ilSkinXML $skin, ilSkinStyleXML $style)
    {
        $this->styles[] = array(
            "var" => "style_" . $skin->getId() . ":" . $style->getId(),
            "id" => $skin->getId() . ":" . $style->getId(),
            "text" => $skin->getName() . " / " . $style->getName(),
            "img" => ilUtil::getImagePath('icon_stys.svg')
        );
    }

    public function getDeleteStyleFormHTML() : string
    {
        ilUtil::sendQuestion($this->lng->txt("info_delete_sure"), true);

        $table_form = new ilConfirmationTableGUI(true);
        $table_form->setFormName("delete_style");

        $table_form->addCommandButton('confirmDelete', $this->lng->txt('confirm'));
        $table_form->addCommandButton('cancel', $this->lng->txt('cancel'));
        $table_form->setFormAction($this->ctrl->getFormActionByClass("ilSystemStyleOverviewGUI"));
        $table_form->setData($this->getStyles());
        return $table_form->getHTML();
    }

    public function getStyles() : array
    {
        return $this->styles;
    }

    public function setStyles(array $styles) : void
    {
        $this->styles = $styles;
    }
}
