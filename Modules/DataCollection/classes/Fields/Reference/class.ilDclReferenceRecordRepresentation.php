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
 ********************************************************************
 */

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

        $html = "";

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
                    $ref_record = ilDclCache::getRecordCache($v);
                    $ref_table = $ref_record->getTable();

                    $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

                    if ($ref_table->getVisibleTableViews($ref_id, true)) {
                        $html .= $this->getLinkHTML(null, $v);
                    } else {
                        $html .= $ref_record->getRecordFieldHTML($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
                    }
                } else {
                    $html .= $ref_record->getRecordFieldHTML($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
                }
            }
            $html .= '<br>';
        }

        $html = substr($html, 0, -4); // cut away last <br>

        return $html;
    }

    /**
     * @param string|null|int $value
     */
    protected function getLinkHTML(?string $link_name = null, $value): string
    {
        if (!$value || $value == "-") {
            return "";
        }
        $record_field = $this;
        $ref_record = ilDclCache::getRecordCache($value);
        if (!$link_name) {
            $link_name = $ref_record->getRecordFieldHTML($record_field->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
        }
        $this->ctrl->clearParametersByClass(ilDclDetailedViewGUI::class);
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, "record_id", $ref_record->getId());
        $table_obj = ilDclCache::getTableCache($ref_record->getTableId());
        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, "tableview_id", $table_obj->getFirstTableViewId($ref_id));
        $html = $this->factory->link()->standard($link_name, $this->ctrl->getLinkTargetByClass(
            ilDclDetailedViewGUI::class,
            "renderRecord"
        ));

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
