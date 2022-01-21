<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveValidator
{
    private ilSetting $setting;
    private ilRPCServerSettings $rpcSettings;

    public function __construct(?ilSetting $setting = null, ?ilRPCServerSettings $rpcSettings = null)
    {
        if (null === $setting) {
            $setting = new ilSetting("certificate");
        }
        $this->setting = $setting;

        if (null === $rpcSettings) {
            $rpcSettings = ilRPCServerSettings::getInstance();
        }
        $this->rpcSettings = $rpcSettings;
    }

    public function validate() : bool
    {
        $globalCertificateActive = (bool) $this->setting->get('active', '0');

        if (false === $globalCertificateActive) {
            return false;
        }

        $serverActive = $this->rpcSettings->isEnabled();

        if (false === $serverActive) {
            return false;
        }

        return true;
    }
}
