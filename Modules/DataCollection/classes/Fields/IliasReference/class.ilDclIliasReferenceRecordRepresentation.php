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
 * Class ilDclIliasRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclIliasReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{

    public function getHTML(bool $link = true) : string
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

    public function getSingleHTML(array $options = null, bool $link = true) : string
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

    public function getLinkHTML(string $title, bool $show_action_menu = false) : string
    {
        $lng = $this->lng;
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
     */
    protected function getActionLink(string $mode) : string
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
