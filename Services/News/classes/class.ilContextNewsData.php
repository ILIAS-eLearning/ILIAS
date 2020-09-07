<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News data
 *
 * @author killinh@leifos.de
 * @ingroup ServicesNews
 */
class ilContextNewsData
{
    /**
     * @var ilNewsServiceDependencies
     */
    protected $_deps;

    /**
     * @var ilNewsService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(int $obj_id, string $obj_type, int $subtype, string $subid, ilNewsService $service, $_deps)
    {
        $this->service = $service;
        $this->_deps = $_deps;
    }
}
