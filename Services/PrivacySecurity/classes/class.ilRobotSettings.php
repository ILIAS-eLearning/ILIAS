<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesPrivacySecurity
 */
class ilRobotSettings
{
    private bool $open_robots = false;
    private ilSetting $settings;
    private static ?self $instance = null;

    /**
     * Private constructor => use getInstance
     */
    private function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->read();
    }

    /**
     * get singleton instance
     */
    public static function getInstance(): ?\ilRobotSettings
    {
        if (!self::$instance instanceof ilRobotSettings) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if client is open for robots
     */
    public function robotSupportEnabled(): bool
    {
        return $this->open_robots;
    }

    /**
     * Read settings
     * @access private
     */
    private function read(): void
    {
        $this->open_robots = (bool) $this->settings->get('open_google', null);
    }

    /**
     * Indirect Check of allow override
     * @access public
     */
    public function checkRewrite(): bool
    {
        if (!function_exists('apache_lookup_uri')) {
            return true;
        }

        $url = ILIAS_HTTP_PATH . '/goto_' . CLIENT_ID . '_root_1.html';
        $status_info = @apache_lookup_uri($url);

        // fallback for php as cgi (and available remote fopen)
        if ($status_info === false && ini_get('allow_url_fopen')) {
            // fopen respects HTTP error codes
            $fp = @fopen($url, 'r');
            if ($fp) {
                fclose($fp);
                return true;
            }
            return false;
        }

        return $status_info->status == 200;
    }
}
