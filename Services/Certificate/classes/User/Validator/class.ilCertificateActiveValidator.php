<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveValidator
{
    /**
     * @var ilSetting|null
     */
    private $setting;

    /**
     * @var ilRPCServerSettings|null|object
     */
    private $rpcSettings;

    /**
     * @param ilSetting|null $setting
     * @param ilRPCServerSettings|null $rpcSettings
     */
    public function __construct(ilSetting $setting = null, ilRPCServerSettings $rpcSettings = null)
    {
        if (null === $setting) {
            $setting = new ilSetting("certificate");
        }
        $this->setting = $setting;

        if (null == $rpcSettings) {
            $rpcSettings = ilRPCServerSettings::getInstance();
        }
        $this->rpcSettings = $rpcSettings;
    }

    public function validate()
    {
        $globalCertificateActive = (bool) $this->setting->get('active');

        if (false === $globalCertificateActive) {
            return false;
        }

        $serverActive = (bool) $this->rpcSettings->isEnabled();

        if (false === $serverActive) {
            return false;
        }

        return true;
    }
}
