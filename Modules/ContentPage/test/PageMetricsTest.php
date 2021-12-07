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
 ********************************************************************
 */

namespace ILIAS\ContentPage;

use Basho\Riak\Command\Bucket\Store;
use ilContentPagePage;
use ILIAS\ContentPage\PageMetrics\Command\GetPageMetricsCommand;
use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;
use ILIAS\ContentPage\PageMetrics\CouldNotFindPageMetrics;
use ILIAS\ContentPage\PageMetrics\Entity\PageMetrics;
use ILIAS\ContentPage\PageMetrics\Event\PageUpdatedEvent;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepository;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;
use PHPUnit\Framework\TestCase;
use ilDBInterface;

class PageMetricsTest extends TestCase
{
    public function testRepositoryThrowsExceptionWhenPageMetricsShouldBeRetrievedButNoPageMetricsExist() : void
    {
        $this->expectException(CouldNotFindPageMetrics::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $database->method('fetchAssoc')->willReturn(null);

        $repo = new PageMetricsRepositoryImp($database);
        $repo->findBy(1, 1, 'de');
    }

    public function testPageMetricsCouldBeRetrievedFromRepository() : void
    {
        $readingTimeInMinutes = 4711;
        $language = 'en';
        $pageId = 1337;
        $contentPageId = 1337;

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $database->method('fetchAssoc')->willReturn(
            [
                'content_page_id' => $contentPageId,
                'page_id' => $pageId,
                'lang' => $language,
                'reading_time' => $readingTimeInMinutes
            ]
        );

        $repo = new PageMetricsRepositoryImp($database);
        $pageMetrics = $repo->findBy($contentPageId, $pageId, $language);

        $this->assertEquals($readingTimeInMinutes, $pageMetrics->readingTime()->minutes());
        $this->assertEquals($language, $pageMetrics->language());
        $this->assertEquals($contentPageId, $pageMetrics->contentPageId());
        $this->assertEquals($pageId, $pageMetrics->pageId());
    }

    public function testPropertiesAreAccessedWhenStoringPageMetrics() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $pageMetrics = $this->getMockBuilder(PageMetrics::class)->disableOriginalConstructor()->getMock();
        $pageMetrics->expects($this->once())->method('contentPageId');
        $pageMetrics->expects($this->once())->method('pageId');
        $pageMetrics->expects($this->once())->method('language');
        $pageMetrics->expects($this->once())->method('readingTime');

        $repo = new PageMetricsRepositoryImp($database);
        $repo->store($pageMetrics);
    }

    public function testPropertiesAreAccessedWhenDeletingPageMetrics() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $pageMetrics = $this->getMockBuilder(PageMetrics::class)->disableOriginalConstructor()->getMock();
        $pageMetrics->expects($this->once())->method('contentPageId');
        $pageMetrics->expects($this->once())->method('pageId');
        $pageMetrics->expects($this->once())->method('language');

        $repo = new PageMetricsRepositoryImp($database);
        $repo->delete($pageMetrics);
    }

    public function testCommandsWorkAsExpected() : void
    {
        $getPageMetrics = new GetPageMetricsCommand(1337, 'de');
        $this->assertEquals('de', $getPageMetrics->getLanguage());
        $this->assertEquals(1337, $getPageMetrics->getContentPageId());

        $storePageMetrics = new StorePageMetricsCommand(1337, 'de');
        $this->assertEquals('de', $storePageMetrics->getLanguage());
        $this->assertEquals(1337, $storePageMetrics->getContentPageId());
    }

    public function testEventsWorkAsExpected() : void
    {
        $page = $this->getMockBuilder(ilContentPagePage::class)->disableOriginalConstructor()->getMock();

        $pageUpdated = new PageUpdatedEvent($page);
        $this->assertEquals($page, $pageUpdated->page());
    }
    
    public function testPageMetricsCanBeRetrievedFromService() : void
    {
        $readingTimeInMinutes = 4711;
        $language = 'fr';
        $pageId = 1337;
        $contentPageId = 1337;

        $readingTime = new PageReadingTime($readingTimeInMinutes);

        $pageMetrics = $this->getMockBuilder(PageMetrics::class)->disableOriginalConstructor()->getMock();
        $pageMetrics->method('contentPageId')->willReturn($contentPageId);
        $pageMetrics->method('pageId')->willReturn($pageId);
        $pageMetrics->method('language')->willReturn($language);
        $pageMetrics->method('readingTime')->willReturn($readingTime);

        $repo = $this->getMockBuilder(PageMetricsRepository::class)->getMock();
        $repo->expects($this->once())->method('findBy')->with($contentPageId, $pageId, $language)->willReturn($pageMetrics);

        $service = new PageMetricsService(
            $repo,
            new \ILIAS\Refinery\Factory(
                new \ILIAS\Data\Factory(),
                $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock(),
            )
        );

        $receivedPageMetrics = $service->get(new GetPageMetricsCommand($contentPageId, $language));
        $this->assertEquals($pageMetrics, $receivedPageMetrics);
    }
}
