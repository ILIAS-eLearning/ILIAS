<?php

/**
 * Class ilDclTextFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextRecordRepresentation extends ilDclBaseRecordRepresentation
{
    const LINK_MAX_LENGTH = 40;


    /**
     * Outputs html of a certain field
     *
     * @param mixed     $value
     * @param bool|true $link
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        $value = $this->getRecordField()->getValue();

        //Property URL
        $field = $this->getField();
        if ($field->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            if (is_array($value)) {
                $link = $value['link'];
                $link_value = $value['title'] ? $value['title'] : $this->shortenLink($link);
            } else {
                $link = $value;
                $link_value = $this->shortenLink($value);
            }

            if (substr($link, 0, 3) === 'www') {
                $link = 'http://' . $link;
            }

            if (preg_match("/^[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $link)) {
                $link = "mailto:" . $link;
            } elseif (!(preg_match('~(^(news|(ht|f)tp(s?)\://){1}\S+)~i', $link))) {
                return $link;
            }

            $html = "<a rel='noopener' target='_blank' href='" . htmlspecialchars($link, ENT_QUOTES) . "'>" . htmlspecialchars($link_value, ENT_QUOTES) . "</a>";
        } elseif ($field->hasProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT) && $link && ilDclDetailedViewDefinition::isActive($_GET['tableview_id'])) {
            $this->ctrl->clearParametersByClass("ilDclDetailedViewGUI");
            $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'record_id', $this->getRecordField()->getRecord()->getId());
            $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'tableview_id', $_GET['tableview_id']);
            $html = '<a href="' . $this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord') . '">' . $value . '</a>';
        } else {
            $html = (is_array($value) && isset($value['link'])) ? $value['link'] : $value;
        }

        return $html;
    }


    /**
     * This method shortens a link. The http(s):// and the www part are taken away. The rest will be shortened to sth similar to:
     * "somelink.de/lange...gugus.html".
     *
     * @param $value The link in it's original form.
     *
     * @return string The shortened link
     */
    protected function shortenLink($value)
    {
        if (strlen($value) > self::LINK_MAX_LENGTH) {
            if (substr($value, 0, 7) == "http://") {
                $value = substr($value, 7);
            }
            if (substr($value, 0, 8) == "https://") {
                $value = substr($value, 8);
            }
            if (substr($value, 0, 4) == "www.") {
                $value = substr($value, 4);
            }
        }
        $link = $value;

        if (strlen($value) > self::LINK_MAX_LENGTH) {
            $link = substr($value, 0, (self::LINK_MAX_LENGTH - 3) / 2);
            $link .= "...";
            $link .= substr($value, -(self::LINK_MAX_LENGTH - 3) / 2);
        }

        return $link;
    }


    /**
     * @inheritDoc
     */
    public function fillFormInput($form)
    {
        $input_field = $form->getItemByPostVar('field_' . $this->getField()->getId());
        $raw_input = $this->getFormInput();

        $value = is_array($raw_input) ? $raw_input['link'] : $raw_input;
        $field_values = array();
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            $field_values["field_" . $this->getRecordField()->getField()->getId() . "_title"] = (isset($raw_input['title'])) ? $raw_input['title'] : '';
        }

        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_TEXTAREA)) {
            $breaks = array("<br />");
            $value = str_ireplace($breaks, "", $value);
        }

        $field_values["field_" . $this->getRecordField()->getField()->getId()] = $value;
        $input_field->setValueByArray($field_values);
    }
}
