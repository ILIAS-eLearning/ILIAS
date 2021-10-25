<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailMimeTransportFactory
{
    protected ilSetting $settings;
    private ilAppEventHandler $eventHandler;

    public function __construct(ilSetting $settings, ilAppEventHandler $eventHandler)
    {
        $this->settings = $settings;
        $this->eventHandler = $eventHandler;
    }

    public function getTransport() : ilMailMimeTransport
    {
        if (!$this->settings->get('mail_allow_external', '0')) {
            return new ilMailMimeTransportNull();
        }

        if ($this->settings->get('mail_smtp_status', '0')) {
            return new ilMailMimeTransportSmtp($this->settings, $this->eventHandler);
        }

        return new ilMailMimeTransportSendmail($this->settings, $this->eventHandler);
    }
}
