<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Text type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeTextGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @var ilLanguage
     */
    protected $lng;
    protected $requested_min_char_limit = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_min_char_limit = $request->getMinCharLimit();
    }

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form)
    {
        $lng = $this->lng;

        $rb_limit_chars = new ilCheckboxInputGUI($lng->txt("exc_limit_characters"), "limit_characters");

        $min_char_limit = new ilNumberInputGUI($lng->txt("exc_min_char_limit"), "min_char_limit");
        $min_char_limit->allowDecimals(false);
        $min_char_limit->setMinValue(0);
        $min_char_limit->setSize(3);

        $max_char_limit = new ilNumberInputGUI($lng->txt("exc_max_char_limit"), "max_char_limit");
        $max_char_limit->allowDecimals(false);
        $max_char_limit->setMinValue($this->requested_min_char_limit + 1);

        $max_char_limit->setSize(3);

        $rb_limit_chars->addSubItem($min_char_limit);
        $rb_limit_chars->addSubItem($max_char_limit);

        $form->addItem($rb_limit_chars);
    }

    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form)
    {
        $ass->setMaxCharLimit(0);
        $ass->setMinCharLimit(0);
        if ($form->getInput("limit_characters") && $form->getInput("max_char_limit")) {
            $ass->setMaxCharLimit($form->getInput("max_char_limit"));
        }
        if ($form->getInput("limit_characters") && $form->getInput("min_char_limit")) {
            $ass->setMinCharLimit($form->getInput("min_char_limit"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass)
    {
        $values = [];
        if ($ass->getMinCharLimit()) {
            $values['limit_characters'] = 1;
            $values['min_char_limit'] = $ass->getMinCharLimit();
        }
        if ($ass->getMaxCharLimit()) {
            $values['limit_characters'] = 1;
            $values['max_char_limit'] = $ass->getMaxCharLimit();
        }

        return $values;
    }

    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission) : void
    {
    }
}
