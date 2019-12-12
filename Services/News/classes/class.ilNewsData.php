<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News data
 *
 * @author killinh@leifos.de
 * @ingroup ServicesNews
 */
class ilNewsData
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
    public function __construct(ilNewsService $service, $_deps)
    {
        $this->service = $service;
        $this->_deps = $_deps;
    }

    /**
     * Save news item
     *
     * @param ilNewsItem $news_item
     * @return int
     */
    public function save(ilNewsItem $news_item) : int
    {
        if ($news_item->getId() > 0) {
            $news_item->update(true);
        } else {
            $news_item->create();
        }
        return $news_item->getId();
    }

    /**
     * Get news of context
     *
     * @param ilNewsContext $context
     * @return ilNewsItem[]
     */
    public function getNewsOfContext(ilNewsContext $context) : array
    {
        return ilNewsItem::getNewsOfContext(
            $context->getObjId(),
            $context->getObjType(),
            $context->getSubId(),
            $context->getSubType()
        );
    }

    /**
     * Delete a news item
     * @param ilNewsItem $news_item
     */
    public function delete(ilNewsItem $news_item)
    {
        $news_item->delete();
    }
}
