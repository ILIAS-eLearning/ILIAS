<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailMimeTransportFactory
{
    /** @var \ilSetting */
    protected $settings;

    /** @var ilAppEventHandler */
    private $eventHandler;

    /**
     * ilMailMimeTransportFactory constructor.
     * @param ilSetting         $settings
     * @param ilAppEventHandler $eventHandler
     */
    public function __construct(\ilSetting $settings, \ilAppEventHandler $eventHandler)
    {
        $this->settings = $settings;
        $this->eventHandler = $eventHandler;
    }

    /**
     * @return ilMailMimeTransport
     */
    public function getTransport()
    {
        if (!(bool) $this->settings->get('mail_allow_external')) {
            return new ilMailMimeTransportNull();
        }

        if ((bool) $this->settings->get('mail_smtp_status')) {
            return new ilMailMimeTransportSmtp($this->settings, $this->eventHandler);
        } else {
            return new ilMailMimeTransportSendmail($this->settings, $this->eventHandler);
        }
    }
}
