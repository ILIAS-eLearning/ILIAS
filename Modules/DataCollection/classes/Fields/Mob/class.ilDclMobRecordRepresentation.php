<?php

/**
 * Class ilDclMobRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclMobRecordRepresentation extends ilDclFileuploadRecordRepresentation
{

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

        // the file is only temporary uploaded. Still need to be confirmed before stored
        if (is_array($value) && $_POST['ilfilehash']) {
            $this->ctrl->setParameterByClass("ildclrecordlistgui", "ilfilehash", $_POST['ilfilehash']);
            $this->ctrl->setParameterByClass("ildclrecordlistgui", "field_id", $this->getRecordField()->getField()->getId());

            return '<a href="' . $this->ctrl->getLinkTargetByClass("ildclrecordlistgui", "sendFile") . '">' . $value['name'] . '</a>';
        }

        $mob = new ilObjMediaObject($value, false);
        $med = $mob->getMediaItem('Standard');

        if (!$med || $med->getLocation() == null) {
            return "";
        }

        $field = $this->getRecordField()->getField();

        $is_linked_field = $field->getProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
        $has_view = ilDclDetailedViewDefinition::isActive($_GET['tableview_id']);

        if (in_array($med->getSuffix(), array('jpg', 'jpeg', 'png', 'gif'))) {
            // Image
            $dir = ilObjMediaObject::_getDirectory($mob->getId());
            $width = (int) $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH);
            $height = (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT);

            $html = ilUtil::img(ilWACSignedPath::signFile($dir . "/" . $med->getLocation()), '', $width, $height);

            if ($is_linked_field && $has_view && $link) {
                $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'record_id', $this->getRecordField()->getRecord()->getId());
                $html = '<a href="' . $this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord') . '">' . $html . '</a>';
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
                $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'record_id', $this->getRecordField()->getRecord()->getId());
                $html = $html . '<a href="' . $this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord') . '">' . $this->lng->txt('details') . '</a>';
            }
        }

        return $html;
    }


    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     *
     * @param $value
     *
     * @return mixed
     */
    public function parseFormInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "mob") {
            return "";
        }

        return $value;
    }
}
