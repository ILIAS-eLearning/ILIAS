<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddress
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddress
{
    /**
     * @var string
     */
    protected $mailbox = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * ilMailAddress constructor.
     * @param string $mailbox
     * @param string $host
     */
    public function __construct($mailbox, $host)
    {
        $this->mailbox = $mailbox;
        $this->host    = $host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param string $mailbox
     */
    public function setMailbox($mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }
}
