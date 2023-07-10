<?php

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilKitchenSinkDataCollectedObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function getArtifactPath(): string
    {
        return ilSystemStyleDocumentationGUI::DATA_PATH;
    }

    public function build(): Setup\Artifact
    {
        $crawler = new Crawler\FactoriesCrawler();
        return new Setup\Artifact\ArrayArtifact(
            $crawler->crawlFactory(ilSystemStyleDocumentationGUI::ROOT_FACTORY_PATH)
                    ->jsonSerialize()
        );
    }
}
