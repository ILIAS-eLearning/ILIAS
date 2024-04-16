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
use ILIAS\DI;
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilUITestRailExampleTestCasesObjective extends Setup\Artifact\BuildArtifactObjective
{
    private const TESTCASEWRITER = 'ui.testrail.xmlwriter';

    public function getArtifactPath(): string
    {
        return './testcases.xml'; //ilSystemStyleDocumentationGUI::DATA_PATH;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilComponentFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $component_factory = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_FACTORY);
        $plugin_admin = $environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN);

        $ORIG_DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["component.factory"] = $component_factory;
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;

        $path = __DIR__ . '/templates/testrail.case.xml';
        $tpl = new ilTemplate($path, true, true);
        $parser = new Crawler\ExamplesYamlParser();
        $testcases = new TestRailXMLWriter($tpl, $parser);

        $environment = $environment->withResource(self::TESTCASEWRITER, $testcases);

        parent::achieve($environment);
        $GLOBALS["DIC"] = $ORIG_DIC;
        return $environment;
    }

    public function build(): Setup\Artifact
    {
    }

    public function buildIn(Setup\Environment $env): Setup\Artifact
    {
        $crawler = new Crawler\FactoriesCrawler();
        $data = $crawler->crawlFactory(ilSystemStyleDocumentationGUI::ROOT_FACTORY_PATH);

        $sorted = [];
        foreach($data as $entry) {
            $path = explode('/', $entry->getPath());
            if(end($path) === 'Factory') {
                continue;
            }
            $path = array_slice($path, 3);
            $section = array_shift($path);
            if(! array_key_exists($section, $sorted)) {
                $sorted[$section] = [];
            }
            $path = array_slice($path, 0, -1);
            $path[] = $entry->getTitle();
            $sorted[$section][] = [implode('/', $path), $entry];
        }
        ksort($sorted);

        $writer = $env->getResource(self::TESTCASEWRITER)
            ->withData($sorted);
        return new Setup\Artifact\XMLArtifact($writer->getXML());
    }
}
