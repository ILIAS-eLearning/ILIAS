<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Report extends Panel implements C\Panel\Report
{
    use ComponentHelper;

    /**
     * @param string $title
     * @param C\Panel\Sub[] | C\Panel\Sub $content
     */
    public function __construct($title, $content)
    {
        $types = [C\Panel\Sub::class];
        $content = $this->toArray($content);
        $this->checkArgListElements("content", $content, $types);

        parent::__construct($title, $content);
    }
}
