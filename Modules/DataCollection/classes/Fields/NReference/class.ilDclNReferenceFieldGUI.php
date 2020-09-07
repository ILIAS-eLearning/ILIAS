<?php

/**
 * Class ilDclNReferenceFieldGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclNReferenceFieldGUI
{

    /**
     * @var ilDclNReferenceRecordFieldModel
     */
    protected $field;


    /**
     * @param ilDclNReferenceRecordFieldModel $field
     */
    public function __construct(ilDclNReferenceRecordFieldModel $field)
    {
        $this->field = $field;
    }


    /**
     * @param ilDclNReferenceRecordFieldModel $field
     * @param null                            $options
     *
     * @return string
     */
    public function getSingleHTML($options = null)
    {
        $values = $this->field->getValue();

        if (!$values || !count($values)) {
            return "";
        }

        $tpl = $this->buildTemplate($this->field, $values, $options);

        return $tpl->get();
    }


    /**
     * @param $record_field
     * @param $values
     * @param $options
     *
     * @return ilTemplate
     */
    protected function buildTemplate(ilDclNReferenceRecordFieldModel $record_field, $values, $options)
    {
        $tpl = new ilTemplate("tpl.reference_list.html", true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");
        foreach ($values as $value) {
            $ref_record = ilDclCache::getRecordCache($value);
            if (!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
                //the referenced record_field does not seem to exist.
                $record_field->setValue(0);
                $record_field->doUpdate();
            } else {
                $tpl->setCurrentBlock("reference");
                if (!$options) {
                    $tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef()));
                } else {
                    $tpl->setVariable("CONTENT", $record_field->getLinkHTML($options['link']['name'], $value));
                }
                $tpl->parseCurrentBlock();
            }
        }
        $tpl->parseCurrentBlock();

        return $tpl;
    }


    /**
     * @return array|mixed|string
     */
    public function getHTML()
    {
        $values = $this->field->getValue();
        $record_field = $this->field;

        if (!$values or !count($values)) {
            return "";
        }

        $html = "";
        $cut = false;
        $tpl = new ilTemplate("tpl.reference_hover.html", true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");
        $elements = array();
        foreach ($values as $value) {
            $ref_record = ilDclCache::getRecordCache($value);
            if (!$ref_record->getTableId() or !$record_field->getField() or !$record_field->getField()->getTableId()) {
                //the referenced record_field does not seem to exist.
                $record_field->setValue(null);
                $record_field->doUpdate();
            } else {
                $elements[] = array('value' => $ref_record->getRecordFieldHTML($this->field->getField()->getFieldRef()),
                                    'sort' => $ref_record->getRecordFieldSortingValue($this->field->getField()->getFieldRef()));
            }
        }
        //sort fetched elements
        $is_numeric = false;
        $ref_field = new ilDclBaseFieldModel($this->field->getField()->getFieldRef());
        switch ($ref_field->getDatatypeId()) {
            case ilDclDatatype::INPUTFORMAT_DATETIME:
            case ilDclDatatype::INPUTFORMAT_NUMBER:
                $is_numeric = true;
                break;
        }
        $elements = ilUtil::sortArray($elements, 'sort', 'asc', $is_numeric);

        //concat
        foreach ($elements as $element) {
            if ((strlen($html) < $record_field->getMaxReferenceLength())) {
                $html .= $element['value'] . ", ";
            } else {
                $cut = true;
            }
            $tpl->setCurrentBlock("reference");
            $tpl->setVariable("CONTENT", $element['value']);
            $tpl->parseCurrentBlock();
        }

        $html = substr($html, 0, -2);
        if ($cut) {
            $html .= "...";
        }
        $tpl->setVariable("RECORD_ID", $record_field->getRecord()->getId());
        $tpl->setVariable("ALL", $html);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
