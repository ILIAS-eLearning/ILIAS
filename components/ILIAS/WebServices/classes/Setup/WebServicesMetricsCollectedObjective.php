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

declare(strict_types=1);

namespace ILIAS\WebServices\Setup;

use ILIAS\Setup;

class WebServicesMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor('common');

        $is_soap_enabled = (bool) $settings->get('soap_user_administration', '0');
        $storage->storeConfigBool(
            'soap_user_administration',
            $is_soap_enabled,
            'SOAP user administration enabled.'
        );

        if ($is_soap_enabled) {
            $storage->storeConfigText(
                'soap_wsdl_path',
                $settings->get('soap_wsdl_path', ''),
                'Path to SOAP WSDL file.'
            );

            $storage->storeConfigText(
                'soap_connect_timeout',
                $settings->get('soap_connect_timeout', ''),
                'SOAP connection timeout in seconds.'
            );

            $storage->storeConfigText(
                'soap_response_timeout',
                $settings->get('soap_response_timeout', ''),
                'SOAP response timeout in seconds.'
            );

            $storage->storeConfigText(
                'soap_internal_wsdl_path',
                $settings->get('soap_internal_wsdl_path', ''),
                'Path to internal SOAP WSDL file.'
            );

            $storage->storeConfigBool(
                'soap_internal_wsdl_verify_peer',
                (bool) $settings->get('soap_internal_wsdl_verify_peer', '1'),
                'Verify SSL peer for internal WSDL.'
            );

            $storage->storeConfigBool(
                'soap_internal_wsdl_verify_peer_name',
                (bool) $settings->get('soap_internal_wsdl_verify_peer_name', '1'),
                'Verify SSL peer name for internal WSDL.'
            );

            $storage->storeConfigBool(
                'soap_internal_wsdl_allow_self_signed',
                (bool) $settings->get('soap_internal_wsdl_allow_self_signed', '0'),
                'Allow self-signed certificates for internal WSDL.'
            );
        }

        $storage->storeConfigText(
            'rpc_server_host',
            $settings->get('rpc_server_host', ''),
            'RPC server host address.'
        );

        $storage->storeConfigText(
            'rpc_server_port',
            $settings->get('rpc_server_port', ''),
            'RPC server port.'
        );
    }
}
