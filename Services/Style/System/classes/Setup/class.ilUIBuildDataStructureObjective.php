<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\UI\Implementation\Crawler as Crawler;

class ilUIBuildDataStructureObjective extends Setup\BuildArtifactObjective
{

	public function __construct()
	{
		$this->crawler_path = ilSystemStyleDocumentationGUI::ROOT_FACTORY_PATH;
		$this->data_path = ilSystemStyleDocumentationGUI::DATA_DIRECTORY
			."/".ilSystemStyleDocumentationGUI::DATA_FILE;
	}

	public function getArtifactPath() : string
	{
		return $this->data_path;
	}


	public function build() : Setup\Artifact
	{
		$crawler = new Crawler\FactoriesCrawler();
		$entries = $crawler->crawlFactory($this->crawler_path);
		$entries_array = json_decode(json_encode($entries), true);
		return new Setup\ArrayArtifact($entries_array);
	}
}