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

use ILIAS\Setup;

/**
 * Store information about https is enabled
 */
class ilWebServicesConfigStoredObjective implements Setup\Objective
{
    protected ilWebServicesSetupConfig $config;

    public function __construct(ilWebServicesSetupConfig $config)
    {
        $this->config = $config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Store information about web services in the settings";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilIniFilesPopulatedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");
        $settings->set(
            "soap_user_administration",
            $this->bool2string($this->config->isSOAPUserAdministration())
        );
        $settings->set("soap_wsdl_path", $this->config->getSOAPWsdlPath());
        $settings->set("soap_connect_timeout", (string) $this->config->getSOAPConnectTimeout());
        $settings->set("soap_response_timeout", (string) $this->config->getSoapResponseTimeout());
        $settings->set("rpc_server_host", $this->config->getRPCServerHost());
        $settings->set("rpc_server_port", (string) $this->config->getRPCServerPort());

        $ilias_ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $ilias_ini->setVariable(
            'webservices',
            'soap_internal_wsdl_path',
            $this->config->getSoapInternalWsdlPath()
        );
        $ilias_ini->setVariable(
            'webservices',
            'soap_internal_wsdl_verify_peer',
            (string) $this->config->getSoapInternalWsdlVerifyPeer()
        );
        $ilias_ini->setVariable(
            'webservices',
            'soap_internal_wsdl_verify_peer_name',
            (string) $this->config->getSoapInternalWsdlVerifyPeerName()
        );
        $ilias_ini->setVariable(
            'webservices',
            'soap_internal_wsdl_allow_self_signed',
            (string) $this->config->getSoapInternalWsdlAllowSelfSigned()
        );
        $ilias_ini->write();

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function bool2string(bool $value): string
    {
        if ($value) {
            return "1";
        }
        return "0";
    }
}
