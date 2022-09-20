<?php

declare(strict_types=1);

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

use ILIAS\ContentPage\GlobalSettings\StorageImpl;
use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\CouldNotFindPageMetrics;
use ILIAS\ContentPage\PageMetrics\Command\GetPageMetricsCommand;

class ilObjContentPageListGUI extends ilObjectListGUI implements ilContentPageObjectConstants
{
    private PageMetricsService $pageMetricsService;

    public function __construct(int $a_context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;

        parent::__construct($a_context);
        $this->pageMetricsService = new PageMetricsService(
            new PageMetricsRepositoryImp($DIC->database()),
            $DIC->refinery()
        );
    }

    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = self::OBJ_TYPE;
        $this->gui_class_name = 'ilObjContentPageGUI';

        $this->commands = ilObjContentPageAccess::_getCommands();

        $this->lng->loadLanguageModule('copa');
    }

    public function getInfoScreenStatus(): bool
    {
        if (ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            "1"
        )) {
            return $this->info_screen_enabled;
        }

        return false;
    }

    public function getProperties(): array
    {
        $properties = [];

        $maySee = $this->rbacsystem->checkAccess('visible', $this->ref_id);
        $mayRead = $this->rbacsystem->checkAccess('read', $this->ref_id);

        if (!$maySee && !$mayRead) {
            return $properties;
        }

        $properties = parent::getProperties();

        if (!$mayRead || ilObject::lookupOfflineStatus($this->obj_id)) {
            return $properties;
        }

        $settingsStorage = new StorageImpl($this->settings);
        if (!$settingsStorage->getSettings()->isReadingTimeEnabled()) {
            return $properties;
        }

        try {
            $ot = ilObjectTranslation::getInstance($this->obj_id);
            $language = $ot->getEffectiveContentLang($this->user->getCurrentLanguage(), $this->type);

            $pageMetrics = $this->pageMetricsService->get(
                new GetPageMetricsCommand($this->obj_id, $language)
            );

            $readingTimePropertyValue = sprintf(
                $this->lng->txt('copa_value_reading_time_f_p'),
                (string) $pageMetrics->readingTime()->minutes()
            );
            if (1 === $pageMetrics->readingTime()->minutes()) {
                $readingTimePropertyValue = sprintf(
                    $this->lng->txt('copa_value_reading_time_f_s'),
                    (string) $pageMetrics->readingTime()->minutes()
                );
            }

            $properties[] = [
                'alert' => false,
                'property' => $this->lng->txt('copa_prop_reading_time'),
                'value' => $readingTimePropertyValue,
            ];
        } catch (CouldNotFindPageMetrics $e) {
        }

        return $properties;
    }

    public function checkInfoPageOnAsynchronousRendering(): bool
    {
        return true;
    }
}
