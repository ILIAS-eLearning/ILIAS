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
    private PageMetricsRepository $pageMetricsRepository;
    private Factory $refinery;

    public function __construct(PageMetricsRepository $pageMetricsRepository, Factory $refinery)
    {
        $this->pageMetricsRepository = $pageMetricsRepository;
        $this->refinery = $refinery;
    }

    protected function doesPageExistsForLanguage(int $contentPageId, string $language) : bool
    {
        return ilContentPagePage::_exists(self::OBJ_TYPE, $contentPageId, $language);
    }

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

        $pageObjectGUI = new ilContentPagePageGUI($command->getContentPageId(), 0, true, $command->getLanguage());
        $pageObjectGUI->setEnabledTabs(false);
        $pageObjectGUI->setFileDownloadLink(ILIAS_HTTP_PATH);
        $pageObjectGUI->setFullscreenLink(ILIAS_HTTP_PATH);
        $pageObjectGUI->setSourcecodeDownloadScript(ILIAS_HTTP_PATH);
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
