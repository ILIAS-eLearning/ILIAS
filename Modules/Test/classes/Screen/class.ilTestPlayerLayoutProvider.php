<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\Image\Image;

/**
 * Class TestPlayerLayoutProvider
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/Test
 */
class ilTestPlayerLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    public const TEST_PLAYER_KIOSK_MODE_ENABLED = 'test_player_kiosk_mode_enabled';


    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }


    public function getLogoModification(CalledContexts $calledContexts): ?LogoModification
    {
        if ($this->isKioskModeEnabled($calledContexts)) {
            $logo = $this->globalScreen()->layout()->factory()->logo();

            $logo = $logo->withModification(function (Image $current) {
                return null;
            });

            return $logo->withHighPriority();
        }

        return null;
    }


    public function getMainBarModification(CalledContexts $calledContexts): ?MainBarModification
    {
        if ($this->isKioskModeEnabled($calledContexts)) {
            $mainBar = $this->globalScreen()->layout()->factory()->mainbar();

            $mainBar = $mainBar->withModification(function (MainBar $current) {
                return null;
            });

            return $mainBar->withHighPriority();
        }

        return null;
    }


    public function getMetaBarModification(CalledContexts $calledContexts): ?MetaBarModification
    {
        if ($this->isKioskModeEnabled($calledContexts)) {
            $metaBar = $this->globalScreen()->layout()->factory()->metabar();

            $metaBar = $metaBar->withModification(function (MetaBar $current) {
                return null;
            });

            return $metaBar->withHighPriority();
        }

        return null;
    }


    public function getFooterModification(CalledContexts $calledContexts): ?FooterModification
    {
        if ($this->isKioskModeEnabled($calledContexts)) {
            $footer = $this->globalScreen()->layout()->factory()->footer();

            $footer = $footer->withModification(function (Footer $current) {
                return null;
            });

            return $footer->withHighPriority();
        }

        return null;
    }


    /**
     * @param CalledContexts $calledContexts
     *
     * @return bool
     */
    protected function isKioskModeEnabled(CalledContexts $calledContexts): bool
    {
        $additionalData = $calledContexts->current()->getAdditionalData();
        $isKioskModeEnabled = $additionalData->is(self::TEST_PLAYER_KIOSK_MODE_ENABLED, true);

        return $isKioskModeEnabled;
    }
}
