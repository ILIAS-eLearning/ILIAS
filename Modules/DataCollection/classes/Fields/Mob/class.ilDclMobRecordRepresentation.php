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
 * Class ilDclMobRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclMobRecordRepresentation extends ilDclFileuploadRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true): string
    {
        $value = $this->getRecordField()->getValue();

        // the file is only temporary uploaded. Still need to be confirmed before stored
        $has_ilfilehash = $this->http->wrapper()->post()->has('ilfilehash');
        if (is_array($value) && $has_ilfilehash) {
            $ilfilehash = $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string());

            $this->ctrl->setParameterByClass("ildclrecordlistgui", "ilfilehash", $ilfilehash);
            $this->ctrl->setParameterByClass(
                "ildclrecordlistgui",
                "field_id",
                $this->getRecordField()->getField()->getId()
            );

            return '<a href="' . $this->ctrl->getLinkTargetByClass(
                "ildclrecordlistgui",
                "sendFile"
            ) . '">' . $value['name'] . '</a>';
        }

        $mob = new ilObjMediaObject($value);
        $med = $mob->getMediaItem('Standard');

        if (!$med || $med->getLocation() == null) {
            return "";
        }

        $field = $this->getRecordField()->getField();

        $is_linked_field = $field->getProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
        $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());
        $has_view = ilDclDetailedViewDefinition::isActive($tableview_id);

        if (in_array($med->getSuffix(), array('jpg', 'jpeg', 'png', 'gif'))) {
            // Image
            $dir = ilObjMediaObject::_getDirectory($mob->getId());
            $width = (int) $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH);
            $height = (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT);

            $html = ilUtil::img(ilWACSignedPath::signFile($dir . "/" . $med->getLocation()), '', $width, $height);

            if ($is_linked_field && $has_view && $link) {
                $this->ctrl->setParameterByClass(
                    'ilDclDetailedViewGUI',
                    'record_id',
                    $this->getRecordField()->getRecord()->getId()
                );
                $html = '<a href="' . $this->ctrl->getLinkTargetByClass(
                    "ilDclDetailedViewGUI",
                    'renderRecord'
                ) . '">' . $html . '</a>';
            }
        } else {
            // Video/Audio
            $mpl = new ilMediaPlayerGUI($med->getId(), '');
            $mpl->setFile(ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation());
            $mpl->setMimeType($med->getFormat());
            $mpl->setDisplayWidth((int) $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH) . 'px');
            $mpl->setDisplayHeight((int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT) . 'px');
            $mpl->setVideoPreviewPic($mob->getVideoPreviewPic());
            $html = $mpl->getPreviewHtml();

            if ($is_linked_field && $has_view) {
                $this->ctrl->setParameterByClass(
                    'ilDclDetailedViewGUI',
                    'record_id',
                    $this->getRecordField()->getRecord()->getId()
                );
                $html = $html . '<a href="' . $this->ctrl->getLinkTargetByClass(
                    "ilDclDetailedViewGUI",
                    'renderRecord'
                ) . '">' . $this->lng->txt('details') . '</a>';
            }
        }

        return $html;
    }

    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param array|int $value
     * @return array|int|string
     */
    public function parseFormInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value) != "mob") {
            return "";
        }

        return $value;
    }
}
