<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Reader for remote ical calendars
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilCalendarRemoteReader
{
    protected const TYPE_ICAL = 1;

    // Fixed in the moment
    private int $type = self::TYPE_ICAL;
    private ?ilCurlConnection $curl = null;

    private string $url = '';
    private string $user = '';
    private string $pass = '';

    private string $ical = '';

    private ilLogger $logger;

    public function __construct(string $a_url)
    {
        global $DIC;

        $this->logger = $DIC->logger()->cal();
        $this->url = $a_url;
    }

    public function setUser(string $a_user): void
    {
        $this->user = $a_user;
    }

    public function setPass(string $a_pass): void
    {
        $this->pass = $a_pass;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function read(): void
    {
        $this->initCurl();

        switch ($this->getType()) {
            case self::TYPE_ICAL:
                $this->readIcal();
                break;
        }
    }

    public function import(ilCalendarCategory $cat): void
    {
        switch ($this->getType()) {
            case self::TYPE_ICAL:
                $this->importIcal($cat);
                break;
        }
    }

    protected function readIcal(): void
    {
        $this->ical = $this->call();
        $this->logger->debug($this->ical);
    }

    protected function importIcal(ilCalendarCategory $cat): void
    {
        // Delete old appointments
        foreach (ilCalendarCategoryAssignments::_getAssignedAppointments(array($cat->getCategoryID())) as $app_id) {
            ilCalendarEntry::_delete($app_id);
        }
        ilCalendarCategoryAssignments::_deleteByCategoryId($cat->getCategoryID());

        // Import new appointments
        $parser = new ilICalParser($this->ical, ilICalParser::INPUT_STRING);
        $parser->setCategoryId($cat->getCategoryID());
        $parser->parse();
    }

    protected function initCurl(): void
    {
        try {
            $this->replaceWebCalProtocol();

            $this->curl = new ilCurlConnection($this->getUrl());
            $this->curl->init();

            $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
            $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
            $this->curl->setOpt(CURLOPT_RETURNTRANSFER, 1);

            $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            $this->curl->setOpt(CURLOPT_MAXREDIRS, 3);

            if ($this->user) {
                $this->curl->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $this->curl->setOpt(CURLOPT_USERPWD, $this->user . ':' . $this->pass);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function replaceWebCalProtocol()
    {
        if (substr($this->getUrl(), 0, 6) == 'webcal') {
            $purged = preg_replace('/webcal/', 'http', $this->getUrl(), 1);
            $this->url = (string) $purged;
        }
    }

    private function call(): string
    {
        try {
            return $this->curl->exec();
        } catch (ilCurlConnectionException $exc) {
            throw($exc);
        }
    }
}
