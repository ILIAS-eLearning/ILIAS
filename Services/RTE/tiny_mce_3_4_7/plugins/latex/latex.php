<?php

$steps = 0;
while(!file_exists('ilias.ini.php'))
{
	chdir('..');
	++$steps;
}

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var $ilIliasIniFile ilIniFile
 * @var $ilUser         ilObjUser
 */
global $ilIliasIniFile, $ilUser;

$tpl = new ilTemplate(dirname(__FILE__) . '/tpl.latex.html', true, true);

$tpl->resetJavascript();

require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
$jquery_path = iljQueryUtil::getLocaljQueryPath();
if(strpos($jquery_path, './') === 0)
{
	$jquery_path = substr($jquery_path, 2);
}
else if(strpos($jquery_path, '.') === 0)
{
	$jquery_path = substr($jquery_path, 1);
}

$mathJaxSetting = new ilSetting('MathJax');
if($mathJaxSetting->get('enable'))
{
	$tpl->addJavaScript($mathJaxSetting->get('path_to_mathjax'));

	$tpl->setCurrentBlock('js_on_change_math_jax');
	$tpl->touchBlock('js_on_change_math_jax');
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock('delimiter_latex');
	$tpl->setVariable('DELIMITER', (int) $mathJaxSetting->get('limiter'));
	$tpl->parseCurrentBlock();
}
else if(strlen($ilIliasIniFile->readVariable('tools', 'latex')))
{
	$tpl->setCurrentBlock('js_on_change_latex');
	$tpl->setVariable('LATEX_URL', $ilIliasIniFile->readVariable('tools', 'latex'));
	$tpl->parseCurrentBlock();
}

$tpl->addJavaScript(str_repeat('../', $steps) . $jquery_path, true, 1);
$tpl->fillJavaScriptFiles(true);
$tpl->show('DEFAULT', false, true);