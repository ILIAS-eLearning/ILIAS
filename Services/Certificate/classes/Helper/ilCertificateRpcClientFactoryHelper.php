<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateRpcClientFactoryHelper
{
    public function ilFO2PDF(string $package, string $certificateContent) : string
    {
        return ilRpcClientFactory::factory($package)->ilFO2PDF($certificateContent);
    }
}
