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

    /**
     * @var bool
     */
    protected $is_read_only = false;

    const ROOT_FACTORY_PATH = "./Services/Style/System/data/abstractDataFactory.php";
    const DATA_DIRECTORY = "./Services/Style/System/data";
    const DATA_FILE = "data.json";
    public static $DATA_PATH;

    /**
     * ilSystemStyleDocumentationGUI constructor.
     * @param bool|false $read_only
     */
    public function __construct($read_only = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->setIsReadOnly($read_only);

        self::$DATA_PATH = self::DATA_DIRECTORY . "/" . self::DATA_FILE;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'parseEntries':
                $this->$cmd();
                $this->show();
                break;
            default:
                if ($this->is_read_only) {
                    $this->resetForReadOnly();
                }
                $this->addGotoLink();
                $this->show();
                break;
        }
    }

    public function show()
    {
        $entries = $this->readEntries();
        $content = "";

        //The button to parse the entries from code should only be shown in DEVMODE. Other users do not need that.
        if (DEVMODE == 1 && !$this->isReadOnly()) {
            $toolbar = new ilToolbarGUI();
            $reload_btn = ilLinkButton::getInstance();
            $reload_btn->setCaption($this->lng->txt('refresh_entries'), false);
            if ($_GET["node_id"]) {
                $this->ctrl->saveParameter($this, "node_id");
            }
            $reload_btn->setUrl($this->ctrl->getLinkTarget($this, 'parseEntries'));
            $toolbar->addButtonInstance($reload_btn);
            $content .= $toolbar->getHTML();
        }

        $explorer = new ilKSDocumentationExplorerGUI($this, "entries", $entries, $_GET["node_id"]);
        $this->tpl->setLeftNavContent($explorer->getHTML());
        $entry_gui = new ilKSDocumentationEntryGUI($this, $explorer->getCurrentOpenedNode(), $entries);
        $content .= $entry_gui->renderEntry();

        $this->tpl->setContent($content);
    }

    protected function resetForReadOnly()
    {
        /**
         * @var ILIAS\DI\Container $DIC
         */
        global $DIC;

        $DIC->tabs()->clearTargets();

        /**
         * Since clearTargets also clears the help screen ids
         */
        $DIC->help()->setScreenIdComponent("sty");
        $DIC->help()->setScreenId("system_styles");

        $skin_id = $_GET["skin_id"];
        $style_id = $_GET["style_id"];

        $skin = ilSystemStyleSkinContainer::generateFromId($skin_id)->getSkin();
        $style = $skin->getStyle($style_id);

        $DIC["tpl"]->setTitle($DIC->language()->txt("documentation"));

        if ($style->isSubstyle()) {
            $DIC["tpl"]->setDescription(
                $this->lng->txt("ks_documentation_of_substyle")
                    . " '"
                    . $style->getName() . "' " .
                    $this->lng->txt("of_parent") . " '" . $skin->getStyle($style->getSubstyleOf())->getName() . "' " .
                    $this->lng->txt("from_skin") . " " . $skin->getName()
            );
        } else {
            $DIC["tpl"]->setDescription(
                $this->lng->txt("ks_documentation_of_style") . " '" . $style->getName() . "' " .
                    $this->lng->txt("from_skin") . " '" . $skin->getName() . "'"
            );
        }

        $DIC["ilLocator"]->clearItems();
        $DIC["tpl"]->setLocator();
    }

    protected function addGotoLink()
    {
        $this->tpl->setPermanentLink("stys", $_GET["ref_id"], "_" . $_GET["node_id"] . "_"
                . $_GET["skin_id"] . "_" . $_GET["style_id"]);
    }

    /**
     * @return Crawler\Entry\ComponentEntries
     * @throws Crawler\Exception\CrawlerException
     */
    protected function parseEntries()
    {
        $crawler = new Crawler\FactoriesCrawler();
        $entries = $crawler->crawlFactory(self::ROOT_FACTORY_PATH);
        file_put_contents(self::$DATA_PATH, json_encode($entries));
        ilUtil::sendSuccess($this->lng->txt("entries_reloaded"), true);
        return $entries;
    }

    /**
     * @return Crawler\Entry\ComponentEntries
     */
    protected function readEntries()
    {
        $entries_array = json_decode(file_get_contents(self::$DATA_PATH), true);

        $entries = new Crawler\Entry\ComponentEntries();
        foreach ($entries_array as $entry_array) {
            $entry = new Crawler\Entry\ComponentEntry($entry_array);
            $entries->addEntry($entry);
        }

        return $entries;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->is_read_only;
    }

    /**
     * @param bool $is_read_only
     */
    public function setIsReadOnly($is_read_only)
    {
        $this->is_read_only = $is_read_only;
    }
}
