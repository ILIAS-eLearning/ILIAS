<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ContentPage\GlobalSettings\StorageImpl;
use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\CouldNotFindPageMetrics;
use ILIAS\ContentPage\PageMetrics\Command\GetPageMetricsCommand;

/**
 * Class ilObjContentPageListGUI
 */
class ilObjContentPageListGUI extends ilObjectListGUI implements ilContentPageObjectConstants
{
    /** PageMetricsService */
    private $pageMetricsService;

    /**
     * ilObjContentPageListGUI constructor.
     * @param int $a_context
     */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;

        parent::__construct($a_context);
        $this->pageMetricsService = new PageMetricsService(
            new PageMetricsRepositoryImp($DIC->database()),
            $DIC->refinery()
        );
    }

    /**
     * @inheritdoc
     */
    public function init()
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

    /**
     * @inheritdoc
     */
    public function getInfoScreenStatus()
    {
        if (ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            true
        )) {
            return $this->info_screen_enabled;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProperties()
    {
        $properties = [];

        $settingsStorage = new StorageImpl($this->settings);
        if (!$settingsStorage->getSettings()->isReadingTimeEnabled()) {
            return $properties;
        }

        if (!$this->access->checkAccess('read', '', $this->ref_id)) {
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

    /**
     * @inheritdoc
     */
    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return true;
    }
}
