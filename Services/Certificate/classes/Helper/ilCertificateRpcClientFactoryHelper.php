<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateRpcClientFactoryHelper
{
    public function ilFO2PDF(string $package, string $certificateContent) : ScalarPdf
    {
        $factory = ilRpcClientFactory::factory($package);
        $ilFO2PDFResult = $factory->ilFO2PDF($certificateContent);
        require_once "Services/Certificate/test/ilCertificateBaseTestCase.php";
        require_once "Services/Certificate/test/ilPdfGeneratorTest.php";

        $scalarPdf = new ScalarPdf();
        $scalarPdf->scalar = $ilFO2PDFResult->scalar;

        return $scalarPdf;
    }
}
