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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSettingsChangeConfirmationGUI extends ilConfirmationGUI
{
    protected ilObjTest $testOBJ;
    private ?string $oldQuestionSetType;
    private ?string $newQuestionSetType;
    private ?bool $questionLossInfoEnabled;

    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
        
        parent::__construct();
    }

    public function setOldQuestionSetType(string $oldQuestionSetType) : void
    {
        $this->oldQuestionSetType = $oldQuestionSetType;
    }

    public function getOldQuestionSetType() : ?string
    {
        return $this->oldQuestionSetType;
    }

    public function setNewQuestionSetType(string $newQuestionSetType) : void
    {
        $this->newQuestionSetType = $newQuestionSetType;
    }

    public function getNewQuestionSetType() : string
    {
        return $this->newQuestionSetType;
    }

    /**
     * @param bool $questionLossInfoEnabled
     */
    public function setQuestionLossInfoEnabled(bool $questionLossInfoEnabled) : void
    {
        $this->questionLossInfoEnabled = $questionLossInfoEnabled;
    }

    public function isQuestionLossInfoEnabled() : bool
    {
        return $this->questionLossInfoEnabled;
    }

    private function buildHeaderText() : string
    {
        $headerText = sprintf(
            $this->lng->txt('tst_change_quest_set_type_from_old_to_new_with_conflict'),
            $this->testOBJ->getQuestionSetTypeTranslation($this->lng, $this->getOldQuestionSetType()),
            $this->testOBJ->getQuestionSetTypeTranslation($this->lng, $this->getNewQuestionSetType())
        );

        if ($this->isQuestionLossInfoEnabled()) {
            $headerText .= '<br /><br />' . $this->lng->txt('tst_nonpool_questions_get_lost_warning');
        }

        return $headerText;
    }

    public function build() : void
    {
        $this->setHeaderText($this->buildHeaderText());
    }

    public function populateParametersFromPost() : void
    {
        foreach ($_POST as $key => $value) {
            if (strcmp($key, "cmd") != 0) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $this->addHiddenItem("{$key}[{$k}]", $v);
                    }
                } else {
                    $this->addHiddenItem($key, $value);
                }
            }
        }
    }

    public function populateParametersFromPropertyForm(ilPropertyFormGUI $form, $timezone) : void
    {
        foreach ($form->getInputItemsRecursive() as $key => $item) {
            switch ($item->getType()) {
                case 'section_header':

                    continue 2;

                case 'datetime':

                    $datetime = $item->getDate();
                    if ($datetime instanceof ilDateTime && !$datetime->isNull()) {
                        $parts = explode(' ', $datetime->get(IL_CAL_DATETIME));
                        if ($datetime instanceof ilDate) {
                            $this->addHiddenItem($item->getPostVar(), $parts[0]);
                        } else {
                            $this->addHiddenItem($item->getPostVar(), $parts[0] . ' ' . $parts[1]);
                        }
                    } else {
                        $this->addHiddenItem($item->getPostVar(), '');
                    }

                    break;

                case 'duration':

                    $this->addHiddenItem("{$item->getPostVar()}[MM]", (string) $item->getMonths());
                    $this->addHiddenItem("{$item->getPostVar()}[dd]", (string) $item->getDays());
                    $this->addHiddenItem("{$item->getPostVar()}[hh]", (string) $item->getHours());
                    $this->addHiddenItem("{$item->getPostVar()}[mm]", (string) $item->getMinutes());
                    $this->addHiddenItem("{$item->getPostVar()}[ss]", (string) $item->getSeconds());

                    break;

                case 'dateduration':

                    foreach (["start", "end"] as $type) {
                        $postVar = $item->getPostVar() . '[' . $type . ']';
                        $datetime = $item->{'get' . ucfirst($type)}();

                        if ($datetime instanceof ilDateTime && !$datetime->isNull()) {
                            $parts = explode(' ', $datetime->get(IL_CAL_DATETIME));
                            if ($datetime instanceof ilDate) {
                                $this->addHiddenItem($postVar, $parts[0]);
                            } else {
                                $this->addHiddenItem($postVar, $parts[0] . ' ' . $parts[1]);
                            }
                        } else {
                            $this->addHiddenItem($postVar, '');
                        }
                    }

                    break;

                case 'checkboxgroup':

                    if (is_array($item->getValue())) {
                        foreach ($item->getValue() as $option) {
                            $this->addHiddenItem("{$item->getPostVar()}[]", $option);
                        }
                    }

                    break;

                case 'select':

                    $value = $item->getValue();
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    foreach ($value as $option) {
                        $this->addHiddenItem("{$item->getPostVar()}[]", $option);
                    }

                    break;

                case 'checkbox':

                    if ($item->getChecked()) {
                        $this->addHiddenItem($item->getPostVar(), '1');
                    }

                    break;

                default:

                    $this->addHiddenItem($item->getPostVar(), (string) $item->getValue());
            }
        }
    }
}
