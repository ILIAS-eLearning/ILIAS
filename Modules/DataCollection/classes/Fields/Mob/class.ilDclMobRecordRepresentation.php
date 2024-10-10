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

class ilDclMobRecordRepresentation extends ilDclFileRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->getRecordField()->getValue();

        if (is_null($value)) {
            return "";
        }

        // the file is only temporary uploaded. Still need to be confirmed before stored
        $has_ilfilehash = $this->http->wrapper()->post()->has('ilfilehash');
        if (is_array($value) && $has_ilfilehash) {
            $ilfilehash = $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string());

            $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, "ilfilehash", $ilfilehash);
            $this->ctrl->setParameterByClass(
                ilDclRecordListGUI::class,
                "field_id",
                $this->getRecordField()->getField()->getId()
            );

            return '<a href="' . $this->ctrl->getLinkTargetByClass(
                ilDclRecordListGUI::class,
                "sendFile"
            ) . '">' . $value['name'] . '</a>';
        }

        $mob = new ilObjMediaObject($value);
        $med = $mob->getMediaItem('Standard');

        if (!$med || $med->getLocation() === "") {
            return "";
        }

        $field = $this->getRecordField()->getField();

        $is_linked_field = $field->getProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_MOB);
        $has_view = false;
        if ($this->http->wrapper()->query()->has("tableview_id")) {
            $tableview_id = $this->http->wrapper()->query()->retrieve(
                'tableview_id',
                $this->refinery->kindlyTo()->int()
            );
            $has_view = ilDclDetailedViewDefinition::isActive($tableview_id);
        }

        $components = [];

        if (in_array($med->getSuffix(), ['jpg', 'jpeg', 'png', 'gif'])) {
            // Image
            $dir = ilObjMediaObject::_getDirectory($mob->getId());

            $image = $this->factory->image()->responsive(ilWACSignedPath::signFile($dir . "/" . $med->getLocation()), "");

            if ($is_linked_field && $has_view && $link) {
                $this->ctrl->setParameterByClass(
                    'ilDclDetailedViewGUI',
                    'record_id',
                    $this->getRecordField()->getRecord()->getId()
                );
                $image = $image->withAction($this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord'));
            }
            $components[] = $image;
        } else {
            $location = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
            if ($med->getSuffix() == 'mp3') {
                $components[] = $this->factory->player()->audio($location);
            } else {
                $components[] = $this->factory->player()->video($location);
            }

            if ($is_linked_field && $has_view) {
                $this->ctrl->setParameterByClass(
                    'ilDclDetailedViewGUI',
                    'record_id',
                    $this->getRecordField()->getRecord()->getId()
                );
                $components[] = $this->factory->link()->standard(
                    $this->lng->txt('details'),
                    $this->ctrl->getLinkTargetByClass(
                        "ilDclDetailedViewGUI",
                        'renderRecord'
                    )
                );
            }
        }

        $width = "200px";
        $height = "auto";
        if ($field->getProperty(ilDclBaseFieldModel::PROP_WIDTH) > 0) {
            $width = $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH) . "px";
        }
        if ($field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT) > 0) {
            $height = $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT) . "px";
        }
        $content = $this->renderer->render($components);
        return "<div style='width:$width; height:$height;'>$content</div>";
    }

    public function parseFormInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || !ilObject2::_exists((int) $value) || ilObject2::_lookupType((int) $value) != 'mob') {
            return '';
        }

        return $value;
    }
}
