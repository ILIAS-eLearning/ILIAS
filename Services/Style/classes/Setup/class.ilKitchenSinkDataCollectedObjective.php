<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilKitchenSinkDataCollectedObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function __construct()
    {
        $this->crawler_path = ilSystemStyleDocumentationGUI::ROOT_FACTORY_PATH;
        $this->data_path = ilSystemStyleDocumentationGUI::DATA_DIRECTORY
            . "/" . ilSystemStyleDocumentationGUI::DATA_FILE;
    }

    public function getArtifactPath() : string
    {
        return $this->data_path;
    }

    public function build() : Setup\Artifact
    {
        $crawler = new Crawler\FactoriesCrawler();
        return new Setup\Artifact\ArrayArtifact($crawler->crawlFactory($this->crawler_path)->jsonSerialize());
    }
}
