<?PHP
/**
* admin languages
* utils for updating the database and optimize it etc.
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$lng->setSystemLanguage($ilias->ini->readVariable("language", "default"));
$lng->setUserLanguage($lng->lng);

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_languages.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");


if ($_POST["cmd"]=="generateall")
{
        $lng->generateLanguageFiles();
}

if ($_POST["action"] == "del")
{
        for ($i = 0; $i<count($marker); $i++)
        {
                if ($lng->deinstallLanguage($marker[$i]) == false)
                {
                        $msg = $lng->error;
                }
        }
}

if ($_POST["action"] == "install")
{
        for ($i = 0; $i<count($marker); $i++)
        {
                if ($lng->installLanguage($marker[$i]) == false)
                {
                        $msg = $lng->error;
                }
        }
}

if ($_GET["cmd"] == "install")
{
        if ($lng->installLanguage($_GET["id"]) == false)
        {
                $msg = $lng->error;
        }
}

if ($_GET["cmd"] == "del")
{
        if ($lng->deinstallLanguage($_GET["id"]) == false)
        {
                $msg = $lng->error;
        }
}

$tpl->setCurrentBlock("message");
$tpl->setVariable("MSG", $msg);
$tpl->parseCurrentBlock();

$langs = $lng->getAvailableLanguages();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./admin.php");
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

$tpl->touchBlock("btn_row");

$tpl->addBlockFile("BUTTONS2", "buttons2", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./adm_languages.php");
$tpl->setVariable("BTN_TXT", $lng->txt("refresh"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",".php");
$tpl->setVariable("BTN_TXT", $lng->txt("change"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",".php");
$tpl->setVariable("BTN_TXT", $lng->txt("check"));
$tpl->parseCurrentBlock();
$tpl->touchBlock("btn_row");


$tpl->setVariable("TXT_AVAILLANG", $lng->txt("available_languages"));
$tpl->setVariable("TXT_LANG", $lng->txt("language"));
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
$tpl->setVariable("TXT_LASTCHANGE", $lng->txt("last_change"));

foreach ($langs as $row)
{
        $i++;
        $tpl->setCurrentBlock("language_row");
        $tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
        $tpl->setVariable("ID", $row["id"]);
        $tpl->setVariable("LNG", $row["name"]);
        $tpl->setVariable("STATUS", $lng->txt($row["status"]));

        if ($row["status"] == "installed")
        {
            $link = "adm_languages.php?cmd=del&amp;id=".$row["id"];
        }

        if ($row["status"] == "not_installed")
        {
            $link = "adm_languages.php?cmd=install&amp;id=".$row["id"];
        }

        $tpl->setVariable("LINK", $link);

        if ($row["lastchange"] != "") {
                $tpl->setVariable("LASTCHANGE", $lng->fmtDateTime($row["lastchange"]));
        }
        $tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_GENERATE", $lng->txt("languages_generate_from_file"));
$tpl->setVariable("LANGMASTERFILE", "languages.txt");
$tpl->setVariable("TXT_SELECTED", $lng->txt("selected"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
$tpl->setVariable("FORMACTION", "adm_languages.php");

//add delete, reinstall feature
$tpl->setCurrentBlock("selaction");
$tpl->setVariable("VALUE", "del");
$tpl->setVariable("OPTION", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("selaction");
$tpl->setVariable("VALUE", "install");
$tpl->setVariable("OPTION", $lng->txt("install"));
$tpl->parseCurrentBlock();


$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_LANGUAGES", $lng->txt("languages"));
$tpl->parseCurrentBlock();

$tpl->show();

?>