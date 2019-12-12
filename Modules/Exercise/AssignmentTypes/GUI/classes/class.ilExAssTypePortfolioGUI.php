<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/interface.ilExAssignmentTypeGUIInterface.php");
include_once("./Modules/Exercise/AssignmentTypes/GUI/traits/trait.ilExAssignmentTypeGUIBase.php");

/**
 * Portfolio type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypePortfolioGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form)
    {
        $lng = $this->lng;

        $rd_template = new ilRadioGroupInputGUI($lng->txt("exc_template"), "template");
        $rd_template->setRequired(true);
        $radio_no_template = new ilRadioOption($lng->txt("exc_without_template"), 0, $lng->txt("exc_without_template_info", "without_template_info"));
        $radio_with_template = new ilRadioOption($lng->txt("exc_with_template"), 1, $lng->txt("exc_with_template_info", "with_template_info"));

        include_once "Services/Form/classes/class.ilRepositorySelector2InputGUI.php";
        $repo = new ilRepositorySelector2InputGUI($lng->txt("exc_portfolio_template"), "template_id");
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes(array("prtt"));
        $repo->getExplorerGUI()->setTypeWhiteList(array("root", "prtt", "cat", "crs", "grp"));
        $radio_with_template->addSubItem($repo);

        $rd_template->addOption($radio_no_template);
        $rd_template->addOption($radio_with_template);
        $form->addItem($rd_template);
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form)
    {
        $ass->setPortfolioTemplateId(0);
        if ($form->getInput("template_id") && $form->getInput("template")) {
            $ass->setPortfolioTemplateId($form->getInput("template_id"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass)
    {
        $values = [];

        if ($ass->getPortfolioTemplateId() > 0) {
            $values["template_id"] = $ass->getPortfolioTemplateId();
            $values["template"] = 1;
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
    }
}
