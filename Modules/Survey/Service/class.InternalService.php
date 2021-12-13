<?php
declare(strict_types = 1);


/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Mode\ModeFactory;/**
 * Survey internal service
 * @author killing@leifos.de
 */

class InternalService
{
    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * @var InternalUIService
     */
    protected $ui;

    /**
     * @var InternalDomainService
     */
    protected $domain;

    /**
     * @var InternalRepoService
     */
    protected $repo;

    /**
     * @var ModeFactory
     */
    protected $mode_factory;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $object_service = $DIC->object();
        $this->db = $DIC->database();

        $this->mode_factory = new ModeFactory();
        $this->data = new InternalDataService();
        $this->repo = new InternalRepoService(
            $this->data(),
            $this->db
        );
        $this->domain = new InternalDomainService(
            $this->mode_factory,
            $this->repo,
            $this->data
        );
        $this->ui = new InternalUIService(
            $object_service,
            $this->mode_factory,
            $this->domain
        );
        $this->mode_factory->setInternalService($this);
    }

    /**
     * @return InternalUIService
     */
    public function ui() : InternalUIService
    {
        return $this->ui;
    }

    /**
     * Booking service repos
     *
     * @return InternalRepoService
     */
    public function repo() : InternalRepoService
    {
        return $this->repo;
    }

    /**
     * Booking service data objects
     * @return InternalDataService
     */
    public function data() : InternalDataService
    {
        return $this->data;
    }

    /**
     * @return InternalDomainService
     */
    public function domain() : InternalDomainService
    {
        return $this->domain;
    }
}
