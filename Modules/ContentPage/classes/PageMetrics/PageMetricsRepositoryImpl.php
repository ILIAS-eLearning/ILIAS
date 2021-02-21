<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics;

use ILIAS\ContentPage\PageMetrics\Entity\PageMetrics;
use ilDBInterface;
use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;

/**
 * Class PageMetricsRepositoryImp
 * @package ILIAS\ContentPage\PageMetrics
 * @author Michael Jansen <mjansen@databay.de>
 */
class PageMetricsRepositoryImp implements PageMetricsRepository
{
    /** @var ilDBInterface */
    private $db;

    /**
     * PageMetricsRepositoryImp constructor.
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function store(PageMetrics $pageMetrics) : void
    {
        $this->db->replace(
            'content_page_metrics',
            [
                'content_page_id' => ['integer', $pageMetrics->contentPageId()],
                'page_id' => ['integer', $pageMetrics->pageId()],
                'lang' => ['text', $pageMetrics->language()],
            ],
            [
                'reading_time' => ['integer', $pageMetrics->readingTime()->minutes()],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(PageMetrics $pageMetrics) : void
    {
        $this->db->queryF(
            'DELETE FROM content_page_metrics WHERE content_page_id = %s AND page_id = %s AND lang = %s',
            ['integer', 'integer', 'text'],
            [$pageMetrics->contentPageId(), $pageMetrics->pageId(), $pageMetrics->language()]
        );
    }

    /**
     * @inheritDoc
     */
    public function findBy(int $contentPageId, int $pageId, string $language) : PageMetrics
    {
        $res = $this->db->queryF(
            'SELECT * FROM content_page_metrics WHERE content_page_id = %s AND page_id = %s AND lang = %s',
            ['integer', 'integer', 'text'],
            [$contentPageId, $pageId, $language]
        );
        $row = $this->db->fetchAssoc($res);
        if (is_array($row) && isset($row['content_page_id'])) {
            return new PageMetrics(
                (int) $row['content_page_id'],
                (int) $row['page_id'],
                $row['lang'],
                new PageReadingTime((int) $row['reading_time'])
            );
        }

        throw CouldNotFindPageMetrics::by($contentPageId, $pageId, $language);
    }
}
