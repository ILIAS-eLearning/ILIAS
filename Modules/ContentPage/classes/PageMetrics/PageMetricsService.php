<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics;

use ILIAS\Refinery\Factory;
use ILIAS\ContentPage\PageMetrics\Entity\PageMetrics;
use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;
use ilContentPageObjectConstants;
use ilContentPagePage;
use ilContentPagePageGUI;
use ilException;
use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;
use ILIAS\ContentPage\PageMetrics\Command\GetPageMetricsCommand;

/**
 * Class PageMetricsService
 * @package ILIAS\ContentPage\PageMetrics
 */
final class PageMetricsService implements ilContentPageObjectConstants
{
    /** @var PageMetricsRepository */
    private $pageMetricsRepository;
    /** @var Factory */
    private $refinery;

    /**
     * PageMetricsService constructor.
     * @param PageMetricsRepository $pageMetricsRepository
     * @param Factory               $refinery
     */
    public function __construct(PageMetricsRepository $pageMetricsRepository, Factory $refinery)
    {
        $this->pageMetricsRepository = $pageMetricsRepository;
        $this->refinery = $refinery;
    }

    /**
     * @param int    $contentPageId
     * @param string $language
     * @return bool
     */
    protected function doesPageExistsForLanguage(int $contentPageId, string $language) : bool
    {
        return ilContentPagePage::_exists(self::OBJ_TYPE, $contentPageId, $language, true);
    }

    /**
     * @param int    $contentPageId
     * @param string $language
     */
    protected function ensurePageObjectExists(int $contentPageId, string $language) : void
    {
        if (!$this->doesPageExistsForLanguage($contentPageId, $language)) {
            $pageObject = new ilContentPagePage();
            $pageObject->setParentId($contentPageId);
            $pageObject->setId($contentPageId);
            $pageObject->setLanguage($language);
            $pageObject->createFromXML();
        }
    }

    /**
     * @param StorePageMetricsCommand $command
     * @throws ilException
     */
    public function store(StorePageMetricsCommand $command) : void
    {
        $this->ensurePageObjectExists($command->getContentPageId(), $command->getLanguage());

        $pageObjectGUI = new ilContentPagePageGUI($command->getContentPageId(), 0, false, $command->getLanguage());
        $pageObjectGUI->setEnabledTabs(false);
        $pageObjectGUI->setFileDownloadLink(ILIAS_HTTP_PATH);
        $pageObjectGUI->setFullscreenLink(ILIAS_HTTP_PATH);
        $pageObjectGUI->setSourcecodeDownloadScript(ILIAS_HTTP_PATH);
        $pageObjectGUI->setProfileBackUrl(ILIAS_HTTP_PATH);
        $text = $pageObjectGUI->getHTML();

        $readingTimeTransformation = $this->refinery->string()->estimatedReadingTime();
        $readingTime = new PageReadingTime($readingTimeTransformation->transform($text));

        $pageMetrics = new PageMetrics(
            $command->getContentPageId(),
            $command->getContentPageId(),
            $command->getLanguage(),
            $readingTime
        );
        $this->pageMetricsRepository->store($pageMetrics);
    }

    /**
     * @param GetPageMetricsCommand $command
     * @return PageMetrics
     * @throws CouldNotFindPageMetrics
     */
    public function get(GetPageMetricsCommand $command) : PageMetrics
    {
        return $this->pageMetricsRepository->findBy(
            $command->getContentPageId(),
            $command->getContentPageId(),
            $command->getLanguage()
        );
    }
}
