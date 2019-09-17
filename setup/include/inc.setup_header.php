<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* header include for ilias setup
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
* @package	ilias-setup
*/

// remove notices from error reporting
use ILIAS\GlobalScreen\Services;

error_reporting((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED);

require_once __DIR__."/../../libs/composer/vendor/autoload.php";

$DIC = new \ILIAS\DI\Container();

define("DEBUG",false);

//require_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
require_once "./setup/classes/class.ilTemplate.php";	// modified class. needs to be merged with base template class
require_once "./setup/classes/class.ilLanguage.php";	// modified class. needs to be merged with base language class 
require_once "./Services/Logging/classes/class.ilLog.php";
require_once "./Services/Authentication/classes/class.ilSession.php";
require_once "./Services/Utilities/classes/class.ilUtil.php";
require_once "./Services/Init/classes/class.ilIniFile.php";
require_once "./setup/classes/class.ilSetupGUI.php";
require_once "./setup/classes/class.Session.php";
require_once "./setup/classes/class.ilClientList.php";
require_once "./setup/classes/class.ilClient.php";
require_once "./Services/FileSystem/classes/class.ilFile.php";
require_once "./setup/classes/class.ilCtrlStructureReader.php";
require_once "./Services/Xml/classes/class.ilSaxParser.php";
require_once "./include/inc.ilias_version.php";
include_once './Services/Logging/classes/public/class.ilLogLevel.php';

// set ilias pathes
if($_SERVER['HTTPS'] == 'on')
{
	define ("ILIAS_HTTP_PATH",substr("https://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"]),0,-6));	
}
else
{
	define ("ILIAS_HTTP_PATH",substr("http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"]),0,-6));
}


// PHP is running in CGI mode?
if (isset($_SERVER["REDIRECT_STATUS"]) && !isset($_SERVER["FCGI_ROLE"]))
{
	if ($_SERVER["PATH_TRANSLATED"] != "")
	{
		define ("ILIAS_ABSOLUTE_PATH",substr(dirname($_SERVER["PATH_TRANSLATED"]),0,-6));
	}
	else
	{
		define ("ILIAS_ABSOLUTE_PATH",substr(dirname($_SERVER["SCRIPT_FILENAME"]),0,-6));
	}
}
else if ($_SERVER["SCRIPT_FILENAME"] != "")
{
	define ("ILIAS_ABSOLUTE_PATH",substr(dirname($_SERVER["SCRIPT_FILENAME"]),0,-6));
}
else
{
	// included this due to http://education2news.blogspot.com.es/2012/06/installing-ilias-424.html
	define ('ILIAS_ABSOLUTE_PATH',str_replace("/setup/include", "", dirname(__FILE__)));
}

// set default timezone 
include_once './Services/Calendar/classes/class.ilTimeZone.php';
include_once './Services/Init/classes/class.ilIniFile.php';
$ini = new ilIniFile(ILIAS_ABSOLUTE_PATH.'/ilias.ini.php');
$ini->read();
$DIC["ini"] = function($c) { return $GLOBALS["ini"]; };

$tz = ilTimeZone::initDefaultTimeZone($ini);
define('IL_TIMEZONE',$tz);
$DIC["tz"] = function($c) { return $GLOBALS["tz"]; };

define ("TPLPATH","./templates/blueshadow");

// init session
$sess = new Session();
$DIC["sess"] = function($c) { return $GLOBALS["sess"]; };

$lang = (isset($_GET["lang"])) ? $_GET["lang"] : $_SESSION["lang"];

$_SESSION["lang"] = $lang;

// init languages
$lng = new ilLanguage($lang);
$DIC["lng"] = function($c) { return $GLOBALS["lng"]; };

include_once './Services/Logging/classes/class.ilLoggingSetupSettings.php';
$logging_settings = new ilLoggingSetupSettings();
$logging_settings->init();

include_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
$loggerFactory = ilLoggerFactory::newInstance($logging_settings);
$log = $loggerFactory->getComponentLogger('setup');

$ilLog = $log;
$DIC["ilLog"] = function($c) { return $GLOBALS["ilLog"]; };
$DIC["ilLoggerFactory"] = function($c) use ($loggerFactory) { return $loggerFactory; };

// init template - in the main program please use ILIAS Template class
// instantiate main template
//$tpl = new ilTemplate("./setup/templates");
//$tpl->loadTemplatefile("tpl.main.html", true, true);
$tpl = new ilTemplate("tpl.main.html", true, true, "setup");
$DIC["tpl"] = function($c) { return $GLOBALS["tpl"]; };

// make instance of structure reader
$ilCtrlStructureReader = new ilCtrlStructureReader();
$DIC["ilCtrlStructureReader"] = function($c) { return $GLOBALS["ilCtrlStructureReader"]; };

require_once "./Services/Utilities/classes/class.ilBenchmark.php";
$ilBench = new ilBenchmark();
$GLOBALS['ilBench'] = $ilBench;
$DIC["ilBench"] = function($c) { return $GLOBALS["ilBench"]; };

include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
include_once("./Services/Database/classes/class.ilMySQLAbstraction.php");
include_once("./Services/Database/classes/class.ilDBGenerator.php");

// HTTP Services
$DIC['http.request_factory'] = function ($c) {
	return new \ILIAS\HTTP\Request\RequestFactoryImpl();
};

$DIC['http.response_factory'] = function ($c) {
	return new \ILIAS\HTTP\Response\ResponseFactoryImpl();
};

$DIC['http.cookie_jar_factory'] = function ($c) {
	return new \ILIAS\HTTP\Cookies\CookieJarFactoryImpl();
};

$DIC['http.response_sender_strategy'] = function ($c) {
	return new \ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy();
};
$DIC["http"] = function ($c) {
	return new \ILIAS\DI\HTTPServices(
		$c['http.response_sender_strategy'],
		$c['http.cookie_jar_factory'],
		$c['http.request_factory'],
		$c['http.response_factory']
	);
};

// UI Services
$c = $DIC;
$c["ui.factory"] = function ($c) {
	return new ILIAS\UI\Implementation\Factory(
		$c["ui.factory.counter"],
		$c["ui.factory.glyph"],
		$c["ui.factory.button"],
		$c["ui.factory.listing"],
		$c["ui.factory.image"],
		$c["ui.factory.panel"],
		$c["ui.factory.modal"],
		$c["ui.factory.dropzone"],
		$c["ui.factory.popover"],
		$c["ui.factory.divider"],
		$c["ui.factory.link"],
		$c["ui.factory.dropdown"],
		$c["ui.factory.item"],
		$c["ui.factory.icon"],
		$c["ui.factory.viewcontrol"],
		$c["ui.factory.chart"],
		$c["ui.factory.input"],
		$c["ui.factory.table"],
		$c["ui.factory.messagebox"],
		$c["ui.factory.card"]
	);
};
$c["ui.signal_generator"] = function($c) {
	return new ILIAS\UI\Implementation\Component\SignalGenerator;
};
$c["ui.factory.counter"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Counter\Factory();
};
$c["ui.factory.glyph"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Glyph\Factory();
};
$c["ui.factory.button"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Button\Factory();
};
$c["ui.factory.listing"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Listing\Factory();
};
$c["ui.factory.image"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Image\Factory();
};
$c["ui.factory.panel"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Panel\Factory($c["ui.factory.panel.listing"]);
};
$c["ui.factory.modal"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Modal\Factory($c["ui.signal_generator"]);
};
$c["ui.factory.dropzone"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Dropzone\Factory($c["ui.factory.dropzone.file"]);
};
$c["ui.factory.popover"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Popover\Factory($c["ui.signal_generator"]);
};
$c["ui.factory.divider"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Divider\Factory();
};
$c["ui.factory.link"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Link\Factory();
};
$c["ui.factory.dropdown"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Dropdown\Factory();
};
$c["ui.factory.item"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Item\Factory();
};
$c["ui.factory.icon"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Icon\Factory();
};
$c["ui.factory.viewcontrol"] = function($c) {
	return new ILIAS\UI\Implementation\Component\ViewControl\Factory($c["ui.signal_generator"]);
};
$c["ui.factory.chart"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Chart\Factory($c["ui.factory.progressmeter"]);
};
$c["ui.factory.input"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Input\Factory(
		$c["ui.signal_generator"],
		$c["ui.factory.input.field"],
		$c["ui.factory.input.container"]
	);
};
$c["ui.factory.table"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Table\Factory($c["ui.signal_generator"]);
};
$c["ui.factory.messagebox"] = function($c) {
	return new ILIAS\UI\Implementation\Component\MessageBox\Factory();
};
$c["ui.factory.card"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Card\Factory();
};
$c["ui.factory.progressmeter"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory();
};
$c["ui.factory.dropzone.file"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Dropzone\File\Factory();
};
$c["ui.factory.input.field"] = function($c) {
	$data_factory = new ILIAS\Data\Factory();
	$validation_factory = new ILIAS\Validation\Factory($data_factory, $c["lng"]);
	$transformation_factory = new ILIAS\Transformation\Factory();
	return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
		$c["ui.signal_generator"],
		$data_factory,
		$validation_factory,
		$transformation_factory
	);
};
$c["ui.factory.input.container"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Input\Container\Factory(
		$c["ui.factory.input.container.form"]
	);
};
$c["ui.factory.input.container.form"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Input\Container\Form\Factory(
		$c["ui.factory.input.field"]
	);
};
$c["ui.factory.panel.listing"] = function($c) {
	return new ILIAS\UI\Implementation\Component\Panel\Listing\Factory();
};

$c["ui.renderer"] = function($c) {
	return new ILIAS\UI\Implementation\DefaultRenderer
	( $c["ui.component_renderer_loader"]
	);
};
$c["ui.component_renderer_loader"] = function($c) {
	return new ILIAS\UI\Implementation\Render\LoaderCachingWrapper
	( new ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper
		( $c["ui.resource_registry"]
			, new ILIAS\UI\Implementation\Render\FSLoader
			( new ILIAS\UI\Implementation\Render\DefaultRendererFactory
			($c["ui.factory"]
				, $c["ui.template_factory"]
				, $c["lng"]
				, $c["ui.javascript_binding"]
			),
				new ILIAS\UI\Implementation\Component\Glyph\GlyphRendererFactory
				($c["ui.factory"]
					, $c["ui.template_factory"]
					, $c["lng"]
					, $c["ui.javascript_binding"]
				)
			)
		)
	);
};
$c["ui.template_factory"] = function($c) {
	return new ILIAS\UI\Implementation\Render\ilTemplateWrapperFactory
	( $c["tpl"]
	);
};
$c["ui.resource_registry"] = function($c) {
	return new ILIAS\UI\Implementation\Render\ilResourceRegistry($c["tpl"]);
};
$c["ui.javascript_binding"] = function($c) {
	return new ILIAS\UI\Implementation\Render\ilJavaScriptBinding($c["tpl"]);
};


// Other Services
$DIC['ilCtrl'] = new ilCtrl();

$DIC["ilIliasIniFile"] = function($c) { return $GLOBALS["ilIliasIniFile"]; };

$DIC["ilClientIniFile"] = function($c) { return $GLOBALS["ilClientIniFile"]; };

$DIC["global_screen"] = function () use ($DIC)  {
	return new Services(new ilGSProviderFactory($DIC));
};

?>
