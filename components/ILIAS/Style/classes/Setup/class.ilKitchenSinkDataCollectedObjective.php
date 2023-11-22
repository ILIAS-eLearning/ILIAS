<?php

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilKitchenSinkDataCollectedObjective extends Setup\Artifact\BuildArtifactObjective
{
    public const ROOT_FACTORY_PATH = __DIR__ . '/../../System/data/abstractDataFactory.php';
    public const DATA_PATH = '../components/ILIAS/Style/System/data/data.php';

    public function getArtifactPath(): string
    {
        return self::DATA_PATH;
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
