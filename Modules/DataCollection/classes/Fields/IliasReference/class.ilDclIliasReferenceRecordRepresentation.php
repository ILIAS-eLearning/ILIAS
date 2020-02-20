<?php

/**
 * Class ilDclIliasRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclIliasReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{

    /**
     * @param bool $link
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        $title = $this->getRecordField()->getValueForRepresentation();
        if (!$title) {
            return '';
        }
        $field = $this->getRecordField()->getField();

        if ($field->getProperty(ilDclBaseFieldModel::PROP_DISPLAY_COPY_LINK_ACTION_MENU)) {
            $html = $this->getLinkHTML($title, true);
        } else {
            if ($field->getProperty(ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK)) {
                $html = $this->getLinkHTML($title);
            } else {
                $html = $title;
            }
        }

        return $html;
    }


    public function getSingleHTML(array $options = null, $link = true)
    {
        $value = $this->getRecordField()->getValue();
        if (!$value) {
            return '';
        }
        $id = ilObject::_lookupObjId($value);
        $value = ilObject::_lookupTitle($id);
        if ($this->getRecordField()->getField()->getProperty(ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK)) {
            return $this->getLinkHTML($value);
        }

        return $value;
    }


    /**
     * @param $title
     * @param $show_action_menu
     *
     * @return string
     */
    public function getLinkHTML($title, $show_action_menu = false)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->getRecordField();
        $link = ilLink::_getStaticLink($this->getRecordField()->getValue());
        if ($show_action_menu) {
            $field = $this->getRecordField()->getField();
            $record = $this->getRecordField()->getRecord();

            $list = new ilAdvancedSelectionListGUI();
            $list->setId('adv_list_copy_link_' . $field->getId() . $record->getId());
            $list->setListTitle($title);
            if ($field->getProperty(ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK)) {
                $list->addItem($lng->txt('view'), 'view', $link);
            }
            $list->addItem($lng->txt('copy'), 'copy', $this->getActionLink('copy'));
            $list->addItem($lng->txt('link'), 'link', $this->getActionLink('link'));

            return $list->getHTML();
        } else {
            return "<a href=\"$link\">$title</a>";
        }
    }


    /**
     * @param string $mode copy|link
     *
     * @return string
     */
    protected function getActionLink($mode)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        switch ($mode) {
            case 'copy':
                $ilCtrl->setParameterByClass('ilobjectcopygui', 'item_ref_id', $this->getRecordField()->getValue());
                $ilCtrl->setParameterByClass('ilobjrootfoldergui', 'item_ref_id', $this->getRecordField()->getValue());
                $ilCtrl->setParameterByClass('ilobjectcopygui', 'source_id', $this->getRecordField()->getValue());

                return $ilCtrl->getLinkTargetByClass('ilobjectcopygui', 'initTargetSelection');
            case 'link':
                return $ilCtrl->getLinkTargetByClass(array('ilrepositorygui', 'ilobjrootfoldergui'), 'link');
            default:
                return '';
        }
    }
}
