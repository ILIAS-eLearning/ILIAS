<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    private ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

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
