<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\GlobalSettings;

/**
 * Interface Storage
 * @package ILIAS\ContentPage\GlobalSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Storage
{
    /**
     * @return Settings
     */
    public function getSettings() : Settings;

    /**
     * @param Settings $settings
     */
    public function store(Settings $settings) : void;
}