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
 * Wiki link / page title handling:
 *
 * Media wiki uses the following fields for page titles/links (see Title.php):
 *
 * $mDbkeyform = $dbkey;				($dbkey includes "_" for " ")
 * $mUrlform = ilWikiUtil::wfUrlencode($dbkey);
 * $mTextform = str_replace('_', ' ', $dbkey);
 *
 * ILIAS uses the ilWikiUtil::makeDbTitle($mTextform) (including " ") as key in the database table and
 * the ilWikiUtil::makeUrlTitle($mTextform) ("_" for " ")for embedding things in URLs.
 *
 */
// From include/Unicode/UtfNormal.php
if (!defined('UTF8_REPLACEMENT')) {
    define('UTF8_REPLACEMENT', "\xef\xbf\xbd" /*codepointToUtf8( UNICODE_REPLACEMENT )*/);
}

const IL_WIKI_MODE_REPLACE = "replace";
const IL_WIKI_MODE_COLLECT = "collect";
const IL_WIKI_MODE_EXT_COLLECT = "ext_collect";

/**
 * Utility class for wiki.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiUtil
{
    /**
     * This one is based on Mediawiki Parser->replaceInternalLinks
     * since we display images in another way, only text links are processed
     */
    public static function replaceInternalLinks(
        string $s,
        int $a_wiki_id,
        bool $a_offline = false
    ) : string {
        return self::processInternalLinks(
            $s,
            $a_wiki_id,
            IL_WIKI_MODE_REPLACE,
            false,
            $a_offline
        );
    }

    /**
     * Collect internal wiki links of a string
     */
    public static function collectInternalLinks(
        string $s,
        int $a_wiki_id,
        bool $a_collect_non_ex = false
    ) : array {
        return self::processInternalLinks(
            $s,
            $a_wiki_id,
            IL_WIKI_MODE_COLLECT,
            $a_collect_non_ex
        );
    }
    
    /**
     * Process internal links
     * (internal)
     * @return array|false|string
     */
    public static function processInternalLinks(
        string $s,
        int $a_wiki_id,
        string $a_mode = IL_WIKI_MODE_REPLACE,
        bool $a_collect_non_ex = false,
        bool $a_offline = false
    ) {
        include_once("./Modules/Wiki/libs/Sanitizer.php");
        $collect = array();
        // both from mediawiki DefaulSettings.php
        $wgLegalTitleChars = " %!\"$&'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+";

        // dummies for wiki globals
        $GLOBALS["wgContLang"] = new class {
            public function getNsIndex($a_p) : bool
            {
                return false;
            }
            public function lc($a_key) : bool
            {
                return false;
            }
        };
        $GLOBALS["wgInterWikiCache"] = false;
        
        # the % is needed to support urlencoded titles as well
        //$tc = Title::legalChars().'#%';
        $tc = $wgLegalTitleChars . '#%';

        //$sk = $this->mOptions->getSkin();

        #split the entire text string on occurences of [[
        $a = explode('[[', ' ' . $s);
        #get the first element (all text up to first [[), and remove the space we added
        $s = array_shift($a);
        $s = substr($s, 1);

        # Match a link having the form [[namespace:link|alternate]]trail
        $e1 = "/^([{$tc}]+)(?:\\|(.+?))?]](.*)\$/sD";
        
        # Match cases where there is no "]]", which might still be images
        //		static $e1_img = FALSE;
        //		if ( !$e1_img ) { $e1_img = "/^([{$tc}]+)\\|(.*)\$/sD"; }

        # Match the end of a line for a word that's not followed by whitespace,
        # e.g. in the case of 'The Arab al[[Razi]]', 'al' will be matched
        //		$e2 = wfMsgForContent( 'linkprefix' );

        /*		$useLinkPrefixExtension = $wgContLang->linkPrefixExtension();
                if( is_null( $this->mTitle ) ) {
                    throw new MWException( __METHOD__.": \$this->mTitle is null\n" );
                }
                $nottalk = !$this->mTitle->isTalkPage();*/
        $nottalk = true;

        /*		if ( $useLinkPrefixExtension ) {
                    $m = array();
                    if ( preg_match( $e2, $s, $m ) ) {
                        $first_prefix = $m[2];
                    } else {
                        $first_prefix = false;
                    }
                } else {*/
        $prefix = '';
        //		}

        $useSubpages = false;
        
        # Loop for each link
        for ($k = 0; isset($a[$k]); $k++) {
            $line = $a[$k];


            $might_be_img = false;

            //wfProfileIn( "$fname-e1" );
            if (preg_match($e1, $line, $m)) { # page with normal text or alt
                $text = $m[2];
                # If we get a ] at the beginning of $m[3] that means we have a link that's something like:
                # [[Image:Foo.jpg|[http://example.com desc]]] <- having three ] in a row fucks up,
                # the real problem is with the $e1 regex
                # See bug 1300.
                #
                # Still some problems for cases where the ] is meant to be outside punctuation,
                # and no image is in sight. See bug 2095.
                #
                if ($text !== '' &&
                    strpos($m[3], ']') === 0 &&
                    strpos($text, '[') !== false
                ) {
                    $text .= ']'; # so that replaceExternalLinks($text) works later
                    $m[3] = substr($m[3], 1);
                }
                # fix up urlencoded title texts
                if (strpos($m[1], '%') !== false) {
                    # Should anchors '#' also be rejected?
                    $m[1] = str_replace(array('<', '>'), array('&lt;', '&gt;'), urldecode($m[1]));
                }
                $trail = $m[3];
            /*			} elseif( preg_match($e1_img, $line, $m) ) { # Invalid, but might be an image with a link in its caption
                            $might_be_img = true;
                            $text = $m[2];
                            if ( strpos( $m[1], '%' ) !== false ) {
                                $m[1] = urldecode($m[1]);
                            }
                            $trail = "";*/
            } else { # Invalid form; output directly
                $s .= $prefix . '[[' . $line ;
                //wfProfileOut( "$fname-e1" );
                continue;
            }
            //wfProfileOut( "$fname-e1" );
            //wfProfileIn( "$fname-misc" );

            # Don't allow internal links to pages containing
            # PROTO: where PROTO is a valid URL protocol; these
            # should be external links.
            if (preg_match('/^\b%na' . self::wfUrlProtocols() . 'me/', $m[1])) {
                $s .= $prefix . '[[' . $line ;
                continue;
            }

            # Make subpage if necessary
            /*			if( $useSubpages ) {
                            $link = $this->maybeDoSubpageLink( $m[1], $text );
                        } else {*/
            $link = $m[1];
            //			}

            $noforce = (strpos($m[1], ':') !== 0);
            if (!$noforce) {
                # Strip off leading ':'
                $link = substr($link, 1);
            }

            //			wfProfileOut( "$fname-misc" );
            //			wfProfileIn( "$fname-title" );

            $nt = Title::newFromText($link);

            if (!$nt) {
                $s .= $prefix . '[[' . $line;
                continue;
            }

            $wasblank = ('' == $text);
            if ($wasblank) {
                $text = $link;
            }

            // Media wiki performs an intermediate step here (Parser->makeLinkHolder)
            if ($a_mode === IL_WIKI_MODE_REPLACE) {
                $s .= self::makeLink(
                    $nt,
                    $a_wiki_id,
                    $text,
                    '',
                    $trail,
                    $prefix,
                    $a_offline
                );
            }
            if ($a_mode === IL_WIKI_MODE_EXT_COLLECT) {
                if (is_object($nt)) {
                    $url_title = self::makeUrlTitle($nt->mTextform);
                    $db_title = self::makeDbTitle($nt->mTextform);
                    [$inside, $trail] = self::splitTrail($trail);
                    $collect[] = array("nt" => $nt, "text" => $text,
                        "trail" => $trail, "db_title" => $db_title,
                        "url_title" => $url_title);
                }
            } else {
                $db_title = self::makeDbTitle($nt->mTextform);

                if ((ilWikiPage::_wikiPageExists($a_wiki_id, $db_title) ||
                    $a_collect_non_ex)
                &&
                    !in_array($db_title, $collect)) {
                    $collect[] = $db_title;
                }
            }
        }

        //wfProfileOut( $fname );

        if ($a_mode === IL_WIKI_MODE_COLLECT ||
            $a_mode === IL_WIKI_MODE_EXT_COLLECT) {
            return $collect;
        } else {
            return $s;
        }
    }

    public static function removeUnsafeCharacters(
        string $a_str
    ) : string {
        return str_replace(array("\x00", "\n", "\r", "\\", "'", '"', "\x1a"), "", $a_str);
    }
    
    /**
     * Make a wiki link, the following formats are supported:
     *
     * [[Page Title]]
     * [[Page Title|Presentation Text]]
     * [[Page Title#Anchor]]
     * [[Page Title#Anchor|Presentation Text]]
     * [[#Anchor|Presentation Text]] (link to anchor on same wiki page)
     */
    public static function makeLink(
        object $nt,
        int $a_wiki_id,
        string $text = '',
        string $query = '',
        string $trail = '',
        string $prefix = '',
        bool $a_offline = false
    ) : string {
        global $DIC;

        $request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $ilCtrl = $DIC->ctrl();

        if (!is_object($nt)) {
            # Fail gracefully
            $retVal = "<!-- ERROR -->{$prefix}{$text}{$trail}";
        } else {
            
            // remove anchor from text, define anchor
            $anc = "";
            if ($nt->mFragment != "") {
                if (substr($text, strlen($text) - strlen("#" . $nt->mFragment)) === "#" . $nt->mFragment) {
                    $text = substr($text, 0, strlen($text) - strlen("#" . $nt->mFragment));
                }
                $anc = "#" . $nt->mFragment;
            }
            
            # Separate the link trail from the rest of the link
            // outcommented due to bug #14590
            //			list( $inside, $trail ) = ilWikiUtil::splitTrail( $trail );
            
            $retVal = '***' . $text . "***" . $trail;
            $url_title = self::makeUrlTitle($nt->mTextform);
            $db_title = self::makeDbTitle($nt->mTextform);
            if ($db_title != "") {
                $pg_exists = ilWikiPage::_wikiPageExists($a_wiki_id, $db_title);
            } else {
                // links on same page (only anchor used)
                $pg_exists = true;
            }
            
            //var_dump($nt);
            //var_dump($inside);
            //var_dump($trail);
            $wiki_link_class = (!$pg_exists)
                ? ' class="ilc_link_IntLink ilWikiPageMissing" '
                : ' class="ilc_link_IntLink" ';

            if (!$a_offline) {
                if ($url_title != "") {
                    $ilCtrl->setParameterByClass("ilobjwikigui", "page", $url_title);
                    $retVal = '<a ' . $wiki_link_class . ' href="' .
                        $ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoPage") . $anc .
                        '">' . $text . '</a>' . $trail;
                    $ilCtrl->setParameterByClass(
                        "ilobjwikigui",
                        "page",
                        $request->getPage()
                    );
                } else {
                    $retVal = '<a ' . $wiki_link_class . ' href="' .
                        $anc .
                        '">' . $text . '</a>' . $trail;
                }
            } else {
                if ($pg_exists) {
                    if ($db_title != "") {
                        $pg_id = ilWikiPage::getPageIdForTitle($a_wiki_id, $db_title);
                        $retVal = '<a ' . $wiki_link_class . ' href="' .
                            "wpg_" . $pg_id . ".html" . $anc .
                            '">' . $text . '</a>' . $trail;
                    } else {
                        $retVal = '<a ' . $wiki_link_class . ' href="' .
                            $anc .
                            '">' . $text . '</a>' . $trail;
                    }
                } else {
                    $retVal = $text . $trail;
                }
            }
        }
        return $retVal;
    }
    
    /**
     * From mediawiki GlobalFunctions.php
     * @return string
     */
    public static function wfUrlProtocols() : string
    {
        $wgUrlProtocols = array(
            'http://',
            'https://',
            'ftp://',
            'irc://',
            'gopher://',
            'telnet://', // Well if we're going to support the above.. -ævar
            'nntp://', // @bug 3808 RFC 1738
            'worldwind://',
            'mailto:',
            'news:'
        );

        // Support old-style $wgUrlProtocols strings, for backwards compatibility
        // with LocalSettings files from 1.5
        $protocols = array();
        foreach ($wgUrlProtocols as $protocol) {
            $protocols[] = preg_quote($protocol, '/');
        }

        return implode('|', $protocols);
    }
    
    public static function wfUrlencode(
        string $s
    ) : string {
        $s = urlencode($s);
        return $s;
    }

    public static function makeDbTitle(
        string $a_par
    ) : string {
        $a_par = self::removeUnsafeCharacters($a_par);
        return str_replace("_", " ", $a_par);
    }

    public static function makeUrlTitle(
        string $a_par
    ) : string {
        $a_par = self::removeUnsafeCharacters($a_par);
        $a_par = str_replace(" ", "_", $a_par);
        return self::wfUrlencode($a_par);
    }
    
    public static function splitTrail(
        string $trail
    ) : array {
        $regex = '/^([a-z]+)(.*)$/sD';
        
        $inside = '';
        if ('' != $trail) {
            $m = array();
            
            if (preg_match($regex, $trail, $m)) {
                $inside = $m[1];
                $trail = $m[2];
            }
        }

        return array( $inside, $trail );
    }

    public static function sendNotification(
        string $a_action,
        int $a_type,
        int $a_wiki_ref_id,
        int $a_page_id,
        ?string $a_comment = null
    ) : void {
        global $DIC;

        $log = ilLoggerFactory::getLogger('wiki');
        $log->debug("start... vvvvvvvvvvvvvvvvvvvvvvvvvvv");

        $ilUser = $DIC->user();
        $ilObjDataCache = $DIC["ilObjDataCache"];
        $ilAccess = $DIC->access();

        $wiki_id = $ilObjDataCache->lookupObjId($a_wiki_ref_id);
        $wiki = new ilObjWiki($a_wiki_ref_id, true);
        $page = new ilWikiPage($a_page_id);
        
        // #11138
        $ignore_threshold = ($a_action === "comment");
        
        // 1st update will be converted to new - see below
        if ($a_action === "new") {
            return;
        }

        $log->debug("-- get notifications");
        if ($a_type == ilNotification::TYPE_WIKI_PAGE) {
            $users = ilNotification::getNotificationsForObject($a_type, $a_page_id, null, $ignore_threshold);
            $wiki_users = ilNotification::getNotificationsForObject(ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            $users = array_merge($users, $wiki_users);
            if (!count($users)) {
                $log->debug("no notifications... ^^^^^^^^^^^^^^^^^^");
                return;
            }

            ilNotification::updateNotificationTime(ilNotification::TYPE_WIKI_PAGE, $a_page_id, $users);
        } else {
            $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            if (!count($users)) {
                $log->debug("no notifications... ^^^^^^^^^^^^^^^^^^");
                return;
            }
        }
        
        ilNotification::updateNotificationTime(ilNotification::TYPE_WIKI, $wiki_id, $users, $a_page_id);
        
        // #15192 - should always be present
        if ($a_page_id) {
            // #18804 - see ilWikiPageGUI::preview()
            $link = ilLink::_getLink("", "wiki", null, "wpage_" . $a_page_id . "_" . $a_wiki_ref_id);
        } else {
            $link = ilLink::_getLink($a_wiki_ref_id);
        }

        $log->debug("-- prepare content");
        $pgui = new ilWikiPageGUI($page->getId());
        $pgui->setRawPageContent(true);
        $pgui->setAbstractOnly(true);
        $pgui->setFileDownloadLink(".");
        $pgui->setFullscreenLink(".");
        $pgui->setSourcecodeDownloadScript(".");
        $snippet = $pgui->showPage();
        $snippet = ilPageObject::truncateHTML($snippet, 500, "...");

        // making things more readable
        $snippet = str_replace(['<br/>', '<br />', '</p>', '</div>'], "\n", $snippet);

        $snippet = trim(strip_tags($snippet));

        // "fake" new (to enable snippet - if any)
        $hist = $page->getHistoryEntries();
        $current_version = array_shift($hist);
        $current_version = $current_version["nr"];
        if (!$current_version && $a_action !== "comment") {
            $a_type = ilNotification::TYPE_WIKI;
            $a_action = "new";
        }

        $log->debug("-- sending mails");
        $mails = [];
        foreach (array_unique($users) as $idx => $user_id) {
            if ($user_id != $ilUser->getId() &&
                $ilAccess->checkAccessOfUser($user_id, 'read', '', $a_wiki_ref_id)) {
                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('wiki');

                if ($a_action === "comment") {
                    $subject = sprintf($ulng->txt('wiki_notification_comment_subject'), $wiki->getTitle(), $page->getTitle());
                    $message = sprintf($ulng->txt('wiki_change_notification_salutation'), ilObjUser::_lookupFullname($user_id)) . "\n\n";

                    $message .= $ulng->txt('wiki_notification_' . $a_action) . ":\n\n";
                    $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                    $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                    $message .= $ulng->txt('wiki_commented_by') . ": " . ilUserUtil::getNamePresentation($ilUser->getId()) . "\n";

                    // include comment/note text
                    if ($a_comment) {
                        $message .= "\n" . $ulng->txt('comment') . ":\n\"" . trim($a_comment) . "\"\n";
                    }

                    $message .= "\n" . $ulng->txt('wiki_change_notification_page_link') . ": " . $link;
                } else {
                    $subject = sprintf($ulng->txt('wiki_change_notification_subject'), $wiki->getTitle(), $page->getTitle());
                    $message = sprintf($ulng->txt('wiki_change_notification_salutation'), ilObjUser::_lookupFullname($user_id)) . "\n\n";

                    if ($a_type == ilNotification::TYPE_WIKI_PAGE) {
                        // update/delete
                        $message .= $ulng->txt('wiki_change_notification_page_body_' . $a_action) . ":\n\n";
                        $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                        $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                        $message .= $ulng->txt('wiki_changed_by') . ": " . ilUserUtil::getNamePresentation($ilUser->getId()) . "\n";

                        if ($snippet) {
                            $message .= "\n" . $ulng->txt('content') . "\n" .
                                "----------------------------------------\n" .
                                $snippet . "\n" .
                                "----------------------------------------\n";
                        }

                        // include comment/note text
                        if ($a_comment) {
                            $message .= "\n" . $ulng->txt('comment') . ":\n\"" . trim($a_comment) . "\"\n";
                        }

                        $message .= "\n" . $ulng->txt('wiki_change_notification_page_link') . ": " . $link;
                    } else {
                        // new
                        $message .= $ulng->txt('wiki_change_notification_body_' . $a_action) . ":\n\n";
                        $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                        $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                        $message .= $ulng->txt('wiki_changed_by') . ": " . ilUserUtil::getNamePresentation($ilUser->getId()) . "\n\n";

                        if ($snippet) {
                            $message .= $ulng->txt('content') . "\n" .
                                "----------------------------------------\n" .
                                $snippet . "\n" .
                                "----------------------------------------\n\n";
                        }

                        $message .= $ulng->txt('wiki_change_notification_link') . ": " . $link;
                    }
                }

                $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $log->debug("before enqueue ($user_id)");
                /*
                $mail_obj->enqueue(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array()
                );*/
                $message .= ilMail::_getInstallationSignature();
                $mails[] = new ilMailValueObject(
                    '',
                    ilObjUser::_lookupLogin($user_id),
                    '',
                    '',
                    $subject,
                    $message,
                    [],
                    false,
                    false
                );
                $log->debug("after enqueue");
            } else {
                unset($users[$idx]);
            }
        }
        if (count($mails) > 0) {
            $processor = new ilMassMailTaskProcessor();
            $processor->run(
                $mails,
                ANONYMOUS_USER_ID,
                "",
                []
            );
        }
        $log->debug("end... ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^");
    }
}
