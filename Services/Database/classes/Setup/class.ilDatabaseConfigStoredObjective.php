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
 
use ILIAS\Setup;

class ilDatabaseConfigStoredObjective extends ilDatabaseObjective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/Database";
    }

    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @return array<int, \ilDatabaseExistsObjective|\ilIniFilesLoadedObjective>
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseExistsObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $type = $this->config->getType();

        if ($type === 'postgres' || $type === 'pdo-postgre') {
            throw new Setup\NotExecutableException('ILIAS 8 no longer Supports POSTGRES');
        }

        $client_ini->setVariable("db", "type", $type);
        $client_ini->setVariable("db", "host", $this->config->getHost());
        $client_ini->setVariable("db", "name", $this->config->getDatabase());
        $client_ini->setVariable("db", "user", $this->config->getUser());
        $client_ini->setVariable("db", "port", (string) ($this->config->getPort() ?? ""));
        $pw = $this->config->getPassword();
        $client_ini->setVariable("db", "pass", $pw !== null ? $pw->toString() : "");

        if (!$client_ini->write()) {
            throw new Setup\UnachievableException("Could not write client.ini.php");
        }

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment) : bool
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $port = $this->config->getPort() ?? "";
        $pass = $this->config->getPassword() !== null ? $this->config->getPassword()->toString() : "";

        return
            $client_ini->readVariable("db", "type") !== $this->config->getType() ||
            $client_ini->readVariable("db", "host") !== $this->config->getHost() ||
            $client_ini->readVariable("db", "name") !== $this->config->getDatabase() ||
            $client_ini->readVariable("db", "user") !== $this->config->getUser() ||
            $client_ini->readVariable("db", "port") !== $port ||
            $client_ini->readVariable("dv", "pass") !== $pass
        ;
    }
}
