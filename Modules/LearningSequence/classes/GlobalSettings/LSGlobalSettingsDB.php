<?php declare(strict_types=1);

/**
 * Repository for LSGlobalSettings
 */
interface LSGlobalSettingsDB
{
    public function getSettings() : LSGlobalSettings;

    public function storeSettings(LSGlobalSettings $settings);
}
