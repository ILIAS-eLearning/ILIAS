<?php

$steps = 0;
while (!file_exists('ilias.ini.php')) {
    chdir('..');
    ++$steps;
}

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var $ilIliasIniFile ilIniFile
 * @var $ilUser         ilObjUser
 */
global $DIC;
$ilIliasIniFile = $DIC['ilIliasIniFile'];
$ilUser = $DIC['ilUser'];

global $DIC;

$htdocs = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
$weburl = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
if (defined('ILIAS_HTTP_PATH')) {
    $weburl = substr(ILIAS_HTTP_PATH, 0, strrpos(ILIAS_HTTP_PATH, '/node_modules')) . '/';
}
$iliasHttpPath = $weburl;

if ($DIC->http()->request()->getMethod() == "GET" && isset($DIC->http()->request()->getQueryParams()['tex'])) {
    $text = ilUtil::insertLatexImages(
        '[tex]' .
        ilUtil::stripSlashes((string) $DIC->http()->request()->getQueryParams()['tex'] ?? '') .
        '[/tex]'
    );
    $responseStream = \ILIAS\Filesystem\Stream\Streams::ofString($text);
    $DIC->http()->saveResponse($DIC->http()->response()->withBody($responseStream));
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

$tpl = new ilRTEGlobalTemplate(dirname(__FILE__) . '/tpl.latex.html', true, true);
$tpl->setVariable("ILIAS_INST_PATH", $iliasHttpPath);

$tpl->resetJavascript();

require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
$jquery_path = iljQueryUtil::getLocaljQueryPath();
if (strpos($jquery_path, './') === 0) {
    $jquery_path = substr($jquery_path, 2);
} elseif (strpos($jquery_path, '.') === 0) {
    $jquery_path = substr($jquery_path, 1);
}

$mathJaxSetting = new ilSetting('MathJax');
if ($mathJaxSetting->get('enable_server') && $mathJaxSetting->get('server_for_browser')) {
    $tpl->setCurrentBlock('js_on_change_server_math_jax');
    $tpl->touchBlock('js_on_change_server_math_jax');
    $tpl->parseCurrentBlock();
} elseif ($mathJaxSetting->get('enable')) {
    $pathToMathJax = $mathJaxSetting->get('path_to_mathjax');
    if (
        false === strpos($pathToMathJax, '//') &&
        false === strpos($pathToMathJax, 'https://') &&
        false === strpos($pathToMathJax, 'http://')
    ) {
        $pathToMathJax = str_repeat('../', $steps) . $pathToMathJax;
    }
    $tpl->addJavaScript($pathToMathJax);

    $tpl->setCurrentBlock('js_on_change_math_jax');
    $tpl->setVariable('DELIMITER', (int) $mathJaxSetting->get('limiter'));
    $tpl->parseCurrentBlock();
} elseif (strlen($ilIliasIniFile->readVariable('tools', 'latex'))) {
    $tpl->setCurrentBlock('js_on_change_latex');
    $tpl->setVariable('LATEX_URL', $ilIliasIniFile->readVariable('tools', 'latex'));
    $tpl->parseCurrentBlock();
}

$tpl->addJavaScript(str_repeat('../', $steps) . $jquery_path, true, 1);
$tpl->fillJavaScriptFiles(true);
$tpl->printToStdout('DEFAULT', false, true);
