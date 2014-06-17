<?php


if( !isset($ilAuth) ) {
    // switch context to something without authentication
    include_once "Services/Context/classes/class.ilContext.php";
    ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);

    require_once("Services/Init/classes/class.ilInitialisation.php");
    ilInitialisation::initILIAS();
}



$_global_template_path = 'Customizing/global/skin/genv/static';

$tpl_file_general = $_global_template_path .'/static_general.html';
$tpl_file_loggedin = $_global_template_path .'/static_loggedin.html';
$tpl_file_loggedout = $_global_template_path .'/static_loggedout.html';

$cssfile = $_global_template_path .'/static_pages.css';
$cssfile_loggedout = $_global_template_path .'/static_pages_loggedout.css';


$ctpl_file = $_global_template_path .'/impressum.html';
if (isset($_REQUEST['tpl'])){
    $ctpl_file = $_global_template_path .'/' .$_REQUEST['tpl'] .'.html';
}



/*
    --------------------------------------------------------------------
    generate contents
*/
$content = '';

$content_buffer = array(
    new ilTemplate($tpl_file_general, 0, 0, "Customizing/global/skin/genv")
);

$css_buffer = array(
    $cssfile
);


if($ilAuth->getAuth()){
/*
    require_once("Services/GEV/Desktop/classes/class.gevMainMenuGUI.php");
    $mainMenu = new gevMainMenuGUI("_top");
    $content .= $mainMenu->renderMainMenuListEntries('', false);
*/    

    $ctpl = new ilTemplate($tpl_file_loggedin, 0, 0, "Customizing/global/skin/genv");
    array_push($content_buffer, $ctpl);

} else {
    array_push($css_buffer, $cssfile_loggedout);

    $ctpl = new ilTemplate($tpl_file_loggedout, 0, 0, "Customizing/global/skin/genv");
    array_push($content_buffer, $ctpl);
}


// content by request
$ctpl = new ilTemplate($ctpl_file, 0, 0, "Customizing/global/skin/genv");
array_push($content_buffer, $ctpl);



/*
    --------------------------------------------------------------------
    render
*/


$tpl->getStandardTemplate();

foreach ($css_buffer as $cssfile) {
    $tpl->addCss($cssfile);
}

$cbuffer = array();
foreach ($content_buffer as $content_template) {
    array_push($cbuffer, $content_template->get());
}

$content .= implode('', $cbuffer);
$tpl->setContent($content);
$tpl->show();


/*
function _getFilesFromTemplateFolder(){
    global $_global_template_path;
    $handle = opendir($_global_template_path);
    if (! $handle) {
        die('cannot read teplate-dir');
    }
    $ret = array();

    while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 4) == 'tpl.' &&
            substr($entry, -5) == '.html')
            {

            $entry_name = substr($entry, 4);
            $entry_name = substr($entry_name, 0, -5);
            array_push($ret, $entry_name);
        }
    }

    closedir($handle);
    return $ret;
}


function _getTabs(){
    $buffer  = '';
    $tpl = '<a class="previewtab" href="./templates_overview.php?tpl=%s">%s</a>&nbsp;';
    $entries = _getFilesFromTemplateFolder();
    foreach ( $entries as $entry) {
        $buffer .= sprintf($tpl, $entry, $entry);
    }

    return $buffer;


}
*/

?>
