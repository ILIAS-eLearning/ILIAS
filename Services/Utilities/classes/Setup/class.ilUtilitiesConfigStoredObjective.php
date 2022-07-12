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

/**
 * Stores configuration for the Utilities service (paths to various tools)
 * in the according ini-fields.
 */
class ilUtilitiesConfigStoredObjective implements Setup\Objective
{
    protected ilUtilitiesSetupConfig $config;
    
    public function __construct(ilUtilitiesSetupConfig $config)
    {
        $this->config = $config;
    }
    
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }
    
    public function getLabel() : string
    {
        return "Store configuration of Services/Utilities";
    }
    
    public function isNotable() : bool
    {
        return false;
    }
    
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }
    
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        
        $ini->setVariable("tools", "convert", $this->config->getPathToConvert());
        $ini->setVariable("tools", "zip", $this->config->getPathToZip());
        $ini->setVariable("tools", "unzip", $this->config->getPathToUnzip());
        
        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }
        
        return $environment;
    }
    
    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        
        return
            $ini->readVariable("tools", "convert") !== $this->config->getPathToConvert() ||
            $ini->readVariable("tools", "zip") !== $this->config->getPathToZip() ||
            $ini->readVariable("tools", "unzip") !== $this->config->getPathToUnzip();
    }
}
