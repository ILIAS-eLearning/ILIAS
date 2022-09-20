<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSAuth
{
    protected ilLogger $log;
    protected array $mids = array();

    private string $realm = '';
    private string $url = '';
    private ?int $pid = null;

    public function __construct()
    {
        global $DIC;

        $this->log = $DIC->logger()->wsrv();
    }

    public function setPid(int $a_pid): void
    {
        $this->pid = $a_pid;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setUrl(string $a_url): void
    {
        $this->url = $a_url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setRealm(string $a_realm): void
    {
        $this->realm = $a_realm;
    }

    public function getRealm(): string
    {
        return $this->realm;
    }
}
