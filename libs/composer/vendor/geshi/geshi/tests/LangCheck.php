<?php
require_once __DIR__ . '/../src/geshi.php';

/**
 * Class LangCheck
 *
 * Runs sanity checks on the given language file
 */
class LangCheck
{

    const TYPE_NOTICE = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;
    const TYPE_FATAL = 4;

    /** @var array hold logged issues */
    protected $issues = array();

    /** @var string the file to check */
    protected $file;

    /** @var string the file content */
    protected $content;

    /** @var array the language data array */
    protected $langdata;

    /** @var array nice names with padding */
    protected $severityNames = array(
        self::TYPE_NOTICE => '[NOTICE] ',
        self::TYPE_WARNING => '[WARNING]',
        self::TYPE_ERROR => '[ERROR]  ',
        self::TYPE_FATAL => '[FATAL]  '
    );

    /**
     * LangCheck constructor.
     *
     * @param string $file the file to check
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Run all the checks on the file
     *
     * @return bool true if all checks pass
     */
    public function runChecks()
    {
        $this->issues = array();
        $this->loadFile();
        $this->checkGeneral();
        $this->checkComment();
        $this->loadLanguageData();
        $this->checkMainKeys();
        // additional checks only if no errors before
        if (!$this->issues) {
            $this->checkKeyContents();
        }

        if ($this->issues) {
            return false;
        }
        return true;
    }

    /**
     * Get all found issues as (severity, message) tuple
     *
     * @return array
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Get all found issues as formatted list
     *
     * @return string
     */
    public function getIssuesAsString()
    {
        $string = '';
        foreach ($this->issues as $issue) {
            $string .= $this->severityNames[$issue[0]] . ' ' . $issue[1] . "\n";
        }
        return $string;
    }

    /**
     * Load the file content
     *
     * Logs a fatal error if the file can't be read
     */
    protected function loadFile()
    {
        if (!is_file($this->file)) {
            $this->issue(self::TYPE_FATAL, 'The path "' . $this->file . '" does not ressemble a regular file!');
        }

        if (!is_readable($this->file)) {
            $this->issue(self::TYPE_FATAL, 'Cannot read file "' . $this->file . '"!');
        }

        $this->content = file_get_contents($this->file);
    }

    /**
     * Check some general file properties
     */
    protected function checkGeneral()
    {
        if (preg_match('/\?>(?:\r?\n|\r(?!\n)){2,}\Z/', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains trailing empty lines at EOF!');
        }
        if (preg_match('/\?>(?:\r?\n|\r(?!\n))?\Z/', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains an PHP end marker at EOF!');
        }
        if (!preg_match('/(?:\r?\n|\r(?!\n))\Z/', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains no newline at EOF!');
        }
        if (preg_match('/(\r?\n|\r(?!\n))\\1\Z/', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains trailing empty line before EOF!');
        }
        if (preg_match('/[\x20\t]$/m', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains trailing whitespace at EOL!');
        }
        if (preg_match('/\t/', $this->content)) {
            $this->issue(self::TYPE_NOTICE, 'Language file contains unescaped tabulator chars (probably for indentation)!');
        }
        if (preg_match('/^(?:    )*(?!    )(?! \*) /m', $this->content)) {
            $this->issue(self::TYPE_NOTICE, 'Language file contains irregular indentation (other than 4 spaces per indentation level)!');
        }
        if (preg_match('/\015\012/s', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains DOS line endings!');
        } else if (preg_match('/\015/s', $this->content)) {
            $this->issue(self::TYPE_ERROR, 'Language file contains MAC line endings!');
        }
    }

    /**
     * Check that the needed information is in the initial file comment
     */
    protected function checkComment()
    {
        if (!preg_match('/\/\*\*\**\s(.*?)(?:\s*\*\/)/s', $this->content, $m)) {
            $this->issue(self::TYPE_ERROR, 'Language file does not have an initial comment block!');
            return;
        }
        $comment = $m[1];

        if (!preg_match('/Author: +\S+/', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not contain a specification of an author!');
        }
        if (!preg_match('/Copyright: +\S+/', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not contain a specification of the copyright!');
        }
        if (!preg_match('/Release Version: +\d+\.\d+\.\d+\.\d+/', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not contain a specification of the release version!');
        }
        if (!preg_match('/Date Started: +\S+/s', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not contain a specification of the date it was started!');
        }
        if (!preg_match('/This file is part of GeSHi\./', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not state that it belongs to GeSHi!');
        }
        if (!preg_match('/\S+ language file for GeSHi\./', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not state that it is a language file for GeSHi!');
        }
        if (!preg_match('/GNU General Public License/', $comment)) {
            $this->issue(self::TYPE_WARNING, 'Language file does not state that it is provided under the terms of the GNU GPL!');
        }
    }

    /**
     * Load the language data
     *
     * Logs a fatal error if the file does not define it
     */
    protected function loadLanguageData()
    {
        $language_data = array();

        include $this->file;

        if (!isset($language_data)) {
            $this->issue(self::TYPE_FATAL, 'Language file does not contain a $language_data structure to check!');
        }

        if (!is_array($language_data)) {
            $this->issue(self::TYPE_FATAL, 'Language file contains a $language_data structure which is not an array!');
        }

        $this->langdata = $language_data;
        unset($language_data);

        $defined = get_defined_vars();
        if ($defined) {
            $this->issue(self::TYPE_ERROR, 'Language file seems to define other variables than $language_data!');
        }
    }

    /**
     * Check that the given key exists and has the correct type
     *
     * Logs errors and returns the result
     *
     * @param string $name
     * @param string $type the type as understood by gettype(), multiple can be joined by '|'
     * @param bool $optional is it okay when the key does not exist?
     * @return bool true if all is okay
     */
    protected function ensureKeyType($name, $type = 'array', $optional = false)
    {
        $types = explode('|', $type);

        if (!isset($this->langdata[$name])) {
            if ($optional) {
                return false;
            }
            $this->issue(self::TYPE_ERROR, "Language file contains no \$language_data['$name'] specification!");
            return false;
        }

        if (!in_array(gettype($this->langdata[$name]), $types)) {
            $this->issue(self::TYPE_ERROR, "Language file contains a \$language_data['$name'] specification which is not a $type!");
            return false;
        }

        return true;
    }

    /**
     * Check the major keys in the language array
     */
    protected function checkMainKeys()
    {
        // these just need a type check:
        $this->ensureKeyType('LANG_NAME', 'string');
        $this->ensureKeyType('COMMENT_SINGLE');
        $this->ensureKeyType('COMMENT_MULTI');
        $this->ensureKeyType('COMMENT_REGEXP', 'array', true);
        $this->ensureKeyType('QUOTEMARKS');
        $this->ensureKeyType('HARDQUOTE', 'array', true);
        $this->ensureKeyType('SYMBOLS');
        $this->ensureKeyType('OBJECT_SPLITTERS');
        $this->ensureKeyType('REGEXPS');
        $this->ensureKeyType('SCRIPT_DELIMITERS');
        $this->ensureKeyType('HIGHLIGHT_STRICT_BLOCK');
        $this->ensureKeyType('PARSER_CONTROL', 'array', true);

        // these need additional simple checks after the type checks out
        if ($this->ensureKeyType('ESCAPE_CHAR', 'string')) {
            if (1 < strlen($this->langdata['ESCAPE_CHAR'])) {
                $this->issue(self::TYPE_ERROR, 'Language file contains a $language_data[\'ESCAPE_CHAR\'] specification is not empty or exactly one char!');
            }
        }

        if ($this->ensureKeyType('CASE_KEYWORDS', 'integer')) {
            if (GESHI_CAPS_NO_CHANGE != $this->langdata['CASE_KEYWORDS'] &&
                GESHI_CAPS_LOWER != $this->langdata['CASE_KEYWORDS'] &&
                GESHI_CAPS_UPPER != $this->langdata['CASE_KEYWORDS']
            ) {
                $this->issue(self::TYPE_ERROR, 'Language file contains a $language_data[\'CASE_KEYWORDS\'] specification which is neither of GESHI_CAPS_NO_CHANGE, GESHI_CAPS_LOWER nor GESHI_CAPS_UPPER!');
            }
        }

        if ($this->ensureKeyType('KEYWORDS')) {
            foreach ($this->langdata['KEYWORDS'] as $kw_key => $kw_value) {
                if (!is_integer($kw_key)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an key '$kw_key' in \$language_data['KEYWORDS'] that is not integer!");
                } elseif (!is_array($kw_value)) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a \$language_data['KEYWORDS']['$kw_value'] structure which is not an array!");
                }
            }
        }

        if ($this->ensureKeyType('CASE_SENSITIVE')) {
            foreach ($this->langdata['CASE_SENSITIVE'] as $cs_key => $cs_value) {
                if (!is_integer($cs_key)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an key '$cs_key' in \$language_data['CASE_SENSITIVE'] that is not integer!");
                } elseif (!is_bool($cs_value)) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a Case Sensitivity specification for \$language_data['CASE_SENSITIVE']['$cs_value'] which is not a boolean!");
                }
            }
        }

        if ($this->ensureKeyType('URLS')) {
            foreach ($this->langdata['URLS'] as $url_key => $url_value) {
                if (!is_integer($url_key)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an key '$url_key' in \$language_data['URLS'] that is not integer!");
                } elseif (!is_string($url_value)) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a Documentation URL specification for \$language_data['URLS']['$url_value'] which is not a string!");
                } elseif (preg_match('#&([^;]*(=|$))#U', $url_value)) {
                    $this->issue(self::TYPE_ERROR, "Language file contains unescaped ampersands (&amp;) in \$language_data['URLS']!");
                }
            }
        }

        if ($this->ensureKeyType('OOLANG', 'boolean|integer')) {
            if (false !== $this->langdata['OOLANG'] &&
                true !== $this->langdata['OOLANG'] &&
                2 !== $this->langdata['OOLANG']
            ) {
                $this->issue(self::TYPE_ERROR, 'Language file contains a $language_data[\'OOLANG\'] specification which is neither of false, true or 2!');
            }
        }

        if ($this->ensureKeyType('STRICT_MODE_APPLIES', 'integer')) {
            if (GESHI_MAYBE != $this->langdata['STRICT_MODE_APPLIES'] &&
                GESHI_ALWAYS != $this->langdata['STRICT_MODE_APPLIES'] &&
                GESHI_NEVER != $this->langdata['STRICT_MODE_APPLIES']
            ) {
                $this->issue(self::TYPE_ERROR, 'Language file contains a $language_data[\'STRICT_MODE_APPLIES\'] specification which is neither of GESHI_MAYBE, GESHI_ALWAYS nor GESHI_NEVER!');
            }
        }

        if ($this->ensureKeyType('TAB_WIDTH', 'integer', true)) {
            if (1 > $this->langdata['TAB_WIDTH']) {
                $this->issue(self::TYPE_ERROR, 'Language file contains a $language_data[\'TAB_WIDTH\'] specification which is less than 1!');
            }
        }

        if ($this->ensureKeyType('STYLES')) {
            $style_arrays = array('KEYWORDS', 'COMMENTS', 'ESCAPE_CHAR',
                'BRACKETS', 'STRINGS', 'NUMBERS', 'METHODS', 'SYMBOLS',
                'REGEXPS', 'SCRIPT');
            foreach ($style_arrays as $style_kind) {
                if (!isset($this->langdata['STYLES'][$style_kind])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains no \$language_data['STYLES']['$style_kind'] structure to check!");
                } elseif (!is_array($this->langdata['STYLES'][$style_kind])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a \$language_data['STYLES\']['$style_kind'] structure which is not an array!");
                } else {
                    foreach ($this->langdata['STYLES'][$style_kind] as $sk_key => $sk_value) {
                        if (!is_int($sk_key) && ('COMMENTS' != $style_kind && 'MULTI' != $sk_key)
                            && !(('STRINGS' == $style_kind || 'ESCAPE_CHAR' == $style_kind) && 'HARD' == $sk_key)
                        ) {
                            $this->issue(self::TYPE_WARNING, "Language file contains an key '$sk_key' in \$language_data['STYLES']['$style_kind'] that is not integer!");
                        } elseif (!is_string($sk_value)) {
                            $this->issue(self::TYPE_WARNING, "Language file contains a CSS specification for \$language_data['STYLES']['$style_kind'][$key] which is not a string!");
                        }
                    }
                }
            }
        }
    }

    /**
     * Check the keywords are sane
     *
     * @fixme split this into some sane chunks, maybe generalize
     */
    protected function checkKeyContents()
    {
        foreach ($this->langdata['KEYWORDS'] as $key => $keywords) {
            if (!isset($this->langdata['CASE_SENSITIVE'][$key])) {
                $this->issue(self::TYPE_ERROR, "Language file contains no \$language_data['CASE_SENSITIVE'] specification for keyword group $key!");
            }
            if (!isset($this->langdata['URLS'][$key])) {
                $this->issue(self::TYPE_ERROR, "Language file contains no \$language_data['URLS'] specification for keyword group $key!");
            }
            if (empty($keywords)) {
                $this->issue(self::TYPE_WARNING, "Language file contains an empty keyword list in \$language_data['KEYWORDS'] for group $key!");
            }
            foreach ($keywords as $id => $kw) {
                if (!is_string($kw)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an non-string entry at \$language_data['KEYWORDS'][$key][$id]!");
                } elseif (!strlen($kw)) {
                    $this->issue(self::TYPE_ERROR, "Language file contains an empty string entry at \$language_data['KEYWORDS'][$key][$id]!");
                } elseif (preg_match('/^([\(\)\{\}\[\]\^=.,:;\-+\*\/%\$\"\'\?]|&[\w#]\w*;)+$/i', $kw)) {
                    $this->issue(self::TYPE_NOTICE, "Language file contains an keyword ('$kw') at \$language_data['KEYWORDS'][$key][$id] which seems to be better suited for the symbols section!");
                }
            }
            if (isset($this->langdata['CASE_SENSITIVE'][$key]) && !$this->langdata['CASE_SENSITIVE'][$key]) {
                array_walk($keywords, array($this, 'strtolower'));
            }
            if (count($keywords) != count(array_unique($keywords))) {
                $kw_diffs = array_count_values($keywords);
                foreach ($kw_diffs as $kw => $kw_count) {
                    if ($kw_count > 1) {
                        $this->issue(self::TYPE_WARNING, "Language file contains per-group duplicate keyword '$kw' in \$language_data['KEYWORDS'][$key]!");
                    }
                }
            }
        }

        $disallowed_before = '(?<![a-zA-Z0-9\$_\|\#;>|^&';
        $disallowed_after = '(?![a-zA-Z0-9_\|%\\-&;';

        foreach ($this->langdata['KEYWORDS'] as $key => $keywords) {
            foreach ($this->langdata['KEYWORDS'] as $key2 => $keywords2) {
                if ($key2 <= $key) {
                    continue;
                }
                $kw_diffs = array_intersect($keywords, $keywords2);
                foreach ($kw_diffs as $kw) {
                    if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS'])) {
                        //Check the precondition\post-cindition for the involved keyword groups
                        $g1_pre = $disallowed_before;
                        $g2_pre = $disallowed_before;
                        $g1_post = $disallowed_after;
                        $g2_post = $disallowed_after;
                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_BEFORE'])) {
                            $g1_pre = $this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_BEFORE'];
                            $g2_pre = $this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_BEFORE'];
                        }
                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_AFTER'])) {
                            $g1_post = $this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_AFTER'];
                            $g2_post = $this->langdata['PARSER_CONTROL']['KEYWORDS']['DISALLOWED_AFTER'];
                        }

                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS'][$key]['DISALLOWED_BEFORE'])) {
                            $g1_pre = $this->langdata['PARSER_CONTROL']['KEYWORDS'][$key]['DISALLOWED_BEFORE'];
                        }
                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS'][$key]['DISALLOWED_AFTER'])) {
                            $g1_post = $this->langdata['PARSER_CONTROL']['KEYWORDS'][$key]['DISALLOWED_AFTER'];
                        }

                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS'][$key2]['DISALLOWED_BEFORE'])) {
                            $g2_pre = $this->langdata['PARSER_CONTROL']['KEYWORDS'][$key2]['DISALLOWED_BEFORE'];
                        }
                        if (isset($this->langdata['PARSER_CONTROL']['KEYWORDS'][$key2]['DISALLOWED_AFTER'])) {
                            $g2_post = $this->langdata['PARSER_CONTROL']['KEYWORDS'][$key2]['DISALLOWED_AFTER'];
                        }

                        if ($g1_pre != $g2_pre || $g1_post != $g2_post) {
                            continue;
                        }
                    }
                    $this->issue(self::TYPE_WARNING, "Language file contains cross-group duplicate keyword '$kw' in \$language_data['KEYWORDS'][$key] and \$language_data['KEYWORDS'][$key2]!");
                }
            }
        }
        foreach ($this->langdata['CASE_SENSITIVE'] as $key => $keywords) {
            if (!isset($this->langdata['KEYWORDS'][$key]) && $key != GESHI_COMMENTS) {
                $this->issue(self::TYPE_WARNING, "Language file contains an superfluous \$language_data['CASE_SENSITIVE'] specification for non-existing keyword group $key!");
            }
        }
        foreach ($this->langdata['URLS'] as $key => $keywords) {
            if (!isset($this->langdata['KEYWORDS'][$key])) {
                $this->issue(self::TYPE_WARNING, "Language file contains an superfluous \$language_data['URLS'] specification for non-existing keyword group $key!");
            }
        }
        foreach ($this->langdata['STYLES']['KEYWORDS'] as $key => $keywords) {
            if (!isset($this->langdata['KEYWORDS'][$key])) {
                $this->issue(self::TYPE_WARNING, "Language file contains an superfluous \$language_data['STYLES']['KEYWORDS'] specification for non-existing keyword group $key!");
            }
        }

        foreach ($this->langdata['COMMENT_SINGLE'] as $ck => $cv) {
            if (!is_int($ck)) {
                $this->issue(self::TYPE_WARNING, "Language file contains an key '$ck' in \$language_data['COMMENT_SINGLE'] that is not integer!");
            }
            if (!is_string($cv)) {
                $this->issue(self::TYPE_WARNING, "Language file contains an non-string entry at \$language_data['COMMENT_SINGLE'][$ck]!");
            }
            if (!isset($this->langdata['STYLES']['COMMENTS'][$ck])) {
                $this->issue(self::TYPE_WARNING, "Language file contains no \$language_data['STYLES']['COMMENTS'] specification for comment group $ck!");
            }
        }
        if (isset($this->langdata['COMMENT_REGEXP'])) {
            foreach ($this->langdata['COMMENT_REGEXP'] as $ck => $cv) {
                if (!is_int($ck)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an key '$ck' in \$language_data['COMMENT_REGEXP'] that is not integer!");
                }
                if (!is_string($cv)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an non-string entry at \$language_data['COMMENT_REGEXP'][$ck]!");
                }
                if (!isset($this->langdata['STYLES']['COMMENTS'][$ck])) {
                    $this->issue(self::TYPE_WARNING, "Language file contains no \$language_data['STYLES']['COMMENTS'] specification for comment group $ck!");
                }
            }
        }
        foreach ($this->langdata['STYLES']['COMMENTS'] as $ck => $cv) {
            if ($ck != 'MULTI' && !isset($this->langdata['COMMENT_SINGLE'][$ck]) &&
                !isset($this->langdata['COMMENT_REGEXP'][$ck])
            ) {
                $this->issue(self::TYPE_NOTICE, "Language file contains an superfluous \$language_data['STYLES']['COMMENTS'] specification for Single Line or Regular-Expression Comment key $ck!");
            }
        }
        if (isset($this->langdata['STYLES']['STRINGS']['HARD'])) {
            if (empty($this->langdata['HARDQUOTE'])) {
                $this->issue(self::TYPE_NOTICE, "Language file contains superfluous \$language_data['STYLES']['STRINGS'] specification for key 'HARD', but no 'HARDQUOTE's are defined!");
            }
            unset($this->langdata['STYLES']['STRINGS']['HARD']);
        }
        foreach ($this->langdata['STYLES']['STRINGS'] as $sk => $sv) {
            if ($sk && !isset($this->langdata['QUOTEMARKS'][$sk])) {
                $this->issue(self::TYPE_NOTICE, "Language file contains an superfluous \$language_data['STYLES']['STRINGS'] specification for non-existing quotemark key $sk!");
            }
        }

        foreach ($this->langdata['REGEXPS'] as $rk => $rv) {
            if (!is_int($rk)) {
                $this->issue(self::TYPE_WARNING, "Language file contains an key '$rk' in \$language_data['REGEXPS'] that is not integer!");
            }
            if (is_string($rv)) {
                //Check for unmasked / in regular expressions ...
                if (empty($rv)) {
                    $this->issue(self::TYPE_WARNING, "Language file contains an empty regular expression at \$language_data['REGEXPS'][$rk]!");
                } else {
                    if (preg_match("/(?<!\\\\)\//s", $rv)) {
                        $this->issue(self::TYPE_WARNING, "Language file contains a regular expression with an unmasked / character at \$language_data['REGEXPS'][$rk]!");
                    } elseif (preg_match("/(?<!<)(\\\\\\\\)*\\\\\|(?!>)/s", $rv)) {
                        $this->issue(self::TYPE_WARNING, "Language file contains a regular expression with an unescaped match for a pipe character '|' which needs escaping as '&lt;PIPE&gt;' instead at \$language_data['REGEXPS'][$rk]!");
                    }
                }
            } elseif (is_array($rv)) {
                if (!isset($rv[GESHI_SEARCH])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains no GESHI_SEARCH entry in extended regular expression at \$language_data['REGEXPS'][$rk]!");
                } elseif (!is_string($rv[GESHI_SEARCH])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a GESHI_SEARCH entry in extended regular expression at \$language_data['REGEXPS'][$rk] which is not a string!");
                } else {
                    if (preg_match("/(?<!\\\\)\//s", $rv[GESHI_SEARCH])) {
                        $this->issue(self::TYPE_WARNING, "Language file contains a regular expression with an unmasked / character at \$language_data['REGEXPS'][$rk]!");
                    } elseif (preg_match("/(?<!<)(\\\\\\\\)*\\\\\|(?!>)/s", $rv[GESHI_SEARCH])) {
                        $this->issue(self::TYPE_WARNING, "Language file contains a regular expression with an unescaped match for a pipe character '|' which needs escaping as '&lt;PIPE&gt;' instead at \$language_data['REGEXPS'][$rk]!");
                    }
                }
                if (!isset($rv[GESHI_REPLACE])) {
                    $this->issue(self::TYPE_WARNING, "Language file contains no GESHI_REPLACE entry in extended regular expression at \$language_data['REGEXPS'][$rk]!");
                } elseif (!is_string($rv[GESHI_REPLACE])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a GESHI_REPLACE entry in extended regular expression at \$language_data['REGEXPS'][$rk] which is not a string!");
                }
                if (!isset($rv[GESHI_MODIFIERS])) {
                    $this->issue(self::TYPE_WARNING, "Language file contains no GESHI_MODIFIERS entry in extended regular expression at \$language_data['REGEXPS'][$rk]!");
                } elseif (!is_string($rv[GESHI_MODIFIERS])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a GESHI_MODIFIERS entry in extended regular expression at \$language_data['REGEXPS'][$rk] which is not a string!");
                }
                if (!isset($rv[GESHI_BEFORE])) {
                    $this->issue(self::TYPE_WARNING, "Language file contains no GESHI_BEFORE entry in extended regular expression at \$language_data['REGEXPS'][$rk]!");
                } elseif (!is_string($rv[GESHI_BEFORE])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a GESHI_BEFORE entry in extended regular expression at \$language_data['REGEXPS'][$rk] which is not a string!");
                }
                if (!isset($rv[GESHI_AFTER])) {
                    $this->issue(self::TYPE_WARNING, "Language file contains no GESHI_AFTER entry in extended regular expression at \$language_data['REGEXPS'][$rk]!");
                } elseif (!is_string($rv[GESHI_AFTER])) {
                    $this->issue(self::TYPE_ERROR, "Language file contains a GESHI_AFTER entry in extended regular expression at \$language_data['REGEXPS'][$rk] which is not a string!");
                }
            } else {
                $this->issue(self::TYPE_WARNING, "Language file contains an non-string and non-array entry at \$language_data['REGEXPS'][$rk]!");
            }
            if (!isset($this->langdata['STYLES']['REGEXPS'][$rk])) {
                $this->issue(self::TYPE_WARNING, "Language file contains no \$language_data['STYLES']['REGEXPS'] specification for regexp group $rk!");
            }
        }
        foreach ($this->langdata['STYLES']['REGEXPS'] as $rk => $rv) {
            if (!isset($this->langdata['REGEXPS'][$rk])) {
                $this->issue(self::TYPE_NOTICE, "Language file contains an superfluous \$language_data['STYLES']['REGEXPS'] specification for regexp key $rk!");
            }
        }
    }


    /**
     * log an issue
     *
     * @param int $type
     * @param string $msg
     * @throws Exception
     */
    protected function issue($type, $msg)
    {
        $this->issues[] = array($type, $msg);

        // abort all processing on fatal errors
        if ($type == self::TYPE_FATAL) {
            throw new Exception($msg);
        }
    }

    public function strtolower(&$value)
    {
        $value = strtolower($value);
    }
}
