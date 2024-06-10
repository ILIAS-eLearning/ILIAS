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
 * Class ilDclReferenceRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->getRecordField()->getValue();
        $record_field = $this->getRecordField();

        if (!$value || $value == "-") {
            return "";
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $items = [];

        foreach ($value as $k => $v) {
            $ref_record = ilDclCache::getRecordCache($v);
            if (!$ref_record->getId() || !$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
                //the referenced record_field does not seem to exist.
                unset($value[$k]);
                $value = array_values($value); // resets the keys
                $record_field->setValue($value);
                $record_field->doUpdate();
                continue;
            } else {
                $field = $this->getRecordField()->getField();
                if ($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE_LINK)) {
                    $ref_table = $ref_record->getTable();
                    $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
                    if ($v !== null && $v !== '' && $v !== '-') {
                        $view = $ref_record->getTable()->getFirstTableViewId($ref_id, $this->user->getId(), true);
                        if ($view) {
                            $items[] = $this->getLinkHTML($ref_record, $view);
                            continue;
                        }
                    }
                }
                $items[] = $ref_record->getRecordFieldHTML($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
            }
        }

        return implode('<br>', $items);
    }

    protected function getLinkHTML(ilDclBaseRecordModel $record, int $view): string
    {
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, "table_id", $record->getTableId());
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, "record_id", $record->getId());
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, "tableview_id", $view);
        $html = $this->factory->link()->standard(
            $record->getRecordFieldValue($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE)),
            $this->ctrl->getLinkTargetByClass(
                ilDclDetailedViewGUI::class,
                "renderRecord"
            )
        );

        return $this->renderer->render($html);
    }

    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param string|array $value
     */
    public function parseFormInput($value)
    {
        if (!$value || $value == []) {
            return null;
        }

        return $value;
    }
}
