<?php
require_once("Services/Style/System/classes/Documentation/class.ilKSDocumentationExplorerGUI.php");
require_once("Services/Style/System/classes/Documentation/class.ilKSDocumentationEntryGUI.php");
require_once("libs/composer/vendor/geshi/geshi/src/geshi.php");


use ILIAS\UI\Implementation\Crawler as Crawler;
/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleDocumentationGUI
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl $ctrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	const ROOT_FACTORY_PATH = "./Services/Style/System/data/abstractDataFactory.php";
	const DATA_DIRECTORY = "./Services/Style/System/data";
	const DATA_FILE = "data.json";
	public static $DATA_PATH;

	/**
	 * ilSystemStyleDocumentationGUI constructor.
	 * @param string $skin_id
	 * @param string $style_id
	 */
	function __construct($skin_id = "",$style_id = "")
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];

		self::$DATA_PATH= self::DATA_DIRECTORY."/".self::DATA_FILE;

	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		switch ($cmd)
		{
			case 'parseEntries':
				$this->$cmd();
				$this->show();
				break;
			default:
				$this->show();
				break;
		}
	}

	public function show(){
		$entries = $this->readEntries();
		$toolbar = new ilToolbarGUI();
		$reload_btn = ilLinkButton::getInstance();
		$reload_btn->setCaption($this->lng->txt('refresh_entries'),false);
		if($_GET["node_id"]){
			$this->ctrl->saveParameter($this,"node_id");
		}
		$reload_btn->setUrl($this->ctrl->getLinkTarget($this, 'parseEntries'));
		$toolbar->addButtonInstance($reload_btn);
		$explorer = new ilKSDocumentationExplorerGUI($this, "entries", $entries, $_GET["node_id"]);
		$this->tpl->setLeftNavContent($explorer->getHTML());
		$entry_gui = new ilKSDocumentationEntryGUI($this,$explorer->getCurrentOpenedNode(), $entries);
		$this->tpl->setContent($toolbar->getHTML().$entry_gui->renderEntry());
	}

	/**
	 * @return Crawler\Entry\ComponentEntries
	 * @throws Crawler\Exception\CrawlerException
	 */
	protected function parseEntries(){
		$crawler = new Crawler\FactoriesCrawler();
		$entries = $crawler->crawlFactory(self::ROOT_FACTORY_PATH);
		file_put_contents(self::$DATA_PATH, json_encode($entries));
		ilUtil::sendSuccess($this->lng->txt("entries_reloaded"),true);
		return $entries;
	}

	/**
	 * @return Crawler\Entry\ComponentEntries
	 */
	protected function readEntries(){
		$entries_array = json_decode(file_get_contents(self::$DATA_PATH),true);

		$entries = new Crawler\Entry\ComponentEntries();
		foreach($entries_array as $entry_array){
			$entry = new Crawler\Entry\ComponentEntry($entry_array);
			$entries->addEntry($entry);
		}

		return $entries;
	}
}