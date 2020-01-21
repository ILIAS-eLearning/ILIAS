<?php

/**
 * Class ilDclReferenceRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{

    /**
     * @return array|mixed|string
     */
    public function getHTML($link = true)
    {
        $value = $this->getRecordField()->getValue();
        $record_field = $this->getRecordField();

        if (!$value || $value == "-") {
            return "";
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        $html = "";

        foreach ($value as $k => $v) {
            $ref_record = ilDclCache::getRecordCache($v);
            if (!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
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

                    if ($ref_table->getVisibleTableViews($_GET['ref_id'], true)) {
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
     * @param null $link_name
     * @param      $value
     *
     * @return string
     */
    protected function getLinkHTML($link_name = null, $value)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if (!$value || $value == "-") {
            return "";
        }
        $record_field = $this;
        $ref_record = ilDclCache::getRecordCache($value);
        if (!$link_name) {
            $link_name = $ref_record->getRecordFieldHTML($record_field->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
        }
        $ilCtrl->clearParametersByClass(ilDclDetailedViewGUI::class);
        $ilCtrl->setParameterByClass(ilDclDetailedViewGUI::class, "record_id", $ref_record->getId());
        $ilDCLTableView = ilDCLTableView::createOrGetStandardView($ref_record->getTableId());
        $ilCtrl->setParameterByClass(ilDclDetailedViewGUI::class, "tableview_id", $ilDCLTableView->getId());
        $ilCtrl->setParameterByClass(ilDclDetailedViewGUI::class, "back_tableview_id", $_GET['tableview_id']);
        $html = "<a href='" . $ilCtrl->getLinkTargetByClass(ilDclDetailedViewGUI::class, "renderRecord") . "&disable_paging=1'>" . $link_name . "</a>";

        return $html;
    }
}
