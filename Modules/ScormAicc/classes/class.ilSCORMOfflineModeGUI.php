<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class ilSCORMOfflineModeGUI
*
* GUI class for scorm offline player connection
*
* @author Stefan Schneider <schneider@hrz.uni-marburg.de>
* @version $Id: class.ilSCORMOfflineModeGUI.php  $
*
*
*/
class ilSCORMOfflineModeGUI
{
    public $refId;
    public $lmId;
    public $clientIdSop;
    public $offlineMode;
    public $offline_mode;
    public $lm_frm_url;
    public $sop_frm_url;
    public $sopcache_url;
    public $lmcache_url;
    public $tracking_url;
    public $vers = "v1";
    
    public function __construct($type)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        include_once "./Modules/ScormAicc/classes/class.ilSCORMOfflineMode.php";
        $this->ilias = $ilias;
        $this->tpl = $tpl;
        $lng->loadLanguageModule("sop");
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, "ref_id");
        $this->offlineMode = new ilSCORMOfflineMode();
        $this->offline_mode = $this->offlineMode->getOfflineMode();
    }
    
    public function executeCommand()
    {
        global $DIC;
        $log = $DIC['log'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->refId = $_GET["ref_id"];
        $this->lmId = ilObject::_lookupObjectId($this->refId);
        $this->clientIdSop = $this->offlineMode->getClientIdSop();
        $cmd = $ilCtrl->getCmd();
        $this->sop_frm_url = $this->offlineMode->cmd_url . 'offlineMode_sop';
        $this->sopcache_url = $this->offlineMode->cmd_url . 'offlineMode_sopcache';
        $this->lm_frm_url = $this->offlineMode->lm_cmd_url . 'offlineMode_il2sop';
        $this->lmcache_url = $this->offlineMode->lm_cmd_url . 'offlineMode_lmcache';
        $this->tracking_url = $this->offlineMode->lm_cmd_url . 'offlineMode_tracking2sop';
        
        switch ($cmd) {
            case 'offlineMode_sop':
                $log->write("offlineMode_sop");
                $sop_cache = new ilTemplate('tpl.cache.html', true, true, 'Modules/ScormAicc');
                //$sop_cache->setVariable("APPCACHE_URL",$ilCtrl->getLinkTarget($this,"offlineMode_sopcache")."&client_id=" . CLIENT_ID);
                $sop_cache->setVariable("APPCACHE_URL", $this->sopcache_url);
                $sop_cache->setVariable("CACHE_TITLE", "SOP Cache Page");
                $this->sendHtml($sop_cache->get());
                break;
                
            case 'offlineMode_sopcache':
                $log->write("offlineMode_manifest");
                $sop_manifest = new ilTemplate('tpl.sop.appcache', true, true, 'Modules/ScormAicc');
                $entries = $this->offlineMode->getSopManifestEntries();
                $sop_manifest->setVariable("CACHE_ENTRIES", $entries);
                $sop_manifest->setVariable("VERSION", $this->vers);
                $this->sendManifest($sop_manifest->get());
                break;
                
            case 'offlineMode_il2sop':
                $log->write("offlineMode_il2sop");
                $lm_cache = new ilTemplate('tpl.cache.html', true, true, 'Modules/ScormAicc');
                //$lm_cache->setVariable("APPCACHE_URL",$ilCtrl->getLinkTarget($this,"offlineMode_lmcache")."&client_id=" . CLIENT_ID);
                $lm_cache->setVariable("APPCACHE_URL", $this->lmcache_url);
                $lm_cache->setVariable("CACHE_TITLE", "LM Cache Page");
                $this->sendHtml($lm_cache->get());
                break;
                
            case 'offlineMode_lmcache':
                $log->write("offlineMode_lmcache");
                $lm_manifest = new ilTemplate('tpl.lm.appcache', true, true, 'Modules/ScormAicc');
                $entries = "";
                if ($_COOKIE['purgeCache'] == 1) {
                    $log->write("purgeCache:" . $_COOKIE['purgeCache']);
                } else {
                    $entries = $this->offlineMode->getLmManifestEntries();
                }
                $lm_manifest->setVariable("CACHE_ENTRIES", $entries);
                $lm_manifest->setVariable("VERSION", $this->vers);
                $this->sendManifest($lm_manifest->get());
                break;
            
            case 'offlineMode_tracking2sop':
                $this->offlineMode->tracking2sop();
                break;
                
            case 'offlineMode_il2sopOk':
                $this->offlineMode->setOfflineMode("offline");
                $this->view($this->offlineMode->getOfflineMode(), $cmd);
                break;

            case 'offlineMode_sop2ilpush':
                $this->offlineMode->sop2il();
                break;

            case 'offlineMode_sop2ilOk':
                $this->offlineMode->setOfflineMode("online");
                ilUtil::sendSuccess($this->lng->txt('sop_msg_push_tracking_ok'), true);
                ilUtil::redirect($this->offlineMode->lm_info_url);
                //$this->view($this->offlineMode->getOfflineMode(),$cmd);
                break;
                
            case 'offlineMode_player12':
                $log->write("offlineMode_player12");
                $player12 = new ilTemplate('tpl.player12.html', false, false, 'Modules/ScormAicc');
                $player12->setVariable("SOP_TITLE", "ILIAS SCORM 1.2 Offline Player"); // ToDo: Language Support
                $js_data = file_get_contents("./Modules/ScormAicc/scripts/basisAPI.js");
                $js_data .= file_get_contents("./Modules/ScormAicc/scripts/SCORM1_2standard.js");
                $player12->setVariable("SOP_SCRIPT", $js_data);
                $this->sendHtml($player12->get());
                break;
                
            case 'offlineMode_player2004':
                $log->write("offlineMode_player2004");

                // language strings
                $langstrings['btnStart'] = $lng->txt('scplayer_start');
                $langstrings['btnExit'] = $lng->txt('scplayer_exit');
                $langstrings['btnExitAll'] = $lng->txt('scplayer_exitall');
                $langstrings['btnSuspendAll'] = $lng->txt('scplayer_suspendall');
                $langstrings['btnPrevious'] = $lng->txt('scplayer_previous');
                $langstrings['btnContinue'] = $lng->txt('scplayer_continue');
                $langstrings['btnhidetree'] = $lng->txt('scplayer_hidetree');
                $langstrings['btnshowtree'] = $lng->txt('scplayer_showtree');
                $langstrings['linkexpandTree'] = $lng->txt('scplayer_expandtree');
                $langstrings['linkcollapseTree'] = $lng->txt('scplayer_collapsetree');
                $langstrings['contCreditOff'] = $lng->txt('cont_credit_off');
                // if ($this->slm->getAutoReviewChar() == "s") {
                    // $langstrings['contCreditOff']=$lng->txt('cont_sc_score_was_higher_message');
                // }
                // $config['langstrings'] = $langstrings;

                //template variables
                $player2004 = new ilTemplate('tpl.player2004.html', true, true, 'Modules/ScormAicc');

                include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
                $player2004->setVariable("JS_FILE", iljQueryUtil::getLocaljQueryPath());
                
                $player2004->setVariable('JSON_LANGSTRINGS', json_encode($langstrings));
                $player2004->setVariable('TREE_JS', "./Modules/Scorm2004/scripts/ilNestedList.js");
                $player2004->setVariable($langstrings);
                $player2004->setVariable("DOC_TITLE", "ILIAS SCORM 2004 Offline Player");
                $player2004->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
                list($tsfrac, $tsint) = explode(' ', microtime());
                $player2004->setVariable('TIMESTAMP', sprintf('%d%03d', $tsint, 1000 * (float) $tsfrac));
                $player2004->setVariable('BASE_DIR', './Modules/Scorm2004/');

                include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
                $player2004->setVariable('INLINE_CSS', ilSCORM13Player::getInlineCss());

                include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
                $this->tpl->setVariable("JS_FILE", iljQueryUtil::getLocaljQueryPath());

                $player2004->setVariable('JS_SCRIPTS', './Modules/Scorm2004/scripts/buildrte/rte.js');

                //disable top menu
                // if ($this->slm->getNoMenu()=="y") {
                    // $this->tpl->setVariable("VAL_DISPLAY", "style=\"display:none;\"");
                // } else {
                    // $this->tpl->setVariable("VAL_DISPLAY", "");
                // }
                
                $this->sendHtml($player2004->get());
                break;
                
            case 'offlineMode_som':
                $log->write("offlineMode_som");
                $som = new ilTemplate('tpl.som.html', true, true, 'Modules/ScormAicc');
                $som->setVariable("SOM_TITLE", "ILIAS SCORM Offline Manager"); // ToDo: Language Support
                $som->setVariable("PLAYER12_URL", $this->offlineMode->player12_url);
                $som->setVariable("PLAYER2004_URL", $this->offlineMode->player2004_url);
                $this->sendHtml($som->get());
                break;
                
            default:
                $this->view($this->offlineMode->getOfflineMode(), $cmd);
                break;
        }
    }
    
    public function view($offline_mode, $cmd)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $this->setOfflineModeTabs($offline_mode);
        $tpl->addJavascript('./Modules/ScormAicc/scripts/ilsop.js');
        $tpl->addJavascript('./libs/bower/bower_components/pouchdb/dist/pouchdb.min.js');
        $tpl->addCss('./Modules/ScormAicc/templates/sop/sop.css', "screen");
        $tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_offline_mode.html", "Modules/ScormAicc");
        $tpl->setCurrentBlock('offline_content');
        $tpl->setVariable("Command", $cmd);
        $tpl->setVariable("CHECK_SYSTEM_REQUIREMENTS", $this->lng->txt('sop_check_system_requirements'));
        $tpl->setVariable("SOP_SYSTEM_CHECK_HTTPS", $this->lng->txt('sop_system_check_https'));
        $tpl->setVariable("SOP_SYSTEM_CHECK_ERROR", $this->lng->txt('sop_system_check_error'));
        $tpl->setVariable("LM_NOT_EXISTS", $this->lng->txt('sop_lm_not_exists'));
        $tpl->setVariable("EXPORT", $this->lng->txt('sop_export'));
        $tpl->setVariable("DESC_EXPORT", $this->lng->txt('sop_desc_export'));
        $tpl->setVariable("TEXT_START_OFFLINE", $this->lng->txt('sop_text_start_offline'));
        $tpl->setVariable("START_SOP", $this->lng->txt('sop_start_sop')); // ToDo: lng files
        $tpl->setVariable("TEXT_START_SOM", $this->lng->txt('sop_text_start_som'));
        $tpl->setVariable("START_SOM", $this->lng->txt('sop_start_som'));
        $tpl->setVariable("TEXT_PUSH_TRACKING", $this->lng->txt('sop_text_push_tracking'));
        $tpl->setVariable("PUSH_TRACKING", $this->lng->txt('sop_push_tracking'));
        $tpl->setVariable("CLIENT_ID", CLIENT_ID);
        $tpl->setVariable("CLIENT_ID_SOP", $this->clientIdSop);
        $tpl->setVariable("REF_ID", $_GET['ref_id']);
        $tpl->setVariable("LM_ID", $this->lmId);
        $tpl->setVariable("OFFLINE_MODE", $offline_mode);
        $tpl->setVariable("PURGE_CACHE", $this->lng->txt('sop_purge_cache')); // ToDo
        $tpl->setVariable("CMD_URL", $this->cmd_url);
        $tpl->setVariable("LM_CMD_URL", $this->offlineMode->lm_cmd_url);
        $tpl->setVariable("SOP_FRM_URL", $this->sop_frm_url);
        $tpl->setVariable("LM_FRM_URL", $this->lm_frm_url);
        $tpl->setVariable("PLAYER12_URL", $this->offlineMode->player12_url);
        $tpl->setVariable("PLAYER2004_URL", $this->offlineMode->player2004_url);
        $tpl->setVariable("SOM_URL", $this->offlineMode->som_url);
        $tpl->setVariable("TRACKING_URL", $this->tracking_url);
        $tpl->parseCurrentBlock();
        $tpl->show();
    }
    
    public function setOfflineModeTabs($offline_mode)
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilLocator = $DIC['ilLocator'];
        $tpl = $DIC['tpl'];
        $log = $DIC['log'];
        $icon = ($offline_mode == "online") ? "icon_sahs.svg" : "icon_sahs_offline.svg";
        $tabTitle = $this->lng->txt("offline_mode");
        $a_active = $offline_mode; //workaround
        $thisurl = $this->ctrl->getLinkTarget($this, $a_active);
        $ilTabs->addTab($a_active, $tabTitle, $thisurl);
        $ilTabs->activateTab($a_active);
        $tpl->getStandardTemplate();
        $tpl->setTitle(ilObject::_lookupTitle($this->lmId));
        $tpl->setTitleIcon(ilUtil::getImagePath($icon));
        $ilLocator->addRepositoryItems();
        $ilLocator->addItem(ilObject::_lookupTitle($this->lmId), $thisurl);
        $tpl->setLocator();
    }
    
    public function sendHtml($html_str)
    {
        header('Content-Type: text/html');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
        print $html_str;
    }
    
    public function sendManifest($manifest_str)
    {
        header('Content-Type: text/cache-manifest');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
        print $manifest_str;
    }
}
