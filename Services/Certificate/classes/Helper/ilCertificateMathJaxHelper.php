<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMathJaxHelper
{
    public function fillXlsFoContent(string $xslfo) : string
    {
        return ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
            ->insertLatexImages($xslfo);
    }
}
