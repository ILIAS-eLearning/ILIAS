<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\Event;

use ilContentPagePage;

/**
 * Class PageUpdatedEvent
 * @package ILIAS\ContentPage\PageMetrics\Event
 * @author Michael Jansen <mjansen@databay.de>
 */
final class PageUpdatedEvent
{
    /** @var ilContentPagePage */
    private $page;

    /**
     * PageUpdatedEvent constructor.
     * @param ilContentPagePage $page
     */
    public function __construct(ilContentPagePage $page)
    {
        $this->page = $page;
    }

    /**
     * @return ilContentPagePage
     */
    public function page() : ilContentPagePage
    {
        return $this->page;
    }
}
