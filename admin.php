<?PHP
/**
 * admin interface
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);
$tplmain->setVariable("TXT_PAGETITLE","ILIAS - ".$lng->txt("profile"));

$tpl = new Template("tpl.admin.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("administration"));

//User-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("user")));
$tpl->parseCurrentBlock();

//Lesson-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("rights"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("lessons")));
$tpl->parseCurrentBlock();

//Basic-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("basic_data"));
$tpl->setVariable("LINK","adm_basicdata.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", "_objects");
$tpl->setVariable("LINK","admindex.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("languages"));
$tpl->setVariable("LINK","adm_languages.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("files_location"));
$tpl->setVariable("LINK","adm_files.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("database"));
$tpl->setVariable("LINK","adm_database.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("system")));
$tpl->parseCurrentBlock();

//main table
$tpl->touchBlock("adminrow");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>