<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
define("IL_WIKI_MODE_REPLACE", "replace");
define("IL_WIKI_MODE_COLLECT", "collect");
define("IL_WIKI_MODE_EXT_COLLECT", "ext_collect");

/**
* Utility class for wiki.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiUtil
{

    /**
    * This one is based on Mediawiki Parser->replaceInternalLinks
    * since we display images in another way, only text links are processed
    *
    * @param	string		input string
    * @param	string		input string
    *
    * @return	string		output string
    */
    public static function replaceInternalLinks($s, $a_wiki_id, $a_offline = false)
    {
        return ilWikiUtil::processInternalLinks(
            $s,
            $a_wiki_id,
            IL_WIKI_MODE_REPLACE,
            false,
            $a_offline
        );
    }

    /**
    * Collect internal wiki links of a string
    *
    * @param	string		input string
    * @return	string		output string
    */
    public static function collectInternalLinks($s, $a_wiki_id, $a_collect_non_ex = false)
    {
        return ilWikiUtil::processInternalLinks(
            $s,
            $a_wiki_id,
            IL_WIKI_MODE_COLLECT,
            $a_collect_non_ex
        );
    }
    
    /**
    * Process internal links
    *
    * string		$s				string that includes internal wiki links
    * int			$a_wiki_id		wiki id
    * mode
    */
    public static function processInternalLinks(
        $s,
        $a_wiki_id,
        $a_mode = IL_WIKI_MODE_REPLACE,
        $a_collect_non_ex = false,
        $a_offline = false
    ) {
        $collect = array();
        // both from mediawiki DefaulSettings.php
        $wgLegalTitleChars = " %!\"$&'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+";

        // Adapter for media wiki classes
        include_once("./Modules/Wiki/classes/class.ilMediaWikiAdapter.php");
        $GLOBALS["wgContLang"] = new ilMediaWikiAdapter();
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
                    substr($m[3], 0, 1) === ']' &&
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
            if (preg_match('/^\b(?:' . ilWikiUtil::wfUrlProtocols() . ')/', $m[1])) {
                $s .= $prefix . '[[' . $line ;
                continue;
            }

            # Make subpage if necessary
            /*			if( $useSubpages ) {
                            $link = $this->maybeDoSubpageLink( $m[1], $text );
                        } else {*/
            $link = $m[1];
            //			}

            $noforce = (substr($m[1], 0, 1) != ':');
            if (!$noforce) {
                # Strip off leading ':'
                $link = substr($link, 1);
            }

            //			wfProfileOut( "$fname-misc" );
            //			wfProfileIn( "$fname-title" );

            // todo
            include_once("./Modules/Wiki/mediawiki/Title.php");
            include_once("./Services/Utilities/classes/Sanitizer.php");
            //$nt = Title::newFromText( $this->mStripState->unstripNoWiki($link) );
            
            // todo: check step by step
            //echo "<br>".htmlentities($link)."---";
            $nt = Title::newFromText($link);

            if (!$nt) {
                $s .= $prefix . '[[' . $line;
                //wfProfileOut( "$fname-title" );
                continue;
            }

            /*			$ns = $nt->getNamespace();
                        $iw = $nt->getInterWiki();
                        wfProfileOut( "$fname-title" );

            /*			if ($might_be_img) { # if this is actually an invalid link
                            wfProfileIn( "$fname-might_be_img" );
                            if ($ns == NS_IMAGE && $noforce) { #but might be an image
                                $found = false;
                                while (isset ($a[$k+1]) ) {
                                    #look at the next 'line' to see if we can close it there
                                    $spliced = array_splice( $a, $k + 1, 1 );
                                    $next_line = array_shift( $spliced );
                                    $m = explode( ']]', $next_line, 3 );
                                    if ( count( $m ) == 3 ) {
                                        # the first ]] closes the inner link, the second the image
                                        $found = true;
                                        $text .= "[[{$m[0]}]]{$m[1]}";
                                        $trail = $m[2];
                                        break;
                                    } elseif ( count( $m ) == 2 ) {
                                        #if there's exactly one ]] that's fine, we'll keep looking
                                        $text .= "[[{$m[0]}]]{$m[1]}";
                                    } else {
                                        #if $next_line is invalid too, we need look no further
                                        $text .= '[[' . $next_line;
                                        break;
                                    }
                                }
                                if ( !$found ) {
                                    # we couldn't find the end of this imageLink, so output it raw
                                    #but don't ignore what might be perfectly normal links in the text we've examined
                                    $text = $this->replaceInternalLinks($text);
                                    $s .= "{$prefix}[[$link|$text";
                                    # note: no $trail, because without an end, there *is* no trail
                                    wfProfileOut( "$fname-might_be_img" );
                                    continue;
                                }
                            } else { #it's not an image, so output it raw
                                $s .= "{$prefix}[[$link|$text";
                                # note: no $trail, because without an end, there *is* no trail
                                wfProfileOut( "$fname-might_be_img" );
                                continue;
                            }
                            wfProfileOut( "$fname-might_be_img" );
                        }
            */

            $wasblank = ('' == $text);
            if ($wasblank) {
                $text = $link;
            }

            # Link not escaped by : , create the various objects
            if ($noforce) {
                # Interwikis
                /*wfProfileIn( "$fname-interwiki" );
                if( $iw && $this->mOptions->getInterwikiMagic() && $nottalk && $wgContLang->getLanguageName( $iw ) ) {
                    $this->mOutput->addLanguageLink( $nt->getFullText() );
                    $s = rtrim($s . $prefix);
                    $s .= trim($trail, "\n") == '' ? '': $prefix . $trail;
                    wfProfileOut( "$fname-interwiki" );
                    continue;
                }
                wfProfileOut( "$fname-interwiki" );*/

/*				if ( $ns == NS_IMAGE ) {
                    wfProfileIn( "$fname-image" );
                    if ( !wfIsBadImage( $nt->getDBkey(), $this->mTitle ) ) {
                        # recursively parse links inside the image caption
                        # actually, this will parse them in any other parameters, too,
                        # but it might be hard to fix that, and it doesn't matter ATM
                        $text = $this->replaceExternalLinks($text);
                        $text = $this->replaceInternalLinks($text);

                        # cloak any absolute URLs inside the image markup, so replaceExternalLinks() won't touch them
                        $s .= $prefix . $this->armorLinks( $this->makeImage( $nt, $text ) ) . $trail;
                        $this->mOutput->addImage( $nt->getDBkey() );

                        wfProfileOut( "$fname-image" );
                        continue;
                    } else {
                        # We still need to record the image's presence on the page
                        $this->mOutput->addImage( $nt->getDBkey() );
                    }
                    wfProfileOut( "$fname-image" );

                }
*/
/*				if ( $ns == NS_CATEGORY ) {
                    wfProfileIn( "$fname-category" );
                    $s = rtrim($s . "\n"); # bug 87

                    if ( $wasblank ) {
                        $sortkey = $this->getDefaultSort();
                    } else {
                        $sortkey = $text;
                    }
                    $sortkey = Sanitizer::decodeCharReferences( $sortkey );
                    $sortkey = str_replace( "\n", '', $sortkey );
                    $sortkey = $wgContLang->convertCategoryKey( $sortkey );
                    $this->mOutput->addCategory( $nt->getDBkey(), $sortkey );
*/
                    /**
                     * Strip the whitespace Category links produce, see bug 87
                     * @todo We might want to use trim($tmp, "\n") here.
                     */
//					$s .= trim($prefix . $trail, "\n") == '' ? '': $prefix . $trail;

//					wfProfileOut( "$fname-category" );
//					continue;
//				}
            }

            # Self-link checking
            /*			if( $nt->getFragment() === '' ) {
                            if( in_array( $nt->getPrefixedText(), $selflink, true ) ) {
                                $s .= $prefix . $sk->makeSelfLinkObj( $nt, $text, '', $trail );
                                continue;
                            }
                        }*/

            # Special and Media are pseudo-namespaces; no pages actually exist in them
            /*			if( $ns == NS_MEDIA ) {
                            $link = $sk->makeMediaLinkObj( $nt, $text );
                            # Cloak with NOPARSE to avoid replacement in replaceExternalLinks
                            $s .= $prefix . $this->armorLinks( $link ) . $trail;
                            $this->mOutput->addImage( $nt->getDBkey() );
                            continue;
                        } elseif( $ns == NS_SPECIAL ) {
                            $s .= $this->makeKnownLinkHolder( $nt, $text, '', $trail, $prefix );
                            continue;
                        } elseif( $ns == NS_IMAGE ) {
                            $img = new Image( $nt );
                            if( $img->exists() ) {
                                // Force a blue link if the file exists; may be a remote
                                // upload on the shared repository, and we want to see its
                                // auto-generated page.
                                $s .= $this->makeKnownLinkHolder( $nt, $text, '', $trail, $prefix );
                                $this->mOutput->addLink( $nt );
                                continue;
                            }
                        }*/
            
            // Media wiki performs an intermediate step here (Parser->makeLinkHolder)
            if ($a_mode == IL_WIKI_MODE_REPLACE) {
                $s .= ilWikiUtil::makeLink(
                    $nt,
                    $a_wiki_id,
                    $text,
                    '',
                    $trail,
                    $prefix,
                    $a_offline
                );
                //echo "<br>-".htmlentities($s)."-";
            }
            if ($a_mode == IL_WIKI_MODE_EXT_COLLECT) {
                if (is_object($nt)) {
                    $url_title = ilWikiUtil::makeUrlTitle($nt->mTextform);
                    $db_title = ilWikiUtil::makeDbTitle($nt->mTextform);
                    list($inside, $trail) = ilWikiUtil::splitTrail($trail);
                    $collect[] = array("nt" => $nt, "text" => $text,
                        "trail" => $trail, "db_title" => $db_title,
                        "url_title" => $url_title);
                }
            } else {
                $url_title = ilWikiUtil::makeUrlTitle($nt->mTextform);
                $db_title = ilWikiUtil::makeDbTitle($nt->mTextform);

                //$s .= ilWikiUtil::makeLink($nt, $a_wiki_id, $text, '', $trail, $prefix);
                include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                if ((ilWikiPage::_wikiPageExists($a_wiki_id, $db_title) ||
                    $a_collect_non_ex)
                &&
                    !in_array($db_title, $collect)) {
                    $collect[] = $db_title;
                }
            }
        }

        //wfProfileOut( $fname );

        if ($a_mode == IL_WIKI_MODE_COLLECT ||
            $a_mode == IL_WIKI_MODE_EXT_COLLECT) {
            return $collect;
        } else {
            return $s;
        }
    }

    /**
    * See class.ilInitialisation.php
    */
    public static function removeUnsafeCharacters($a_str)
    {
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
        &$nt,
        $a_wiki_id,
        $text = '',
        $query = '',
        $trail = '',
        $prefix = '',
        $a_offline = false
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        //wfProfileIn( __METHOD__ );
        if (!is_object($nt)) {
            # Fail gracefully
            $retVal = "<!-- ERROR -->{$prefix}{$text}{$trail}";
        } else {
            
//var_dump($trail);
            //var_dump($nt);

            // remove anchor from text, define anchor
            $anc = "";
            if ($nt->mFragment != "") {
                if (substr($text, strlen($text) - strlen("#" . $nt->mFragment))
                    == "#" . $nt->mFragment) {
                    $text = substr($text, 0, strlen($text) - strlen("#" . $nt->mFragment));
                    $anc = "#" . $nt->mFragment;
                } else {
                    $anc = "#" . $nt->mFragment;
                }
            }
            
            # Separate the link trail from the rest of the link
            // outcommented due to bug #14590
            //			list( $inside, $trail ) = ilWikiUtil::splitTrail( $trail );
            
            $retVal = '***' . $text . "***" . $trail;
            $url_title = ilWikiUtil::makeUrlTitle($nt->mTextform);
            $db_title = ilWikiUtil::makeDbTitle($nt->mTextform);
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
                    $ilCtrl->setParameterByClass("ilobjwikigui", "page", $_GET["page"]);
                } else {
                    $retVal = '<a ' . $wiki_link_class . ' href="' .
                        $anc .
                        '">' . $text . '</a>' . $trail;
                }
            } else {
                if ($pg_exists) {
                    if ($db_title != "") {
                        $pg_id = ilWikiPage::getIdForPageTitle($a_wiki_id, $db_title);
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

            /*			if ( $nt->isExternal() ) {
                            $nr = array_push( $this->mInterwikiLinkHolders['texts'], $prefix.$text.$inside );
                            $this->mInterwikiLinkHolders['titles'][] = $nt;
                            $retVal = '<!--IWLINK '. ($nr-1) ."-->{$trail}";
                        } else {
                            $nr = array_push( $this->mLinkHolders['namespaces'], $nt->getNamespace() );
                            $this->mLinkHolders['dbkeys'][] = $nt->getDBkey();
                            $this->mLinkHolders['queries'][] = $query;
                            $this->mLinkHolders['texts'][] = $prefix.$text.$inside;
                            $this->mLinkHolders['titles'][] = $nt;

                            $retVal = '<!--LINK '. ($nr-1) ."-->{$trail}";
                        }
            */
        }
        //wfProfileOut( __METHOD__ );
        //echo "<br>".$retVal; exit;
        return $retVal;
    }
    
    /**
    * From mediawiki GlobalFunctions.php
    */
    public static function wfUrlProtocols()
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
        if (is_array($wgUrlProtocols)) {
            $protocols = array();
            foreach ($wgUrlProtocols as $protocol) {
                $protocols[] = preg_quote($protocol, '/');
            }
    
            return implode('|', $protocols);
        } else {
            return $wgUrlProtocols;
        }
    }
    
    /**
    * From GlobalFunctions.php
    */
    public static function wfUrlencode($s)
    {
        $s = urlencode($s);
        //		$s = preg_replace( '/%3[Aa]/', ':', $s );
        //		$s = preg_replace( '/%2[Ff]/', '/', $s );

        return $s;
    }

    
    /**
    * Handle page GET parameter
    */
    public static function makeDbTitle($a_par)
    {
        $a_par = ilWikiUtil::removeUnsafeCharacters($a_par);
        return str_replace("_", " ", $a_par);
    }

    /**
    * Set page parameter for Url Embedding
    */
    public static function makeUrlTitle($a_par)
    {
        $a_par = ilWikiUtil::removeUnsafeCharacters($a_par);
        $a_par = str_replace(" ", "_", $a_par);
        return ilWikiUtil::wfUrlencode($a_par);
    }
    
    // from Linker.php
    public static function splitTrail($trail)
    {
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

    public static function sendNotification($a_action, $a_type, $a_wiki_ref_id, $a_page_id, $a_comment = null)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilObjDataCache = $DIC["ilObjDataCache"];
        $ilAccess = $DIC->access();

        include_once "./Services/Notification/classes/class.ilNotification.php";
        include_once "./Modules/Wiki/classes/class.ilObjWiki.php";
        include_once "./Modules/Wiki/classes/class.ilWikiPage.php";
        
        $wiki_id = $ilObjDataCache->lookupObjId($a_wiki_ref_id);
        $wiki = new ilObjWiki($a_wiki_ref_id, true);
        $page = new ilWikiPage($a_page_id);
        
        // #11138
        $ignore_threshold = ($a_action == "comment");
        
        // 1st update will be converted to new - see below
        if ($a_action == "new") {
            return;
        }

        if ($a_type == ilNotification::TYPE_WIKI_PAGE) {
            $users = ilNotification::getNotificationsForObject($a_type, $a_page_id, null, $ignore_threshold);
            $wiki_users = ilNotification::getNotificationsForObject(ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            $users = array_merge($users, $wiki_users);
            if (!sizeof($users)) {
                return;
            }

            ilNotification::updateNotificationTime(ilNotification::TYPE_WIKI_PAGE, $a_page_id, $users);
        } else {
            $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            if (!sizeof($users)) {
                return;
            }
        }
        
        ilNotification::updateNotificationTime(ilNotification::TYPE_WIKI, $wiki_id, $users, $a_page_id);
        
        // #15192 - should always be present
        include_once "./Services/Link/classes/class.ilLink.php";
        if ($a_page_id) {
            // #18804 - see ilWikiPageGUI::preview()
            $link = ilLink::_getLink("", "wiki", null, "wpage_" . $a_page_id . "_" . $a_wiki_ref_id);
        } else {
            $link = ilLink::_getLink($a_wiki_ref_id);
        }

        include_once "./Services/Mail/classes/class.ilMail.php";
        include_once "./Services/User/classes/class.ilObjUser.php";
        include_once "./Services/Language/classes/class.ilLanguageFactory.php";
        include_once("./Services/User/classes/class.ilUserUtil.php");
                
        
        // see ilBlogPostingGUI::getSnippet()
        // see ilBlogPosting::getNotificationAbstract()

        include_once "Modules/Wiki/classes/class.ilWikiPageGUI.php";
        $pgui = new ilWikiPageGUI($page->getId());
        $pgui->setRawPageContent(true);
        $pgui->setAbstractOnly(true);
        $pgui->setFileDownloadLink(".");
        $pgui->setFullscreenLink(".");
        $pgui->setSourcecodeDownloadScript(".");
        $snippet = $pgui->showPage();
        $snippet = ilPageObject::truncateHTML($snippet, 500, "...");

        // making things more readable
        $snippet = str_replace('<br/>', "\n", $snippet);
        $snippet = str_replace('<br />', "\n", $snippet);
        $snippet = str_replace('</p>', "\n", $snippet);
        $snippet = str_replace('</div>', "\n", $snippet);

        $snippet = trim(strip_tags($snippet));

        // "fake" new (to enable snippet - if any)
        $current_version = array_shift($page->getHistoryEntries());
        $current_version = $current_version["nr"];
        if (!$current_version) {
            $a_type = ilNotification::TYPE_WIKI;
            $a_action = "new";
        }
        

        foreach (array_unique($users) as $idx => $user_id) {
            if ($user_id != $ilUser->getId() &&
                $ilAccess->checkAccessOfUser($user_id, 'read', '', $a_wiki_ref_id)) {
                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('wiki');

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

                $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $mail_obj->sendMail(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array(),
                    array("system")
                );
            } else {
                unset($users[$idx]);
            }
        }
    }
}
