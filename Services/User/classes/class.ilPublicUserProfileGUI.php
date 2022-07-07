<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * GUI class for public user profile presentation.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPublicUserProfileGUI: ilObjPortfolioGUI
 */
class ilPublicUserProfileGUI implements ilCtrlBaseClassInterface
{
    private bool $offline = false;
    protected ilUserDefinedFields $user_defined_fields;
    protected \ILIAS\User\ProfileGUIRequest $profile_request;
    protected int $userid = 0;
    protected int $portfolioid = 0;
    protected string $backurl = "";
    protected array $additional = []; // Missing array type.
    protected bool $embedded = false;
    protected array $custom_prefs = []; // Missing array type.
    protected ilObjUser $current_user;
    protected \ilSetting $setting;

    public function __construct(int $a_user_id = 0)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->current_user = $DIC->user();

        $this->setting = $DIC["ilSetting"];

        $this->profile_request = new \ILIAS\User\ProfileGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        if ($a_user_id) {
            $this->setUserId($a_user_id);
        } else {
            $this->setUserId($this->profile_request->getUserId());
        }
        
        $ilCtrl->saveParameter($this, array("user_id","back_url", "user"));
        $back_url = $this->profile_request->getBackUrl();
        if ($back_url != "") {
            $this->setBackUrl($back_url);
        }
        
        $lng->loadLanguageModule("user");
    }
    
    public function setUserId(int $a_userid) : void
    {
        $this->userid = $a_userid;
    }

    public function getUserId() : int
    {
        return $this->userid;
    }

    /**
     * Set Additonal Information.
     */
    public function setAdditional(array $a_additional) : void // Missing array type.
    {
        $this->additional = $a_additional;
    }

    public function getAdditional() : array // Missing array type.
    {
        return $this->additional;
    }

    /**
     * Set Back Link URL.
     */
    public function setBackUrl(string $a_backurl) : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        // we only allow relative links
        $parts = parse_url($a_backurl);
        $host = $parts['host'] ?? '';
        if ($host !== '') {
            $a_backurl = "#";
        }
        $this->backurl = $a_backurl;
        $ilCtrl->setParameter($this, "back_url", rawurlencode($a_backurl));
    }

    public function getBackUrl() : string
    {
        return $this->backurl;
    }
        
    protected function handleBackUrl(bool $a_is_portfolio = false) : void
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];

        $back_url = $this->profile_request->getBackUrl();
        $back = ($this->getBackUrl() != "")
            ? $this->getBackUrl()
            : $back_url;
        
        if (!$back) {
            if ($DIC->user()->getId() != ANONYMOUS_USER_ID) {
                // #15984
                $back = 'ilias.php?baseClass=ilDashboardGUI';
            } else {
                $back = 'ilias.php?baseClass=ilRepositoryGUI';
            }
        }

        if (!$a_is_portfolio) {
            // #17838
            $ilTabs->clearTargets();
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $back
            );
        }
    }
    
    /**
     * Set custom preferences for public profile fields
     */
    public function setCustomPrefs(array $a_prefs) : void // Missing array type.
    {
        $this->custom_prefs = $a_prefs;
    }

    /**
     * Get user preference for public profile
     */
    protected function getPublicPref(ilObjUser $a_user, string $a_id) : string
    {
        if (!$this->custom_prefs) {
            return (string) $a_user->getPref($a_id);
        } else {
            return (string) $this->custom_prefs[$a_id];
        }
    }
    
    public function setEmbedded(bool $a_value, bool $a_offline = false) : void
    {
        $this->embedded = $a_value;
        $this->offline = $a_offline;
    }
    
    public function executeCommand() : string
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ret = "";
        if (!self::validateUser($this->getUserId())) {
            return "";
        }

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        $tpl->loadStandardTemplate();
        
        switch ($next_class) {
            case "ilobjportfoliogui":
                $portfolio_id = $this->getProfilePortfolio();
                if ($portfolio_id) {
                    $this->handleBackUrl(true);
                    $gui = new ilObjPortfolioGUI($portfolio_id); // #11876
                    $gui->setAdditional($this->getAdditional());
                    $gui->setPermaLink($this->getUserId(), "usr");
                    $ilCtrl->forwardCommand($gui);
                    break;
                }
                // no break
            case 'ilbuddysystemgui':
                $osd_id = $this->profile_request->getOsdId();
                if ($osd_id > 0) {
                    ilNotificationOSDHandler::removeNotification($osd_id);
                }
                $gui = new ilBuddySystemGUI();
                $ilCtrl->setReturn($this, 'view');
                $ilCtrl->forwardCommand($gui);
                break;
            default:
                $ret = $this->$cmd();
                $tpl->setContent($ret);
                break;
        }
            
        // only for direct links
        if (strtolower($this->profile_request->getBaseClass()) == "ilpublicuserprofilegui") {
            $tpl->printToStdout();
        }
        return (string) $ret;
    }
    
    /**
     * View. This one is called e.g. through the goto script
     */
    public function view() : string
    {
        return $this->getHTML();
    }

    protected function isProfilePublic() : bool
    {
        $setting = $this->setting;
        $user = new ilObjUser($this->getUserId());
        $current = $user->getPref("public_profile");
        // #17462 - see ilPersonalProfileGUI::initPublicProfileForm()
        if ($user->getPref("public_profile") == "g" && !$setting->get('enable_global_profiles')) {
            $current = "y";
        }
        return in_array($current, ["g", "y"]);
    }

    public function getHTML() : string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilSetting = $DIC['ilSetting'];

        if ($this->embedded) {
            return $this->getEmbeddable();
        }
                
        // #15438 - (currently) inactive user?
        $is_active = true;
        $user = new ilObjUser($this->getUserId());
        if (!$user->getActive() ||
            !$user->checkTimeLimit()) {
            $is_active = false;
        }
        
        if ($is_active && $this->getProfilePortfolio()) {
            $ilCtrl->redirectByClass("ilobjportfoliogui", "preview");
        } else {
            if (!$is_active) {
                ilUtil::redirect('ilias.php?baseClass=ilDashboardGUI');
            }
            
            // Check from Database if value
            // of public_profile = "y" show user infomation
            $user = new ilObjUser($this->getUserId());
            $current = $user->getPref("public_profile");
            // #17462 - see ilPersonalProfileGUI::initPublicProfileForm()
            if ($user->getPref("public_profile") == "g" && !$ilSetting->get('enable_global_profiles')) {
                $current = "y";
            }
            
            if ($current != "y" &&
                ($current != "g" || !$ilSetting->get('enable_global_profiles')) &&
                !$this->custom_prefs) {
                ilUtil::redirect('ilias.php?baseClass=ilDashboardGUI');
            }

            $this->renderTitle();
            return $this->getEmbeddable(true);
        }
        return "";
    }


    /**
     * get public profile html code
     * Used in Personal Profile (as preview) and Portfolio (as page block)
     */
    public function getEmbeddable(bool $a_add_goto = false) : string
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];
        $h = $v = "";

        // get user object
        if (!ilObject::_exists($this->getUserId())) {
            return "";
        }
        $user = new ilObjUser($this->getUserId());
        
        $tpl = new ilTemplate(
            "tpl.usr_public_profile.html",
            true,
            true,
            "Services/User"
        );


        $tpl->setVariable("ROWCOL1", "tblrow1");
        $tpl->setVariable("ROWCOL2", "tblrow2");

        if (!$this->offline && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $ref_url = str_replace("&amp;", "&", $this->getBackUrl());
            if (!$ref_url) {
                $ref_url = basename($_SERVER['REQUEST_URI']);
            }
            
            $tpl->setCurrentBlock("mail");
            $tpl->setVariable("TXT_MAIL", $lng->txt("send_mail"));
            $tpl->setVariable(
                'HREF_MAIL',
                ilMailFormCall::getLinkTarget(
                    $ref_url,
                    '',
                    array(),
                    array('type' => 'new', 'rcp_to' => $user->getLogin())
                )
            );
            $tpl->parseCurrentBlock();
        }


        // short version, fixes e.g. #27242
        if (!$this->isProfilePublic()) {
            $tpl->setVariable("TXT_NAME", $lng->txt("name"));
            $tpl->setVariable("FIRSTNAME", ilUserUtil::getNamePresentation($user->getId()));
            return $tpl->get();
        }

        $first_name = "";
        if ($this->getPublicPref($user, "public_title") == "y") {
            $first_name .= $user->getUTitle() . " ";
        }
        $first_name .= $user->getFirstname();

        if ($this->getPublicPref($user, "public_gender") == "y" && in_array($user->getGender(), ['m', 'f'])) {
            $sal = $lng->txt("salutation_" . $user->getGender()) . " ";
            $tpl->setVariable("SALUTATION", $sal);
        }

        $tpl->setVariable("TXT_NAME", $lng->txt("name"));
        $tpl->setVariable("FIRSTNAME", $first_name);
        $tpl->setVariable("LASTNAME", $user->getLastname());

        if ($user->getBirthday() &&
            $this->getPublicPref($user, "public_birthday") == "y") {
            // #17574
            $tpl->setCurrentBlock("bday_bl");
            $tpl->setVariable("TXT_BIRTHDAY", $lng->txt("birthday"));
            $tpl->setVariable("VAL_BIRTHDAY", ilDatePresentation::formatDate(new ilDate($user->getBirthday(), IL_CAL_DATE)));
            $tpl->parseCurrentBlock();
        }
        
        if (!$this->offline) {
            // vcard
            $tpl->setCurrentBlock("vcard");
            $tpl->setVariable("TXT_VCARD", $lng->txt("vcard"));
            $tpl->setVariable("TXT_DOWNLOAD_VCARD", $lng->txt("vcard_download"));
            $ilCtrl->setParameter($this, "user", $this->getUserId());
            $tpl->setVariable("HREF_VCARD", $ilCtrl->getLinkTarget($this, "deliverVCard"));
        }
        
        $webspace_dir = ilFileUtils::getWebspaceDir("user");
        $check_dir = ilFileUtils::getWebspaceDir();
        $random = new \ilRandom();
        $imagefile = $webspace_dir . "/usr_images/" . $user->getPref("profile_image") . "?dummy=" . $random->int(1, 999999);
        $check_file = $check_dir . "/usr_images/" . $user->getPref("profile_image");

        if (!is_file($check_file)) {
            $imagefile = $check_file =
                ilObjUser::_getPersonalPicturePath($user->getId(), "small", false, true);
        } else {
            if ($this->offline) {
                $imagefile = basename($imagefile);
            } else {
                $imagefile = ilWACSignedPath::signFile($imagefile . "?t=1");
            }
        }

        if ($this->getPublicPref($user, "public_upload") == "y" && $imagefile != "" &&
            ($ilUser->getId() != ANONYMOUS_USER_ID || $user->getPref("public_profile") == "g")) {
            //Getting the flexible path of image form ini file
            //$webspace_dir = ilUtil::getWebspaceDir("output");
            $tpl->setCurrentBlock("image");
            $tpl->setVariable("TXT_IMAGE", $lng->txt("image"));
            $tpl->setVariable("IMAGE_PATH", $imagefile);
            $tpl->setVariable("IMAGE_ALT", $lng->txt("personal_picture"));
            $tpl->parseCurrentBlock();
        }
        
        // address
        if ($this->getPublicPref($user, "public_street") == "y" ||
            $this->getPublicPref($user, "public_zipcode") == "y" ||
            $this->getPublicPref($user, "public_city") == "y" ||
            $this->getPublicPref($user, "public_country") == "y") {
            $address = array();
            $val_arr = array("getStreet" => "street",
                "getZipcode" => "zipcode",
                "getCity" => "city",
                "getCountry" => "country",
                "getSelectedCountry" => "sel_country");
            foreach ($val_arr as $key => $value) {
                // if value "y" show information
                if ($this->getPublicPref($user, "public_" . $value) == "y") {
                    $address_value = $user->$key();
                    
                    // only if set
                    if (trim($address_value) != "") {
                        switch ($value) {
                            case "street":
                                $address[0] = $address_value;
                                break;
                            
                            case "zipcode":
                            case "city":
                                $address[1] = ($address[1] ?? '') . $address_value;
                                break;
                            
                            case "sel_country":
                                $lng->loadLanguageModule("meta");
                                $address[2] = $lng->txt("meta_c_" . $address_value);
                                break;
                            
                            case "country":
                                $address[2] = $address_value;
                                break;
                        }
                    }
                }
            }
            if (count($address)) {
                $tpl->setCurrentBlock("address_line");
                foreach ($address as $line) {
                    if (trim($line)) {
                        $tpl->setVariable("TXT_ADDRESS_LINE", trim($line));
                        $tpl->parseCurrentBlock();
                    }
                }
                $tpl->setCurrentBlock("address");
                $tpl->setVariable("TXT_ADDRESS", $lng->txt("address"));
                $tpl->parseCurrentBlock();
            }
        }

        // if value "y" show information
        if ($this->getPublicPref($user, "public_org_units") == "y") {
            $tpl->setCurrentBlock("org_units");
            $tpl->setVariable("TXT_ORG_UNITS", $lng->txt("objs_orgu"));
            $tpl->setVariable("ORG_UNITS", $user->getOrgUnitsRepresentation());
            $tpl->parseCurrentBlock();
        }

        // institution / department
        if ($this->getPublicPref($user, "public_institution") == "y" ||
            $this->getPublicPref($user, "public_department") == "y") {
            $tpl->setCurrentBlock("inst_dep");
            $sep = "";
            if ($this->getPublicPref($user, "public_institution") == "y") {
                $h = $lng->txt("institution");
                $v = $user->getInstitution();
                $sep = " / ";
            }
            if ($this->getPublicPref($user, "public_department") == "y") {
                $h .= $sep . $lng->txt("department");
                $v .= $sep . $user->getDepartment();
            }
            $tpl->setVariable("TXT_INST_DEP", $h);
            $tpl->setVariable("INST_DEP", $v);
            $tpl->parseCurrentBlock();
        }

        // contact
        $val_arr = array(
            "getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
            "getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email", "getSecondEmail" => "second_email");
        $v = $sep = "";
        foreach ($val_arr as $key => $value) {
            // if value "y" show information
            if ($this->getPublicPref($user, "public_" . $value) == "y") {
                $v .= $sep . $lng->txt($value) . ": " . $user->$key();
                $sep = "<br />";
            }
        }
        if ($v != "") {
            $tpl->parseCurrentBlock("contact");
            $tpl->setVariable("TXT_CONTACT", $lng->txt("contact"));
            $tpl->setVariable("CONTACT", $v);
            $tpl->parseCurrentBlock();
        }

        
        $val_arr = array(
            "getHobby" => "hobby",
            "getGeneralInterestsAsText" => "interests_general",
            "getOfferingHelpAsText" => "interests_help_offered",
            "getLookingForHelpAsText" => "interests_help_looking",
            "getMatriculation" => "matriculation",
            "getClientIP" => "client_ip");
            
        foreach ($val_arr as $key => $value) {
            // if value "y" show information
            if ($this->getPublicPref($user, "public_" . $value) == "y") {
                $tpl->setCurrentBlock("profile_data");
                $tpl->setVariable("TXT_DATA", $lng->txt($value));
                $tpl->setVariable("DATA", $user->$key());
                $tpl->parseCurrentBlock();
            }
        }

        // portfolios
        $back = ($this->getBackUrl() != "")
            ? $this->getBackUrl()
            : ilLink::_getStaticLink($this->getUserId(), "usr", true);
        $port = ilObjPortfolio::getAvailablePortfolioLinksForUserIds(array($this->getUserId()), $back);
        $cnt = 0;
        if (count($port) > 0) {
            foreach ($port as $u) {
                $tpl->setCurrentBlock("portfolio");
                foreach ($u as $link => $title) {
                    $cnt++;
                    $tpl->setVariable("HREF_PORTFOLIO", $link);
                    $tpl->setVariable("TITLE_PORTFOLIO", $title);
                    $tpl->parseCurrentBlock();
                }
            }
            $tpl->setCurrentBlock("portfolios");
            if ($cnt > 1) {
                $lng->loadLanguageModule("prtf");
                $tpl->setVariable("TXT_PORTFOLIO", $lng->txt("prtf_portfolios"));
            } else {
                $tpl->setVariable("TXT_PORTFOLIO", $lng->txt("portfolio"));
            }
            $tpl->parseCurrentBlock();
        }

        // map
        if (ilMapUtil::isActivated() &&
            $this->getPublicPref($user, "public_location") == "y" &&
            $user->getLatitude() != "") {
            $tpl->setVariable("TXT_LOCATION", $lng->txt("location"));

            $map_gui = ilMapUtil::getMapGUI();
            $map_gui->setMapId("user_map")
                    ->setWidth("350px")
                    ->setHeight("230px")
                    ->setLatitude($user->getLatitude())
                    ->setLongitude($user->getLongitude())
                    ->setZoom($user->getLocationZoom())
                    ->setEnableNavigationControl(true)
                    ->addUserMarker($user->getId());

            $tpl->setVariable("MAP_CONTENT", $map_gui->getHtml());
        }
        
        // additional defined user data fields
        $this->user_defined_fields = ilUserDefinedFields::_getInstance();
        $user_defined_data = $user->getUserDefinedData();
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            // public setting
            if ($this->getPublicPref($user, "public_udf_" . $definition["field_id"]) == "y") {
                if ($user_defined_data["f_" . $definition["field_id"]] != "") {
                    $tpl->setCurrentBlock("udf_data");
                    $tpl->setVariable("TXT_UDF_DATA", $definition["field_name"]);
                    $tpl->setVariable("UDF_DATA", $user_defined_data["f_" . $definition["field_id"]]);
                    $tpl->parseCurrentBlock();
                }
            }
        }
        
        // additional information
        $additional = $this->getAdditional();
        if (is_array($additional)) {
            foreach ($additional as $key => $val) {
                $tpl->setCurrentBlock("profile_data");
                $tpl->setVariable("TXT_DATA", $key);
                $tpl->setVariable("DATA", $val);
                $tpl->parseCurrentBlock();
            }
        }

        if (
            $this->getUserId() != $ilUser->getId() &&
            !$ilUser->isAnonymous() &&
            !ilObjUser::_isAnonymous($this->getUserId())
        ) {
            $button = ilBuddySystemLinkButton::getInstanceByUserId($user->getId());
            $tpl->setVariable('BUDDY_HTML', $button->getHtml());
        }
        
        // badges
        $user_badges = ilBadgeAssignment::getInstancesByUserId($user->getId());
        if ($user_badges) {
            $has_public_badge = false;
            $cnt = 0;
            
            $cut = 20;
            
            foreach ($user_badges as $ass) {
                // only active
                if ($ass->getPosition()) {
                    $cnt++;
                    
                    $renderer = new ilBadgeRenderer($ass);

                    // limit to 20, [MORE] link
                    if ($cnt <= $cut) {
                        $tpl->setCurrentBlock("badge_bl");
                        $tpl->setVariable("BADGE", $renderer->getHTML());
                    } else {
                        $tpl->setCurrentBlock("badge_hidden_item_bl");
                        $tpl->setVariable("BADGE_HIDDEN", $renderer->getHTML());
                    }
                    $tpl->parseCurrentBlock();

                    $has_public_badge = true;
                }
            }
            
            if ($cnt > $cut) {
                $lng->loadLanguageModule("badge");
                $tpl->setVariable("BADGE_HIDDEN_TXT_MORE", $lng->txt("badge_profile_more"));
                $tpl->setVariable("BADGE_HIDDEN_TXT_LESS", $lng->txt("badge_profile_less"));
                $tpl->touchBlock("badge_js_bl");
            }
            
            if ($has_public_badge) {
                $tpl->setVariable("TXT_BADGES", $lng->txt("obj_bdga"));
            }
        }

        $goto = "";
        if ($a_add_goto) {
            global $DIC;

            $mtpl = $DIC->ui()->mainTemplate();

            $mtpl->setPermanentLink(
                "usr",
                $user->getId(),
                "",
                "_top"
            );
        }
        return $tpl->get() . $goto;
    }
    
    /**
    * Deliver vcard information.
    */
    public function deliverVCard() : void
    {
        $type = "";
        // get user object
        if (!ilObject::_exists($this->getUserId())) {
            return;
        }
        $user = new ilObjUser($this->getUserId());
        
        $vcard = new ilvCard();
        
        // ilsharedresourceGUI: embedded in shared portfolio
        if ($user->getPref("public_profile") != "y" &&
            $user->getPref("public_profile") != "g" &&
            strtolower($this->profile_request->getBaseClass()) != "ilsharedresourcegui" &&
            $this->current_user->getId() != $this->getUserId()
        ) {
            return;
        }
        
        $vcard->setName($user->getLastname(), $user->getFirstname(), "", $user->getUTitle());
        $vcard->setNickname($user->getLogin());
        
        $webspace_dir = ilFileUtils::getWebspaceDir("output");
        $imagefile = $webspace_dir . "/usr_images/" . $user->getPref("profile_image");
        if ($user->getPref("public_upload") == "y" && is_file($imagefile)) {
            $fh = fopen($imagefile, 'rb');
            if ($fh) {
                $image = fread($fh, filesize($imagefile));
                fclose($fh);
                $mimetype = ilObjMediaObject::getMimeType($imagefile);
                if (0 === strpos($mimetype, "image")) {
                    $type = $mimetype;
                }
                $vcard->setPhoto($image, $type);
            }
        }

        $val_arr = array("getOrgUnitsRepresentation" => "org_units", "getInstitution" => "institution",
            "getDepartment" => "department", "getStreet" => "street",
            "getZipcode" => "zipcode", "getCity" => "city", "getCountry" => "country",
            "getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
            "getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email",
            "getHobby" => "hobby", "getMatriculation" => "matriculation",
            "getClientIP" => "client_ip", "dummy" => "location");

        $org = array();
        $adr = array();
        foreach ($val_arr as $key => $value) {
            // if value "y" show information
            if ($user->getPref("public_" . $value) == "y") {
                switch ($value) {
                    case "institution":
                        $org[0] = $user->$key();
                        break;
                    case "department":
                        $org[1] = $user->$key();
                        break;
                    case "street":
                        $adr[2] = $user->$key();
                        break;
                    case "zipcode":
                        $adr[5] = $user->$key();
                        break;
                    case "city":
                        $adr[3] = $user->$key();
                        break;
                    case "country":
                        $adr[6] = $user->$key();
                        break;
                    case "phone_office":
                        $vcard->setPhone($user->$key(), TEL_TYPE_WORK);
                        break;
                    case "phone_home":
                        $vcard->setPhone($user->$key(), TEL_TYPE_HOME);
                        break;
                    case "phone_mobile":
                        $vcard->setPhone($user->$key(), TEL_TYPE_CELL);
                        break;
                    case "fax":
                        $vcard->setPhone($user->$key(), TEL_TYPE_FAX);
                        break;
                    case "email":
                        $vcard->setEmail($user->$key());
                        break;
                    case "hobby":
                        $vcard->setNote($user->$key());
                        break;
                    case "location":
                        $vcard->setPosition($user->getLatitude(), $user->getLongitude());
                        break;
                }
            }
        }
        
        if (count($org)) {
            $vcard->setOrganization(implode(";", $org));
        }
        if (count($adr)) {
            $vcard->setAddress(
                $adr[0] ?? "",
                $adr[1] ?? "",
                $adr[2] ?? "",
                $adr[3] ?? "",
                $adr[4] ?? "",
                $adr[5] ?? "",
                $adr[6] ?? ""
            );
        }
        
        ilUtil::deliverData($vcard->buildVCard(), $vcard->getFilename(), $vcard->getMimetype());
    }
    
    /**
     * Check if given user id is valid
     */
    protected static function validateUser(int $usrId) : bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilCtrl = $DIC->ctrl();

        if (ilObject::_lookupType($usrId) != "usr") {
            return false;
        }

        $user = new ilObjUser($usrId);

        if ($ilUser->isAnonymous()) {
            if (strtolower($ilCtrl->getCmd()) == strtolower('approveContactRequest')) {
                $ilCtrl->redirectToURL('login.php?cmd=force_login&target=usr_' . $usrId . '_contact_approved');
            } elseif (strtolower($ilCtrl->getCmd()) == strtolower('ignoreContactRequest')) {
                $ilCtrl->redirectToURL('login.php?cmd=force_login&target=usr_' . $usrId . '_contact_ignored');
            }

            if ($user->getPref("public_profile") != "g") {
                // #12151
                if ($user->getPref("public_profile") == "y") {
                    $ilCtrl->redirectToURL("login.php?cmd=force_login&target=usr_" . $usrId);
                }

                return false;
            }
        }

        return true;
    }
    
    public function renderTitle() : void
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        $tpl->resetHeaderBlock();
        $tpl->setTitle(ilUserUtil::getNamePresentation($this->getUserId()));
        $tpl->setTitleIcon(ilObjUser::_getPersonalPicturePath($this->getUserId(), "xsmall"));
        
        $this->handleBackUrl();
    }
    
    /**
     * Check if current profile portfolio is accessible
     */
    protected function getProfilePortfolio() : ?int
    {
        $portfolio_id = ilObjPortfolio::getDefaultPortfolio($this->getUserId());
        if ($portfolio_id) {
            $access_handler = new ilPortfolioAccessHandler();
            if ($access_handler->checkAccess("read", "", $portfolio_id)) {
                return $portfolio_id;
            }
        }
        return null;
    }
    
    public static function getAutocompleteResult(
        string $a_field_id,
        string $a_term
    ) : array {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $result = [];
        
        $multi_fields = array("interests_general", "interests_help_offered", "interests_help_looking");
        if (in_array($a_field_id, $multi_fields) && $a_term) {
            // registration has no current user
            $user_id = null;
            if ($ilUser && $ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID) {
                $user_id = $ilUser->getId();
            }

            $result = array();
            $cnt = 0;

            // term is searched in ALL interest fields, no distinction
            foreach (ilObjUser::findInterests($a_term, $ilUser->getId()) as $item) {
                $result[$cnt] = new stdClass();
                $result[$cnt]->value = $item;
                $result[$cnt]->label = $item;
                $cnt++;
            }

            // :TODO: search in skill data
            /*
            foreach (ilSkillTreeNode::findSkills($a_term) as $skill) {
                $result[$cnt] = new stdClass();
                $result[$cnt]->value = $skill;
                $result[$cnt]->label = $skill;
                $cnt++;
            }*/
        }
        
        return $result;
    }
    
    protected function doProfileAutoComplete() : void
    {
        $field_id = $this->profile_request->getFieldId();
        $term = $this->profile_request->getTerm();
                
        $result = self::getAutocompleteResult($field_id, $term);

        echo json_encode($result, JSON_THROW_ON_ERROR);
        exit();
    }

    protected function approveContactRequest() : void
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $osd_id = $this->profile_request->getOsdId();
        if ($osd_id) {
            $ilCtrl->setParameterByClass('ilBuddySystemGUI', 'osd_id', $osd_id);
        }
        $ilCtrl->setParameterByClass('ilBuddySystemGUI', 'user_id', $this->getUserId());
        $ilCtrl->redirectByClass(array('ilPublicUserProfileGUI', 'ilBuddySystemGUI'), 'link');
    }

    protected function ignoreContactRequest() : void
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        $osd_id = $this->profile_request->getOsdId();
        if ($osd_id > 0) {
            $ilCtrl->setParameterByClass('ilBuddySystemGUI', 'osd_id', $osd_id);
        }

        $ilCtrl->setParameterByClass('ilBuddySystemGUI', 'user_id', $this->getUserId());
        $ilCtrl->redirectByClass(array('ilPublicUserProfileGUI', 'ilBuddySystemGUI'), 'ignore');
    }
}
