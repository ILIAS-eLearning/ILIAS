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
    public function __construct(string $mailbox, string $host)
    {
        $this->mailbox = $mailbox;
        $this->host    = $host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @param string $mailbox
     */
    public function setMailbox(string $mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMailbox() : string
    {
        return $this->mailbox;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return implode('@', [
            $this->getMailbox(),
            $this->getHost(),
        ]);
    }
}
