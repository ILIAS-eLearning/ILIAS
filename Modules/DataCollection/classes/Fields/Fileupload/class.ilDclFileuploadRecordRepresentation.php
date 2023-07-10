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
 * @deprecated
 */
class ilDclFileuploadRecordRepresentation extends ilDclBaseRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true, array $options = []): string
    {
        return $this->lng->txt('fileupload_not_migrated');
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
