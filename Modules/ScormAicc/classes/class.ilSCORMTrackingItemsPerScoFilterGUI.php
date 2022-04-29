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
 * Class ilSCORMTrackingItemsPerScoFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerScoFilterGUI extends ilPropertyFormGUI
{
    public function __construct($a_parent_obj, $a_parent_cmd)//PHP8Review: Missing Typehint
    {
        $this->parent_obj = $a_parent_obj;//PHP8Review: Missing Typehint. Also shouldnt be declared dynamicly
        $this->parent_cmd = $a_parent_cmd;//PHP8Review: Missing Typehint. Also shouldnt be declared dynamicly
        parent::__construct();
    }

    /**
     * @throws ilCtrlException
     */
    public function parse(string $scoSelected, string $report, array $reports) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();//PHP8Review: Missing Typehint. Also shouldnt be declared dynamicly
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));
        $scos = $this->parent_obj->object->getTrackedItems();

        foreach ($scos as $row) {
            $options[$row->getId()] = $row->getTitle();
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
