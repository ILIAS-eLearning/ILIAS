<?PHP

include("./includes/inc.main.php");
include_once("./classes/class.User.php");
include_once("./classes/class.Language.php");

$usr = new User(1);
$lng = new Language($usr->lng);

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.admin.html", true, true);

$tplmain->setVariable("PAGETITLE","ILIAS - ".$lng->txt("administration"));
$tpl->setVariable("PAGEHEADLINE", $lng->txt("administration"));

//User-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("user")));
$tpl->parseCurrentBlock();

//Lesson-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("rights"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("lessons")));
$tpl->parseCurrentBlock();

//Basic-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("basic_data"));
$tpl->setVariable("LINK","admin_basicdata.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("languages"));
$tpl->setVariable("LINK","admin_languages.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("files_location"));
$tpl->setVariable("LINK","admin_files.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("database"));
$tpl->setVariable("LINK","admin_database.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("system")));
$tpl->parseCurrentBlock();



//main table
$tpl->touchBlock("adminrow");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>