<?php

/**
 * Class ilDclTextFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextFieldModel extends ilDclBaseFieldModel
{

    /**
     * @inheritdoc
     */
    public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE "
            . $ilDB->quote("%$filter_value%", 'text') . ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }


    /**
     * @inheritdoc
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        // use custom record sorting for url-fields
        if ($this->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            return new ilDclTextRecordQueryObject();
        } else {
            return parent::getRecordQuerySortObject($direction, $sort_by_status);
        }
    }


    /**
     * @param ilPropertyFormGUI $form
     * @param null              $record_id
     */
    public function checkValidityFromForm(ilPropertyFormGUI &$form, $record_id = null)
    {
        $has_url_property = $this->getProperty(ilDclBaseFieldModel::PROP_URL);
        if ($has_url_property) {
            $values = array(
                'link'  => $form->getInput("field_" . $this->getId()),
                'title' => $form->getInput("field_" . $this->getId() . "_title"),
            );
            $this->checkValidityOfURLField($values, $record_id);
        } else {
            parent::checkValidityFromForm($form, $record_id);
        }
    }


    /**
     * @inheritdoc
     */
    public function checkValidity($value, $record_id = null)
    {
        $has_url_property = $this->getProperty(ilDclBaseFieldModel::PROP_URL);
        if ($has_url_property) {
            return $this->checkValidityOfURLField($value, $record_id);
        }

        //Don't check empty values
        if ($value == null) {
            return true;
        }

        $this->checkRegexAndLength($value);

        if ($this->isUnique()) {
            $table = ilDclCache::getTableCache($this->getTableId());
            foreach ($table->getRecords() as $record) {
                //for text it has to be case insensitive.
                $record_value = $record->getRecordFieldValue($this->getId());

                if (strtolower($this->normalizeValue($record_value)) == strtolower($this->normalizeValue(nl2br($value)))
                    && ($record->getId() != $record_id
                        || $record_id == 0)
                ) {
                    throw new ilDclInputException(ilDclInputException::UNIQUE_EXCEPTION);
                }
            }
        }
    }


    /**
     * @param $value
     * @param $record_id
     *
     * @return bool
     * @throws ilDclInputException
     */
    protected function checkValidityOfURLField($value, $record_id)
    {
        // TODO: value should always be an array with url fields, can we remove the check & json_decode?
        if (!is_array($value)) {
            $value = array('link' => $value, 'title' => '');
        }

        //Don't check empty values
        if (!$value['link']) {
            return true;
        }

        $this->checkRegexAndLength($value['link']);

        //check url/email
        $link = (substr($value['link'], 0, 3) === 'www') ? 'http://' . $value['link'] : $value['link'];
        if (!filter_var($link, FILTER_VALIDATE_URL) && !filter_var($link, FILTER_VALIDATE_EMAIL) && $link != '') {
            throw new ilDclInputException(ilDclInputException::NOT_URL);
        }

        if ($this->isUnique()) {
            $table = ilDclCache::getTableCache($this->getTableId());
            foreach ($table->getRecords() as $record) {
                $record_value = $record->getRecordFieldValue($this->getId());

                if ($record_value == $value
                    && ($record->getId() != $record_id
                        || $record_id == 0)
                ) {
                    throw new ilDclInputException(ilDclInputException::UNIQUE_EXCEPTION);
                }
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function checkFieldCreationInput(ilPropertyFormGUI $form)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $return = true;
        // Additional check for text fields: The length property should be max 200 if the textarea option is not set
        if ((int) $form->getInput('prop_' . ilDclBaseFieldModel::PROP_LENGTH) > 200 && !$form->getInput('prop_' . ilDclBaseFieldModel::PROP_TEXTAREA)) {
            $inputObj = $form->getItemByPostVar('prop_' . ilDclBaseFieldModel::PROP_LENGTH);
            $inputObj->setAlert($lng->txt("form_msg_value_too_high"));
            $return = false;
        }

        return $return;
    }


    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array(ilDclBaseFieldModel::PROP_LENGTH, ilDclBaseFieldModel::PROP_REGEX, ilDclBaseFieldModel::PROP_URL, ilDclBaseFieldModel::PROP_TEXTAREA, ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
    }


    /**
     * @param $value
     *
     * @throws ilDclInputException
     */
    protected function checkRegexAndLength($value)
    {
        $regex = $this->getProperty(ilDclBaseFieldModel::PROP_REGEX);
        if (substr($regex, 0, 1) != "/") {
            $regex = "/" . $regex;
        }
        if (substr($regex, -1) != "/") {
            $regex .= "/";
        }

        if ($this->getProperty(ilDclBaseFieldModel::PROP_LENGTH) < $this->strlen($value, 'UTF-8')
            && is_numeric($this->getProperty(ilDclBaseFieldModel::PROP_LENGTH))
        ) {
            throw new ilDclInputException(ilDclInputException::LENGTH_EXCEPTION);
        }

        if ($this->getProperty(ilDclBaseFieldModel::PROP_REGEX) != null) {
            try {
                $preg_match = preg_match($regex, $value);
            } catch (ErrorException $e) {
                throw new ilDclInputException(ilDclInputException::REGEX_CONFIG_EXCEPTION);
            }

            if ($preg_match == false) {
                throw new ilDclInputException(ilDclInputException::REGEX_EXCEPTION);
            }
        }
    }


    /**
     * @param        $value
     * @param string $encoding
     *
     * @return int
     */
    public function strlen($value, $encoding = 'UTF-8')
    {
        switch (true) {
            case function_exists('mb_strlen'):
                return mb_strlen($value, $encoding);
            case function_exists('iconv_strlen'):
                return iconv_strlen($value, $encoding);
            default:
                return strlen($value);
        }
    }


    public function fillHeaderExcel(ilExcel $worksheet, &$row, &$col)
    {
        parent::fillHeaderExcel($worksheet, $row, $col);
        if ($this->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            $worksheet->setCell($row, $col, $this->getTitle() . '_title');
            $col++;
        }
    }


    /**
     * @param array $titles
     * @param array $import_fields
     */
    public function checkTitlesForImport(array &$titles, array &$import_fields)
    {
        foreach ($titles as $k => $title) {
            if (!ilStr::isUtf8($title)) {
                $title = utf8_encode($title);
            }
            if ($title == $this->getTitle()) {
                $import_fields[$k] = $this;
                if ($this->hasProperty(ilDclBaseFieldModel::PROP_URL) && $titles[$k + 1] == $this->getTitle() . '_title') {
                    unset($titles[$k + 1]);
                }
            }
        }
    }
}
