<?php

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

use ILIAS\Setup;

class ilHttpMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return \ilIniFilesLoadedObjective[]|\ilSettingsFactoryExistsObjective[]
     */
    public function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $ilias_ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        if ($ilias_ini) {
            $storage->storeConfigText(
                "http_path",
                fn() => $ilias_ini->readVariable("server", "http_path"),
                "URL of the server."
            );
            $storage->storeConfigText(
                "https forced",
                fn() => $ilias_ini->readVariable("https", "forced"),
                ""
            );

            if ($ilias_ini->readVariable("https", "auto_https_detect_enabled")) {
                $header_name = new Setup\Metrics\Metric(
                    Setup\Metrics\MetricStability::CONFIG,
                    Setup\Metrics\MetricType::TEXT,
                    fn() => $ilias_ini->readVariable("https", "auto_https_detect_header_name"),
                    "The name of the header used for https detection in requests."
                );
                $header_value = new Setup\Metrics\Metric(
                    Setup\Metrics\MetricStability::CONFIG,
                    Setup\Metrics\MetricType::TEXT,
                    fn() => $ilias_ini->readVariable("https", "auto_https_detect_header_value"),
                    "The value in the named header that indicates usage of https in requests."
                );
                $https_metrics = new Setup\Metrics\Metric(
                    Setup\Metrics\MetricStability::CONFIG,
                    Setup\Metrics\MetricType::COLLECTION,
                    fn() => [
                        "header_name" => $header_name,
                        "header_value" => $header_value
                    ],
                    "The properties of a request used for https detection."
                );
                $storage->store("https_autodetection", $https_metrics);
            } else {
                $storage->storeConfigBool(
                    "https_autodetection",
                    fn() => false,
                    "Does the server attempt to detect https in incoming requests?"
                );
            }
        }

        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        if (!$factory) {
            return;
        }
        $settings = $factory->settingsFor("common");

        if ($settings->get("proxy_status")) {
            $host = new Setup\Metrics\Metric(
                Setup\Metrics\MetricStability::CONFIG,
                Setup\Metrics\MetricType::TEXT,
                fn() => $settings->get("proxy_host"),
                "The host of the proxy."
            );
            $port = new Setup\Metrics\Metric(
                Setup\Metrics\MetricStability::CONFIG,
                Setup\Metrics\MetricType::TEXT,
                fn() => $settings->get("proxy_port"),
                "The port of the proxy."
            );
            $proxy = new Setup\Metrics\Metric(
                Setup\Metrics\MetricStability::CONFIG,
                Setup\Metrics\MetricType::COLLECTION,
                fn() => [
                    "host" => $host,
                    "port" => $port
                ],
                "The proxy that is used for outgoing connections."
            );
            $storage->store("proxy", $proxy);
        } else {
            $storage->storeConfigBool(
                "proxy",
                fn() => false,
                "Does the server use a proxy for outgoing connections?"
            );
        }
    }
}
