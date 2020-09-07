<?php

/**
 * Class ilDclFileuploadRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFileuploadRecordRepresentation extends ilDclBaseRecordRepresentation
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
        } else {
            if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
                return "";
            }
        }

        $file_obj = new ilObjFile($value, false);
        $this->ctrl->setParameterByClass("ildclrecordlistgui", "record_id", $this->getRecordField()->getRecord()->getId());
        $this->ctrl->setParameterByClass("ildclrecordlistgui", "field_id", $this->getRecordField()->getField()->getId());

        $html = '<a href="' . $this->ctrl->getLinkTargetByClass("ildclrecordlistgui", "sendFile") . '">' . $file_obj->getFileName() . '</a>';
        if (ilPreview::hasPreview($file_obj->getId())) {
            ilPreview::createPreview($file_obj); // Create preview if not already existing
            $preview = new ilPreviewGUI((int) $_GET['ref_id'], ilPreviewGUI::CONTEXT_REPOSITORY, $file_obj->getId(), $this->access);
            $preview_status = ilPreview::lookupRenderStatus($file_obj->getId());
            $preview_status_class = "";
            $preview_text_topic = "preview_show";
            if ($preview_status == ilPreview::RENDER_STATUS_NONE) {
                $preview_status_class = "ilPreviewStatusNone";
                $preview_text_topic = "preview_none";
            }
            $wrapper_html_id = 'record_field_' . $this->getRecordField()->getId();
            $script_preview_click = $preview->getJSCall($wrapper_html_id);
            $preview_title = $this->lng->txt($preview_text_topic);
            $preview_icon = ilUtil::getImagePath("preview.png", "Services/Preview");
            $html = '<div id="' . $wrapper_html_id . '">' . $html;
            $html .= '<span class="il_ContainerItemPreview ' . $preview_status_class . '"><a href="javascript:void(0);" onclick="'
                . $script_preview_click . '" title="' . $preview_title . '"><img src="' . $preview_icon
                . '" height="16" width="16"></a></span></div>';
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

        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
            return "";
        }

        $file_obj = new ilObjFile($value, false);

        //$input = ilObjFile::_lookupAbsolutePath($value);
        return $file_obj->getFileName();
    }
}
