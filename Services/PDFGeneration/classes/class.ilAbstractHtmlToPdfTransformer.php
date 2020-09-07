<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../interfaces/interface.ilHtmlToPdfTransformer.php';

/**
 * Class ilHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractHtmlToPdfTransformer implements ilHtmlToPdfTransformer
{
    /**
     * @return string
     */
    public function getPdfTempName()
    {
        return $this->getTempFileName('pdf');
    }

    /**
     * @return string
     */
    public function getHtmlTempName()
    {
        return $this->getTempFileName('html');
    }

    /**
     * @param $file_type
     * @return string
     */
    protected function getTempFileName($file_type)
    {
        return ilUtil::ilTempnam() . '.' . $file_type;
    }
}
