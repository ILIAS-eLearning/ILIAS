<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * Content style internal service
 * @author Alexander Killing <killing@leifos.de>
 */
class Service
{
    /**
     * @var InternalService
     */
    protected $internal_service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->internal_service = new InternalService();
    }

    /**
     * @return InternalService
     */
    public function internal() : InternalService
    {
        return $this->internal_service;
    }
}
