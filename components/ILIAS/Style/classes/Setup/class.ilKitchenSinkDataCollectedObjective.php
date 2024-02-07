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
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilKitchenSinkDataCollectedObjective extends Setup\Artifact\BuildArtifactObjective
{
    public const ROOT_FACTORY_PATH = __DIR__ . '/../../System/data/abstractDataFactory.php';

    public function getArtifactName(): string
    {
        return "kitchen_sink_data";
    }


    public function build(): Setup\Artifact
    {
        $crawler = new Crawler\FactoriesCrawler();
        return new Setup\Artifact\ArrayArtifact(
            $crawler->crawlFactory(self::ROOT_FACTORY_PATH)
                    ->jsonSerialize()
        );
    }
}
