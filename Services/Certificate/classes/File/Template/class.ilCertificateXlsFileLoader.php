<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateXlsFileLoader
{
    /**
     * @return bool|string
     */
    public function getXlsCertificateContent()
    {
        $xsl = file_get_contents("./Services/Certificate/xml/xhtml2fo.xsl");

        return $xsl;
    }
}
