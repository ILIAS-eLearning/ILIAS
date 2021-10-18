<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddress
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddress
{
    protected string $mailbox = '';
    protected string $host = '';

    public function __construct(string $mailbox, string $host)
    {
        $this->mailbox = $mailbox;
        $this->host = $host;
    }

    public function setHost(string $host) : void
    {
        $this->host = $host;
    }

    public function setMailbox(string $mailbox) : void
    {
        $this->mailbox = $mailbox;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    
    public function getMailbox() : string
    {
        return $this->mailbox;
    }

    public function __toString() : string
    {
        return implode('@', [
            $this->getMailbox(),
            $this->getHost(),
        ]);
    }
}
