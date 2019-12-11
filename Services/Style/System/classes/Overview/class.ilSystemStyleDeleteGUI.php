<?php
include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
include_once("Services/Utilities/classes/class.ilConfirmationTableGUI.php");


/**
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleDeleteGUI
{

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var array
     */
    protected $styles = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * @param ilSkinXML $skin
     * @param ilSkinStyleXML $style
     */
    public function addStyle(ilSkinXML $skin, ilSkinStyleXML $style)
    {
        $this->styles[] = array(
            "var" => "style_" . $skin->getId() . ":" . $style->getId(),
            "id" => $skin->getId() . ":" . $style->getId(),
            "text" => $skin->getName() . " / " . $style->getName(),
            "img" => ilUtil::getImagePath('icon_stys.svg')
        );
    }

    /**
     * @return string
     */
    public function getDeleteStyleFormHTML()
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

    /**
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param array $styles
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;
    }
}
