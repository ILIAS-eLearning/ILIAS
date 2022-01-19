<?php
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

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function parse($scoSelected, $report, $reports): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
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
        for ($i = 0;$i < count($reports);$i++) {
            $options[$reports[$i]] = $lng->txt(strtolower($reports[$i]));
        }
        $si = new ilSelectInputGUI($lng->txt("report"), "report");
        $si->setOptions($options);
        $si->setValue($report);
        $this->form->addItem($si);
        $this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
    }
}
