<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateRpcClientFactoryHelper
{
    /**
     * @param string $package
     * @param string $certificateContent
     * @return stdClass
     */
    public function ilFO2PDF(string $package, string $certificateContent) : stdClass
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilRpcClientFactory::factory($package)->ilFO2PDF($certificateContent);
    }
}
