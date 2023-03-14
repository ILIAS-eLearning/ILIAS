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

/**
 * Class ilDclFileuploadRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFileuploadRecordRepresentation extends ilDclBaseRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true, array $options = []): string
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
        } else {
            if (!ilObject2::_exists((int)$value) || ilObject2::_lookupType($value, false) != "file") {
                return "";
            }
        }

        $file_obj = new ilObjFile($value, false);
        $this->ctrl->setParameterByClass(
            "ildclrecordlistgui",
            "record_id",
            $this->getRecordField()->getRecord()->getId()
        );
        $this->ctrl->setParameterByClass(
            "ildclrecordlistgui",
            "field_id",
            $this->getRecordField()->getField()->getId()
        );

        $html = '<a href="' . $this->ctrl->getLinkTargetByClass(
            "ildclrecordlistgui",
            "sendFile"
        ) . '">' . $file_obj->getFileName() . '</a>';

        $preview= new ilObjFilePreviewRendererGUI($file_obj->getId());
        if ($preview->has()) {
            $html = '<div id="' . $wrapper_html_id . '">' . $html;
            $html .= $preview->getRenderedTriggerComponents(false) . '</div>';
        }

        return $html;
    }

    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param array|string $value
     * @return array|string
     */
    public function parseFormInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (!ilObject2::_exists((int)$value) || ilObject2::_lookupType($value) != "file") {
            return "";
        }

        $file_obj = new ilObjFile($value, false);

        //$input = ilObjFile::_lookupAbsolutePath($value);
        return $file_obj->getFileName();
    }
}
