<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ActiveRecordEditGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 *
 */
class arEditGUI extends ilPropertyFormGUI
{

    /**
     * @var  ActiveRecord
     */
    protected $record;
    /**
     * @var ilPropertyFormGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;

    /**
     * @var string
     */
    protected $lng_prefix = "";

    /**
     * @var string
     */
    protected $form_name = "";

    /**
     * @var array
     */
    protected $fields_to_hide = array();


    /**
     * @param $parent_gui
     * @param ActiveRecord $record
     * @param ilPlugin $plugin_object
     */
    public function __construct($parent_gui, ActiveRecord $record, ilPlugin $plugin_object = null)
    {
        if ($plugin_object)
        {
            $this->setLngPrefix($plugin_object->getPrefix());
            $plugin_object->loadLanguageModule();
        }

        global $ilCtrl;
        $this->record     = $record;
        $this->parent_gui = $parent_gui;
        $this->ctrl       = $ilCtrl;
        $this->ctrl->saveParameter($parent_gui, 'message_id');
        $this->initFieldsToHide();
        $this->initForm();
        if ($this->record->getId() != 0)
        {
            $this->fillForm();
        }
    }

    /**
     * @param array $fields_to_hide
     */
    public function setFieldsToHide($fields_to_hide)
    {
        $this->fields_to_hide = $fields_to_hide;
    }

    /**
     * @return array
     */
    public function getFieldsToHide()
    {
        return $this->fields_to_hide;
    }

    protected function initFieldsToHide()
    {
    }

    protected function initForm()
    {
        $this->setInitFormAction();
        $this->setFormName();
        $this->generateFields();
        $this->addCommandButtons();
    }

    protected function setInitFormAction()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
    }


    protected function generateFields()
    {
        foreach($this->record->returnDbFields() as $field_id => $field)
        {
            if(!in_array($field_id,$this->getFieldsToHide()))
            {
                $this->addField($field_id,$field);
            }
        }
    }

    protected function addField($field_id, $field)
    {
        $field_element = null;
        switch ($field->db_type)
        {
            case 'integer':
            case 'float':
                $field_element = $this->addNumberInputField($field_id);
                break;
            case 'text':
                $field_element = $this->addTextInputField($field_id);
                break;
            case 'date':
            case 'time':
            case 'timestamp':
                $field_element = $this->addDateTimeInputField($field_id);
                break;
            case 'clob':
                $field_element = $this->addClobInputField($field_id);
                break;
        }
        if ($field->notnull)
        {
            $field_element->setRequired(true);
        }
        $this->adaptAnyInput($field_element, $field_id);
        if($field_element)
        {
            $this->addItem($field_element);
        }
    }

    protected function addTextInputField($field_id)
    {
        return new ilTextInputGUI($this->txt($field_id), $field_id);;
    }

    protected function addNumberInputField($field_id)
    {
        return new ilNumberInputGUI($this->txt($field_id), $field_id);
    }


    protected function addDateTimeInputField($field_id)
    {
        $date_input = new ilDateTimeInputGUI($this->txt($field_id), $field_id);
        $date_input->setDate(new ilDate(date('Y-m-d H:i:s'), IL_CAL_DATE));
        $date_input->setShowTime(true);
        return $date_input;
    }

    protected function addClobInputField($field_id)
    {
        return new ilTextAreaInputGUI($this->txt($field_id), $field_id);
    }

    protected function adaptAnyInput(&$any_input, $field_id)
    {
    }

    protected function setFormName()
    {
        if ($this->record->getId() == 0)
        {
            $this->setTitle($this->txt('create_'.$this->form_name));
        } else
        {
            $this->setTitle($this->txt('edit_' . $this->form_name));
        }
    }


    public function fillForm()
    {
        $fields = array();

        foreach ($this->record->returnDbFields() as $field_id => $field)
        {
            if (!in_array($field_id, $this->getFieldsToHide()))
            {
                $get_function = "get" . $this->record->_toCamelCase($field_id, true);
                switch ($field->db_type)
                {
                    case 'integer':
                    case 'float':
                    case 'text':
                    case 'clob':
                        $fields[$field_id] = $this->record->$get_function();
                        break;
                    case 'date':
                    case 'time':
                    case 'timestamp':
                        $date = date('Y-m-d', $this->record->$get_function());
                        $time = date('H:i:s', $this->record->$get_function());
                        $fields[$field_id] = array("date" =>$date,"time"=>$time);
                        break;

                }
            }
        }

        $this->setValuesByArray($fields);
    }


    /**
     * returns whether checkinput was successful or not.
     *
     * @return bool
     */
    public function setRecordFields()
    {
        if (!$this->checkInput())
        {
            return false;
        }

        foreach ($this->record->returnDbFields() as $field_id => $field)
        {
            $valid = false;

            if($field_id == 'id')
            {
                $valid = true;
            } elseif($field_id == 'created' && $this->record->getId()==0)
            {
                $this->record->setCreated(time());
                $valid = true;
            } elseif ($field_id == 'modified')
            {
                $this->record->setModified(time());
                $valid = true;
            } elseif(array_key_exists($field_id, $_POST))
            {
                $value = $_POST[$field_id];

                $set_function = "set" . $this->record->_toCamelCase($field_id, true);

                switch ($field->db_type)
                {
                    case 'integer':
                    case 'float':
                        $valid = $this->setNumberRecordField($field_id, $set_function, $value);
                        break;
                    case 'text':
                        $valid = $this->setTextRecordField($field_id, $set_function, $value);
                        break;
                    case 'date':
                    case 'time':
                    case 'timestamp':
                        $valid = $this->setDateTimeRecordField($field_id, $set_function, $value);
                        break;
                    case 'clob':
                        $valid = $this->setClobRecordField($field_id, $set_function, $value);
                        break;
                }
            }
            else
            {
                $valid = $this->handleEmptyPostValue($field_id);;
            }


            if(!$valid)
            {
                return false;
            }
        }
        return true;
    }

    protected function setNumberRecordField($field_id, $set_function, $value)
    {
        $this->record->$set_function($value);
        return true;
    }

    protected function setTextRecordField($field_id, $set_function, $value)
    {
        $this->record->$set_function($value);
        return true;
    }

    protected function setDateTimeRecordField($field_id, $set_function, $value)
    {

        if($value['time'])
        {
            $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $value['date']." ". $value['time'])->getTimestamp();
        }
        else
        {
            $timestamp = DateTime::createFromFormat("Y-m-d", $value['date'])->getTimestamp();
        }

        $this->record->$set_function($timestamp);
        return true;
    }

    protected function setClobRecordField($field_id, $set_function, $value)
    {
        $this->record->$set_function($value);
        return true;
    }


    protected function handleEmptyPostValue($field_id)
    {
        return true;
    }


    /**
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->setRecordFields())
        {
            return false;
        }
        if ($this->record->getId())
        {
            $this->record->update();
        } else
        {
            $this->record->create();
        }

        return true;
    }


    protected function addCommandButtons()
    {
        if ($this->record->getId() == 0)
        {
            $this->addCommandButton('create', $this->txt('create_message'));
        } else
        {
            $this->addCommandButton('update', $this->txt('save'));
        }
        $this->addCommandButton('index', $this->txt('cancel'));
    }

    /**
     * @param string $lng_prefix
     */
    public function setLngPrefix($lng_prefix)
    {
        $this->lng_prefix = $lng_prefix;
    }

    /**
     * @return string
     */
    public function getLngPrefix()
    {
        return $this->lng_prefix;
    }


    protected function txt($txt)
    {
        global $lng;

        if ($this->getLngPrefix() != "")
        {
            return $lng->txt($this->getLngPrefix() . "_" . $txt, $this->getLngPrefix());
        } else
        {
            return $lng->txt($txt);
        }

    }
}