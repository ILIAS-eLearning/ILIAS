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

class ilPDFGenerationConfigStoredObjective implements Setup\Objective
{
    protected ilPDFGenerationSetupConfig $config;

    public function __construct(ilPDFGenerationSetupConfig $config)
    {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/PDFGeneration";
    }

    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @return ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
    
        $ini->setVariable(
            "tools",
            "phantomjs",
            $this->config->getPathToPhantomJS() ?? ''
        );

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return $ini->readVariable("tools", "phantomjs") !== $this->config->getPathToPhantomJS();
    }
}
