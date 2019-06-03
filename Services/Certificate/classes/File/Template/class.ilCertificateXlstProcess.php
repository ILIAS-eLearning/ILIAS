<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateXlstProcess
{
    /**
     * @param array $args
     * @param array $params
     * @return string
     */
    public function process(array $args, array $params) : string
    {
        $xh = xslt_create();

        $output = xslt_process(
            $xh,
            "arg:/_xml",
            "arg:/_xsl",
            null,
            $args,
            $params
        );

        xslt_error($xh);
        xslt_free($xh);

        return $output;
    }
}
