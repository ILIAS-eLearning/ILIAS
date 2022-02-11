<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
abstract class ilAbstractHtmlToPdfTransformer implements ilHtmlToPdfTransformer
{
    public function getPdfTempName() : string
    {
        return $this->getTempFileName('pdf');
    }

    public function getHtmlTempName() : string
    {
        return $this->getTempFileName('html');
    }

    protected function getTempFileName(string $file_type) : string
    {
        return ilFileUtils::ilTempnam() . '.' . $file_type;
    }
}
