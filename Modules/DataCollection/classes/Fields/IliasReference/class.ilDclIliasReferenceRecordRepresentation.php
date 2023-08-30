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

class ilDclIliasReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public function getHTML(bool $link = true, array $options = []): string
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

    public function getSingleHTML(array $options = null, bool $link = true): string
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

    public function getLinkHTML(string $title, bool $show_action_menu = false): string
    {
        $link = ilLink::_getStaticLink($this->getRecordField()->getValue());
        if ($show_action_menu) {
            $field = $this->getRecordField()->getField();
            $dropdown_items = [];
            if ($field->getProperty(ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK)) {
                $dropdown_items[] = $this->factory->link()->standard(
                    $this->lng->txt('view'),
                    $link
                );
            }
            $dropdown_items[] = $this->factory->link()->standard(
                $this->lng->txt('link'),
                $this->getActionLink('link')
            );
            $dropdown_items[] = $this->factory->link()->standard(
                $this->lng->txt('copy'),
                $this->getActionLink('copy')
            );
            $dropdown = $this->factory->dropdown()->standard($dropdown_items)->withLabel($title);
            return $this->renderer->render($dropdown);
        } else {
            return $this->renderer->render($this->factory->link()->standard($title, $link));
        }
    }

    /**
     * @param string $mode copy|link
     */
    protected function getActionLink(string $mode): string
    {
        switch ($mode) {
            case 'copy':
                $this->ctrl->setParameterByClass(ilObjectCopyGUI::class, 'item_ref_id', $this->getRecordField()->getValue());
                $this->ctrl->setParameterByClass(ilObjRootFolderGUI::class, 'item_ref_id', $this->getRecordField()->getValue());
                $this->ctrl->setParameterByClass(ilObjectCopyGUI::class, 'source_id', $this->getRecordField()->getValue());

                return $this->ctrl->getLinkTargetByClass(ilObjectCopyGUI::class, 'initTargetSelection');
            case 'link':
                return $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjRootFolderGUI::class], 'link');
            default:
                return '';
        }
    }
}
