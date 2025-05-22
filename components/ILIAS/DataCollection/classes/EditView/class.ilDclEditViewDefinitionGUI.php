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

declare(strict_types=1);

/**
 * @ilCtrl_IsCalledBy ilDclEditViewDefinitionGUI: ilDclTableViewEditGUI
 */
class ilDclEditViewDefinitionGUI extends ilPageObjectGUI
{
    public ilDclTableView $tableview;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(int $tableview_id)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tableview = ilDclTableView::findOrGetInstance($tableview_id);
    }

    public function executeCommand(): string
    {
        if ($this->ctrl->getCmd() === 'saveTable') {
            $this->saveTable();
        }
        $table = new ilDclEditViewTableGUI($this);
        $this->tpl->setContent($table->getHTML());
        return '';
    }

    public function saveTable(): void
    {
        foreach ($this->tableview->getFieldSettings() as $setting) {
            if (!$setting->getFieldObject()->isStandardField() || $setting->getFieldObject()->getId() === 'owner') {

                // Radio Inputs
                $attribute = "RadioGroup";
                $selection_key = $attribute . '_' . $setting->getField();
                $selection = $this->http->wrapper()->post()->retrieve(
                    $selection_key,
                    $this->refinery->kindlyTo()->string()
                );
                $selected_radio_attribute = explode("_", $selection)[0];

                foreach (["LockedEdit", "RequiredEdit", "VisibleEdit", "NotVisibleEdit"] as $radio_attribute) {
                    $result = false;

                    if ($selected_radio_attribute === $radio_attribute) {
                        $result = true;
                    }

                    $setting->{'set' . $radio_attribute}($result);
                }

                // Text Inputs
                $attribute = "DefaultValue";
                $key = $attribute . '_' . $setting->getField();
                if ($this->http->wrapper()->post()->has($key)) {
                    $attribute_value = $this->http->wrapper()->post()->retrieve(
                        $key,
                        $this->refinery->kindlyTo()->string()
                    );
                } else {
                    $attribute_value = "";
                }

                $setting->{'set' . $attribute}($attribute_value);

                $setting->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->ctrl->redirect($this, 'presentation');
    }
}
