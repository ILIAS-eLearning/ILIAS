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

use ILIAS\DI;
use ILIAS\Setup;

class ilChatroomMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilFileSystemComponentDataDirectoryCreatedObjective("chatroom")
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["DIC"]["ilBench"] = null;

        $chatAdministrations = ilObject::_getObjectsByType('chta');
        $chatAdministration = current($chatAdministrations);

        $chat_admin = new ilChatroomAdmin((int) $chatAdministration['obj_id']);
        $settings = $chat_admin->loadGeneralSettings();

        if (count($settings) > 0) {
            $storage->storeConfigText(
                "address",
                $settings['address'] ?? "",
                "IP-Address/FQN of Chat Server."
            );
            $storage->storeConfigText(
                "port",
                (string) ($settings['port'] ?? ""),
                "Port of the chat server."
            );
            $storage->storeConfigText(
                "sub_directory",
                $settings['sub_directory'] ?? "",
                "http(s)://[IP/Domain]/[SUB_DIRECTORY]"
            );

            $storage->storeConfigText(
                "protocol",
                $settings['protocol'] ?? "",
                "Protocol used for connection (http/https)."
            );

            if ($settings['protocol'] === 'https') {
                $cert = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['cert'] ?? ""
                );
                $key = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['key'] ?? ""
                );
                $dhparam = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['dhparam'] ?? ""
                );
                $https = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_COLLECTION,
                    [
                        "cert" => $cert,
                        "key" => $key,
                        "dhparam" => $dhparam,
                    ],
                    "Holds parameters for https."
                );
                $storage->store("https", $https);
            }

            $storage->storeConfigText(
                "log",
                (string) ($settings['log'] ?? ''),
                "Absolute server path to the chat server's log file."
            );
            $storage->storeConfigText(
                "log_level",
                $settings['log_level'] ?? "",
                "Possible values are emerg, alert, crit error, warning, notice, info, debug, silly."
            );
            $storage->storeConfigText(
                "error_log",
                $settings['error_log'] ?? "",
                "Absolute server path to the chat server's error log file."
            );

            if ($settings['ilias_proxy']) {
                $ilias_url = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['ilias_url'] ?? ""
                );
                $ilias_proxy = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_COLLECTION,
                    [
                        "ilias_url" => $ilias_url
                    ],
                    "Holds proxy url if ILIAS proxy is enabled."
                );
                $storage->store("ilias_proxy", $ilias_proxy);
            } else {
                $storage->storeConfigBool(
                    "ilias_proxy",
                    false,
                    "Holds proxy url if ILIAS proxy is enabled."
                );
            }

            if ($settings['client_proxy']) {
                $client_url = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['client_url'] ?? ""
                );
                $client_proxy = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_COLLECTION,
                    [
                        "client_url" => $client_url
                    ],
                    "Holds proxy url if client proxy is enabled."
                );
                $storage->store("client_proxy", $client_proxy);
            } else {
                $storage->storeConfigBool(
                    "client_proxy",
                    false,
                    "Holds proxy url if client proxy is enabled."
                );
            }

            if ($settings['deletion_mode']) {
                $deletion_unit = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['deletion_unit'] ?? ""
                );
                $deletion_value = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    (string) ($settings['deletion_value'] ?? '')
                );
                $deletion_time = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $settings['deletion_time'] ?? ""
                );
                $deletion_mode = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_COLLECTION,
                    [
                        "deletion_unit" => $deletion_unit,
                        "deletion_value" => $deletion_value,
                        "deletion_time" => $deletion_time,
                    ],
                    "Holds information about deletion process."
                );
                $storage->store("deletion_mode", $deletion_mode);
            }
        }

        $GLOBALS["DIC"] = $DIC;
    }
}
