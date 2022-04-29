<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilSCORM2004TrackingItemsPerScoFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004TrackingItemsPerScoFilterGUI extends ilPropertyFormGUI
{
    protected ilObjSCORM2004LearningModuleGUI $parent_obj;

    protected string $parent_cmd;

    public ilPropertyFormGUI $form;

    public function __construct(ilObjSCORM2004LearningModuleGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * @throws ilCtrlException
     */
    public function parse(string $scoSelected, string $report, array $reports) : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));
        $scos = $this->parent_obj->object->getTrackedItems();
        foreach ($scos as $row) {
            $options[$row["id"]] = $row["title"];
        }
        $si = new ilSelectInputGUI($lng->txt("chapter"), "scoSelected");
        $si->setOptions($options);
        $si->setValue($scoSelected);
        $this->form->addItem($si);

        $options = array("choose" => $lng->txt("please_choose"));
        foreach ($reports as $value) {
            $options[$value] = $lng->txt(strtolower($value));
        }
        $si = new ilSelectInputGUI($lng->txt("report"), "report");
        $si->setOptions($options);
        $si->setValue($report);
        $this->form->addItem($si);
        $this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
    }
}
