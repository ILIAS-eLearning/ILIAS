<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\DOM;

/**
 * Page DOM wrapper
 *
 * @author killing@leifos.de
 */
class PageDOM
{
    /**
     * @var \DOMDocument
     */
    protected $doc;

    /**
     * Constructor
     */
    public function __construct(\DOMDocument $doc)
    {
        $this->doc = $doc;
    }
}
