<?php

// patched: alex, 30.4.2019: Added missing defines

define('NS_MAIN', "nsmain");
define('NS_SPECIAL', "nsspecial");

define('GAID_FOR_UPDATE', 1);

# Title::newFromTitle maintains a cache to avoid
# expensive re-normalization of commonly used titles.
# On a batch operation this can become a memory leak
# if not bounded. After hitting this many titles,
# reset the cache.
define('MW_TITLECACHE_MAX', 1000);

# Constants for pr_cascade bitfield
define('CASCADE', 1);

/**
 * Title class
 * - Represents a title, which may contain an interwiki designation or namespace
 * - Can fetch various kinds of data from the database, albeit inefficiently.
 *
 */
class Title
{
    /**
     * Static cache variables
     */
    private static $titleCache = array();
    private static $interwikiCache = array();
    /**
     * @var false
     */
    protected bool $mOldRestrictions;

    /**
     * All member variables should be considered private
     * Please use the accessor functions
     */

    /**#@+
    * @private
    */

    public $mTextform;           	# Text form (spaces not underscores) of the main part
    public $mUrlform;            	# URL-encoded form of the main part
    public $mDbkeyform;          	# Main part with underscores
    public $mNamespace;          	# Namespace index, i.e. one of the NS_xxxx constants
    public $mInterwiki;          	# Interwiki prefix (or null string)
    public $mFragment;           	# Title fragment (i.e. the bit after the #)
    public $mArticleID;          	# Article ID, fetched from the link cache on demand
    public $mLatestID;         	# ID of most recent revision
    public $mRestrictions;       	# Array of groups allowed to edit this article
    public $mCascadeRestriction;	# Cascade restrictions on this page to included templates and images?
    public $mRestrictionsExpiry;	# When do the restrictions on this page expire?
    public $mHasCascadingRestrictions;	# Are cascading restrictions in effect on this page?
    public $mCascadeRestrictionSources;# Where are the cascading restrictions coming from on this page?
    public $mRestrictionsLoaded; 	# Boolean for initialisation on demand
    public $mPrefixedText;       	# Text form including namespace/interwiki, initialised on demand
    public $mDefaultNamespace;   	# Namespace index when there is no namespace
                                # Zero except in {{transclusion}} tags
    public $mWatched;      		# Is $wgUser watching this page? NULL if unfilled, accessed through userIsWatching()
    /**#@-*/


    /**
     * Constructor
     * @private
     */
    /* private */ public function __construct()
    {
        $this->mInterwiki = $this->mUrlform =
        $this->mTextform = $this->mDbkeyform = '';
        $this->mArticleID = -1;
        $this->mNamespace = NS_MAIN;
        $this->mRestrictionsLoaded = false;
        $this->mRestrictions = array();
        # Dont change the following, NS_MAIN is hardcoded in several place
        # See bug #696
        $this->mDefaultNamespace = NS_MAIN;
        $this->mWatched = null;
        $this->mLatestID = false;
        $this->mOldRestrictions = false;
    }


    /**
     * Create a new Title from text, such as what one would
     * find in a link. Decodes any HTML entities in the text.
     *
     * @param string $text the link text; spaces, prefixes,
     *	and an initial ':' indicating the main namespace
     *	are accepted
     * @param int $defaultNamespace the namespace to use if
     * 	none is specified by a prefix
     * @return Title the new object, or NULL on an error
     */
    public static function newFromText($text, $defaultNamespace = NS_MAIN)
    {
        /**
         * Wiki pages often contain multiple links to the same page.
         * Title normalization and parsing can become expensive on
         * pages with many links, so we can save a little time by
         * caching them.
         *
         * In theory these are value objects and won't get changed...
         */
        if ($defaultNamespace == NS_MAIN && isset(Title::$titleCache[$text])) {
            return Title::$titleCache[$text];
        }

        /**
         * Convert things like &eacute; &#257; or &#x3017; into real text...
         */
        $filteredText = Sanitizer::decodeCharReferences($text);

        $t = new Title();
        $t->mDbkeyform = str_replace(' ', '_', $filteredText);
        $t->mDefaultNamespace = $defaultNamespace;

        static $cachedcount = 0 ;
        if ($t->secureAndSplit()) {
            if ($defaultNamespace == NS_MAIN) {
                if ($cachedcount >= MW_TITLECACHE_MAX) {
                    # Avoid memory leaks on mass operations...
                    Title::$titleCache = array();
                    $cachedcount = 0;
                }
                $cachedcount++;
                Title::$titleCache[$text] = &$t;
            }
            return $t;
        } else {
            $ret = null;
            return $ret;
        }
    }


    #----------------------------------------------------------------------------
    #	Static functions
    #----------------------------------------------------------------------------


    /**
     * Get a regex character class describing the legal characters in a link
     * @return string the list of characters, not delimited
     */
    public static function legalChars()
    {
        global $wgLegalTitleChars;

        $wgLegalTitleChars = " %!\"$&'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+";

        return $wgLegalTitleChars;
    }


    /**
     * Returns the URL associated with an interwiki prefix
     * @param string $key the interwiki prefix (e.g. "MeatBall")
     * @return string the associated URL, containing "$1", which should be
     * 	replaced by an article title
     * @static (arguably)
     */
    public function getInterwikiLink($key)
    {
        return "";
    }

    #----------------------------------------------------------------------------
    #	Other stuff
    #----------------------------------------------------------------------------

    /** Simple accessors */
    /**
     * Get the text form (spaces not underscores) of the main part
     * @return string
     */
    public function getText()
    {
        return $this->mTextform;
    }

    /**
     * Get the main part with underscores
     * @return string
     */
    public function getDBkey()
    {
        return $this->mDbkeyform;
    }
    /**
     * Get the namespace index, i.e. one of the NS_xxxx constants
     * @return int
     */
    public function getNamespace()
    {
        return $this->mNamespace;
    }
    /**
     * Get the namespace text
     * @return string
     */
    public function getNsText()
    {
        global $wgContLang, $wgCanonicalNamespaceNames;

        if ('' != $this->mInterwiki) {
            // This probably shouldn't even happen. ohh man, oh yuck.
            // But for interwiki transclusion it sometimes does.
            // Shit. Shit shit shit.
            //
            // Use the canonical namespaces if possible to try to
            // resolve a foreign namespace.
            if (isset($wgCanonicalNamespaceNames[$this->mNamespace])) {
                return $wgCanonicalNamespaceNames[$this->mNamespace];
            }
        }
        return $wgContLang->getNsText($this->mNamespace);
    }
    /**
     * Get the interwiki prefix (or null string)
     * @return string
     */
    public function getInterwiki()
    {
        return $this->mInterwiki;
    }
    /**
     * Get the Title fragment (i.e. the bit after the #) in text form
     * @return string
     */
    public function getFragment()
    {
        return $this->mFragment;
    }

    /**
     * Prefix some arbitrary text with the namespace or interwiki prefix
     * of this object
     *
     * @param string $name the text
     * @return string the prefixed text
     * @private
     */
    /* private */ public function prefix($name)
    {
        $p = '';
        if ('' != $this->mInterwiki) {
            $p = $this->mInterwiki . ':';
        }
        if (0 != $this->mNamespace) {
            $p .= $this->getNsText() . ':';
        }
        return $p . $name;
    }

    /**
     * Secure and split - main initialisation function for this object
     *
     * Assumes that mDbkeyform has been set, and is urldecoded
     * and uses underscores, but not otherwise munged.  This function
     * removes illegal characters, splits off the interwiki and
     * namespace prefixes, sets the other forms, and canonicalizes
     * everything.
     * @return bool true on success
     */
    private function secureAndSplit()
    {
        global $wgContLang, $wgLocalInterwiki, $wgCapitalLinks;

        # Initialisation
        static $rxTc = false;
        if (!$rxTc) {
            # % is needed as well
            $rxTc = '/[^' . Title::legalChars() . ']|%[0-9A-Fa-f]{2}/S';
        }

        $this->mInterwiki = $this->mFragment = '';
        $this->mNamespace = $this->mDefaultNamespace; # Usually NS_MAIN

        $dbkey = $this->mDbkeyform;

        # Strip Unicode bidi override characters.
        # Sometimes they slip into cut-n-pasted page titles, where the
        # override chars get included in list displays.
        $dbkey = str_replace("\xE2\x80\x8E", '', $dbkey); // 200E LEFT-TO-RIGHT MARK
        $dbkey = str_replace("\xE2\x80\x8F", '', $dbkey); // 200F RIGHT-TO-LEFT MARK

        # Clean up whitespace
        #
        $dbkey = preg_replace('/[ _]+/', '_', $dbkey);
        $dbkey = trim($dbkey, '_');

        if ('' == $dbkey) {
            return false;
        }

        if (false !== strpos($dbkey, UTF8_REPLACEMENT)) {
            # Contained illegal UTF-8 sequences or forbidden Unicode chars.
            return false;
        }

        $this->mDbkeyform = $dbkey;

        # Initial colon indicates main namespace rather than specified default
        # but should not create invalid {ns,title} pairs such as {0,Project:Foo}
        if (':' == $dbkey[0]) {
            $this->mNamespace = NS_MAIN;
            $dbkey = substr($dbkey, 1); # remove the colon but continue processing
            $dbkey = trim($dbkey, '_'); # remove any subsequent whitespace
        }

        # Namespace or interwiki prefix
        $firstPass = true;
        do {
            $m = array();
            if (preg_match("/^(.+?)_*:_*(.*)$/S", $dbkey, $m)) {
                $p = $m[1];
                if ($ns = $wgContLang->getNsIndex($p)) {
                    # Ordinary namespace
                    $dbkey = $m[2];
                    $this->mNamespace = $ns;
                } elseif ($this->getInterwikiLink($p)) {
                    if (!$firstPass) {
                        # Can't make a local interwiki link to an interwiki link.
                        # That's just crazy!
                        return false;
                    }

                    # Interwiki link
                    $dbkey = $m[2];
                    $this->mInterwiki = $wgContLang->lc($p);

                    # Redundant interwiki prefix to the local wiki
                    if (0 == strcasecmp($this->mInterwiki, $wgLocalInterwiki)) {
                        if ($dbkey == '') {
                            # Can't have an empty self-link
                            return false;
                        }
                        $this->mInterwiki = '';
                        $firstPass = false;
                        # Do another namespace split...
                        continue;
                    }

                    # If there's an initial colon after the interwiki, that also
                    # resets the default namespace
                    if ($dbkey !== '' && $dbkey[0] == ':') {
                        $this->mNamespace = NS_MAIN;
                        $dbkey = substr($dbkey, 1);
                    }
                }
                # If there's no recognized interwiki or namespace,
                # then let the colon expression be part of the title.
            }
            break;
        } while (true);

        # We already know that some pages won't be in the database!
        #
        if ('' != $this->mInterwiki || NS_SPECIAL == $this->mNamespace) {
            $this->mArticleID = 0;
        }
        $fragment = strstr($dbkey, '#');
        if (false !== $fragment) {
            $this->setFragment($fragment);
            $dbkey = substr($dbkey, 0, strlen($dbkey) - strlen($fragment));
            # remove whitespace again: prevents "Foo_bar_#"
            # becoming "Foo_bar_"
            $dbkey = preg_replace('/_*$/', '', $dbkey);
        }

        # Reject illegal characters.
        #
        if (preg_match($rxTc, $dbkey)) {
            return false;
        }

        /**
         * Pages with "/./" or "/../" appearing in the URLs will
         * often be unreachable due to the way web browsers deal
         * with 'relative' URLs. Forbid them explicitly.
         */
        if (strpos($dbkey, '.') !== false &&
             ($dbkey === '.' || $dbkey === '..' ||
               strpos($dbkey, './') === 0 ||
               strpos($dbkey, '../') === 0 ||
               strpos($dbkey, '/./') !== false ||
               strpos($dbkey, '/../') !== false)) {
            return false;
        }

        /**
         * Magic tilde sequences? Nu-uh!
         */
        if (strpos($dbkey, '~~~') !== false) {
            return false;
        }

        /**
         * Limit the size of titles to 255 bytes.
         * This is typically the size of the underlying database field.
         * We make an exception for special pages, which don't need to be stored
         * in the database, and may edge over 255 bytes due to subpage syntax
         * for long titles, e.g. [[Special:Block/Long name]]
         */
        if (($this->mNamespace != NS_SPECIAL && strlen($dbkey) > 255) ||
          strlen($dbkey) > 512) {
            return false;
        }

        /**
         * Normally, all wiki links are forced to have
         * an initial capital letter so [[foo]] and [[Foo]]
         * point to the same place.
         *
         * Don't force it for interwikis, since the other
         * site might be case-sensitive.
         */
        if ($wgCapitalLinks && $this->mInterwiki == '') {
            $dbkey = $wgContLang->ucfirst($dbkey);
        }

        /**
         * Can't make a link to a namespace alone...
         * "empty" local links can only be self-links
         * with a fragment identifier.
         */
        if ($dbkey == '' &&
            $this->mInterwiki == '' &&
            $this->mNamespace != NS_MAIN) {
            return false;
        }

        // Any remaining initial :s are illegal.
        if ($dbkey !== '' && ':' == $dbkey[0]) {
            return false;
        }

        # Fill fields
        $this->mDbkeyform = $dbkey;
        $this->mUrlform = ilWikiUtil::wfUrlencode($dbkey);

        $this->mTextform = str_replace('_', ' ', $dbkey);

        return true;
    }

    /**
     * Set the fragment for this title
     * This is kind of bad, since except for this rarely-used function, Title objects
     * are immutable. The reason this is here is because it's better than setting the
     * members directly, which is what Linker::formatComment was doing previously.
     *
     * @param string $fragment text
     * @todo clarify whether access is supposed to be public (was marked as "kind of public")
     */
    public function setFragment($fragment)
    {
        $this->mFragment = str_replace('_', ' ', substr($fragment, 1));
    }

    /**
     * Compare with another title.
     *
     * @param Title $title
     * @return bool
     */
    public function equals($title)
    {
        // Note: === is necessary for proper matching of number-like titles.
        return $this->getInterwiki() === $title->getInterwiki()
            && $this->getNamespace() == $title->getNamespace()
            && $this->getDBkey() === $title->getDBkey();
    }
}
