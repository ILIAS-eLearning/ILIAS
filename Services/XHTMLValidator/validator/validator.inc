<?php
/* 
   +----------------------------------------------------------------------+
   | HTML/XML Validator                                                   |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 Nuno Lopes                                   |
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   |
   |                                                                      |
   | This program is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      |
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License along with this library; if not, write to the Free Software  |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA             |
   | 02111-1307  USA.                                                     |
   +----------------------------------------------------------------------+
   |                     http://validator.aborla.net/                     |
   +----------------------------------------------------------------------+

     $Id$

vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
*/

//require './config.inc';

// include bundled Text_Diff PEAR package
//require './Text_Diff/Diff.php';
//require './Text_Diff/Renderer.php';
//require './Text_Diff/unified.php';


class validator {

  /* limit for a file download - used by parse_url() and for file uploads
	Don't forget to modify this setting also in the .htaccess file
  */
    const FILE_LIMIT = 50000;
    public  $lang = '';

    private $tidy;
    private $charset;
    private $config;
    private $errors = '';
    private $html;
    private $spell = array();


    function __construct($config = array()) {

        /* set the config options and perform sanity checks */
        foreach ($GLOBALS['opts'] as $k => $v) {

            if (isset($config[$k])) {

                switch($v['type']) {
                    case 'bool':
                        $this->config[$k] = $config[$k] ? 1 : 0;
                        break;

                    case 'number':
                        $config[$k] = (int) $config[$k];
                        $this->config[$k] = ($config[$k] <= $v['max'] && $config[$k] >= $v['min']) ? $config[$k] : $v['default'];
                        break;

                    case 'string':
                        $this->config[$k] = strlen($config[$k]) <= $v['length'] ? $config[$k] : substr($config[$k], 0, $v['length']-3) . '...';
                        break;

                    case 'select':
                        $this->config[$k] = isset($v['values'][$config[$k]]) ? $config[$k] : $v['default'];
                        break;
                }

            /* not defined, revert to default */
            } else {
                $this->config[$k] = $v['default'];
            }
        }

    } //end of __construct()



    /* Return the configuration option to put in a <form> */
    static function html_opts($aditional = '') {
        global $langs, $message;

        $return = '<fieldset><legend>' . $GLOBALS['message'][0] . "</legend>\n" . $aditional;

        foreach ($GLOBALS['opts'] as $k => $v) {

            /* lock the explanation tip to 90 chars */
            if (isset($v['explain'][91])) {
                $title = iconv_substr($v['explain'], 0, 87, 'UTF-8') . '...';
            } else {
                $title = $v['explain'];
            }

            $return .= "<label title=\"$title\">\n$v[name]: ";

            switch($v['type']) {
                case 'bool':
                    $return .= "<input type=\"checkbox\" name=\"$k\" value=\"1\" />";
                    break;

                case 'number':
                    $return .= '<input type="text" name="' . $k . '" size="5" value="' . $v['default'] . '" />';
                    break;

                case 'string':
                    $return .= '<input type="text" name="' . $k . '" size="40" value="' . $v['default'] . '" />';
                    break;

                case 'select':
                    $return .= "<select name=\"$k\">\n";

                    foreach ($v['values'] as $key => $val) {
                        if ($val[0] == '$')
                            eval('$val='.$val.';'); // map the var name to its value

                        $return .= "<option value=\"$key\"" . ($v['default'] == $key ? ' selected="selected"' :'') .">$val</option>\n";
                    }
                    $return .= '</select>';
                    break;
            }

            $return .= "<br />\n</label>\n";
        }

        return $return . '</fieldset>';

    } //end of html_opts()



    /* Parse an URL */
    function parse_url($url) {

        /* Check for invalid/local URLs */
        if (!preg_match('@^https?://[^.]+\..+$@', $url) ||
            preg_match('@^https?://(?:(?:10\.\d{1,3}|172\.16|192\.168)\.\d{1,3}\.\d{1,3}|127\.0\.0\.1)@', $url)) {
            $this->_error(0, htmlspecialchars($url));
            return false;
        }

        if (!$fp = @fopen($url, 'r')) {
            $this->_error(1, htmlspecialchars($url));
            return false;
        }

        $html = @stream_get_contents($fp, self::FILE_LIMIT);
        $meta_data = stream_get_meta_data($fp);
        fclose($fp);

        /* File too big */
        if (!$meta_data['eof']) {
            $this->_error(6, self::FILE_LIMIT);
        }

        /* Timeout */
        if ($meta_data['timed_out']) {
            $this->_error(7);
        }

        /* reverse and filter the array, to include just the last connection */
        $headers_array = array_reverse($http_response_header);
        $i = -1;
        $headers = '';

        while(strncmp($headers_array[++$i], 'HTTP/', 5) != 0) {
            $headers .= $headers_array[$i] . "\n";
        }

        $this->_check_encoding($headers, true);

        return $this->parse_string($html);

    } //end of parse_url()



    /* Parse a string */
    function parse_string($html) {
        $html = trim($html);

        if ($html == '') {
            $this->_error(2);
            return false;
        }

        /* no charset found, revert to default */
        if (!$this->charset && !$this->_check_encoding($html)) {
            $this->_error(3);
            $this->charset = 'latin1';
        }

        /* special case: choose input/output mode */
        if (isset($this->config['output'])) {

            switch($this->config['output']) {
                case 'html':
                    $this->config['output-html'] = 1;
                    break;

                case 'xml':
                    $this->config['output-xml'] = 1;
                    $this->config['input-xml']  = 1;
                    break;

                default:
                    $this->config['output-xhtml'] = 1;
            }
        }

        if ($this->config['spellcheck'] != 'no')
            $this->lang = $this->config['spellcheck'];

        unset($this->config['charset'], $this->config['output'], $this->config['spellcheck']);

        // store the html for later use (as UTF-8)
		// removed unnecessary conversion
        //$this->html = iconv($this->readable_charset(), 'UTF-8//IGNORE', $html);
		$this->html = $html;

        $this->tidy = tidy_parse_string($html, $this->config, $this->tidy_charset());
        $this->tidy->cleanRepair();
        $result = $this->tidy->diagnose();

		// dont do spell check
        //if ($this->lang)
        //   $this->_spell_check();

        return $result;

    } //end of parse_string()



    /* return tidy charset */
    function tidy_charset() {

        if (isset($GLOBALS['charsets_tidy'][$this->charset])) {
            return $GLOBALS['charsets_tidy'][$this->charset];
        }

        return $this->charset;

    } // end of tidy_charset()



    /* return readable charset */
    function readable_charset() {

        if (isset($GLOBALS['charsets_readable'][$this->charset])) {
            return $GLOBALS['charsets_readable'][$this->charset];
        }

        return $GLOBALS['opts']['charset']['values'][$this->charset];

    } // end of readable_charset()



    /* Return the detected HTML version */
    function detected_version() {

        if ($this->tidy instanceof tidy) {
            if (preg_match('/^Info: Document content looks like (.+)$/m', $this->tidy->errorBuffer, $match)) {

                return $match[1];

            /* this can happen if the document has too many errors */
            } else {
               return false;
            }

        /* there is no tidy resource */
        } else {
            $this->_error(4);
            return false;
        }

    } //end of detected_version()



    /* Return the full repaired source */
    function repaired_source() {

        if ($this->tidy instanceof tidy) {
			// removed unnecessary conversion
            //return iconv($this->readable_charset(), 'UTF-8//IGNORE', $this->tidy);
			return $this->tidy;

        /* there is no tidy resource */
        } else {
            $this->_error(4);
            return false;
        }

    } //end of repaired_source()



    /* Return diff with the repaired source */
    function repaired_diff() {

        if ($this->tidy instanceof tidy) {
            $diff = new Text_Diff(preg_split('/(?:\r\n?|\n)/', $this->html), explode("\n", $this->repaired_source()));
            $renderer = new Text_Diff_Renderer_unified();
            $diff = htmlspecialchars($renderer->render($diff), ENT_QUOTES, 'UTF-8');
            $text = '';

            foreach (explode("\n", $diff) as $line) {

                switch(isset($line[0]) ? $line[0] : '') {
                    case '-':
                        $text .= '<span class="diffsub">' . $line . "</span><br />\n";
                        break;
                    case '+':
                        $text .= '<span class="diffadd">' . $line . "</span><br />\n";
                        break;
                    case '@':
                        if (isset($line[1]) && $line[1] == '@' && isset($line[2]) && $line[2] == ' ') {
                            $text .= '<span class="diffjmp">' . $line . "</span><br />\n";
                            break;
                        }
                    default:
                        $text .= "$line<br />\n";
                }
            }

            return $text;


        /* there is no tidy resource */
        } else {
            $this->_error(4);
            return false;
        }

    } //end of repaired_diff()



    /* performs a spell checking */
    private function _spell_check() {
// this can later be used to add a wordlist feature
//        if (!$dict = @pspell_new_personal(dirname(__FILE__) . "/local/$this->lang.dict", $this->lang, '', '', 'utf-8', PSPELL_FAST)) {
        if (!$dict = @pspell_new($this->lang, '', '', 'utf-8', PSPELL_FAST)) {
            $this->_error(10, $this->lang);
            return;
        }

        $line  = 1;
        $stack = array();
        $root  = $this->tidy->root();
        $this->_spell_check_r($root, $dict, $line, $stack);

    } //end of _spell_check()



    /* performs a spell checking */
    private function _spell_check_r(&$node, $dict, &$line, &$stack) {
        global $message;

        if ($node->isText()) {
            if ($text = strtr($node->value, array('&nbsp;'=>''))) {
                $text = iconv($this->readable_charset(), 'UTF-8//IGNORE', $text);
                preg_match_all('/\pL+/u', $text, $words);

                // we avoid repeting the same error on the same line, so we keep the errors in the stack
                if ($node->line != $line) {
                    unset($stack);
                    $line = $node->line;
                }

                foreach ($words[0] as $word) {
                    if (empty($stack[$word]) && !pspell_check($dict, $word))
                    {
                        $stack[$word] = 1;
                        $column = $node->column + strpos($text, $word); //use the real column

                        $this->spell[] = "line $node->line column $column - Spell: " .
                                         sprintf($message[23], $word, implode(', ', array_slice(pspell_suggest($dict, $word), 0, 10)));
                    }
                }
            }

        // iterate through the childs, but not childs of <script> and <style> as they contain code, not text
        } elseif ($childs = &$node->child && $node->name != 'script' && $node->name != 'style') {
            foreach ($childs as $child) {
                $this->_spell_check_r($child, $dict, $line, $stack);
            }
        }
    } //end of _spell_check_r()



    /* return the number of tidy errors */
    function error() {
        $tmp = $this->errors_array();
        return $tmp ? count($tmp) : 0;

    } //end of error()



    /* Return error buffer from tidy as string */
    function errors_string() {
        static $cache;

        if ($cache != NULL)
            return $cache;

        if ($this->tidy instanceof tidy) {

            /* discard other info and keep just the errors */
            if (preg_match_all('/^line .+$/m', $this->tidy->errorBuffer, $match) || $this->spell) {

                $match = $match[0];

                /* filter array to remove bogus tidy errors */
                $count = count($match);
                for ($i = 0; $i < $count; ++$i) {
                    if (strpos($match[$i], 'Warning: <table> lacks "summary" attribute')) {
                        unset($match[$i]);
                    }
                }

                $match = array_merge($match, $this->spell); // merge with the spelling errors
                natsort($match);

                return $cache = nl2br(htmlspecialchars(implode("\n", $match), ENT_QUOTES, 'UTF-8'));

            } else {
                return $cache = false;
            }

        /* there is no tidy resource */
        } else {
            $this->_error(4);
            return false;
        }

    } //end of errors_string()



    /* Return error buffer from tidy as a parsed array */
    function errors_array() {
        static $cache;

        if ($cache != NULL)
            return $cache;

        if ($buffer = $this->errors_string()) {

            preg_match_all('/^line (\d+) column (\d+) - ([^:]+): (?:\[[^]]+\]: )?(.+)$/m', $buffer, $match, PREG_SET_ORDER);
            return $cache = $match;

        } else {
            return $cache = false;
        }

    } //end of errors_array()



    /* Return errors along with the source */
    function errors_source() {

        $return = '';
        $i = 1;
        $errors = $this->errors_array();
        $lines = preg_split('/(?:\r\n?|\n)/', nl2br(htmlspecialchars($this->html, ENT_QUOTES, 'UTF-8')));

        foreach ($lines as $line) {
            $return .= "$i: $line\n";
            $first   = 1;

            while ($errors && $errors[0][1] == $i) {

                // we need to close the <code> tags before printing the <div>s with the errors
                if ($first)
                    $return .= '</code>';

                $return .= '<div class="' . $errors[0][3] . '">' . $errors[0][4] . "</div><br />\n";
                $first   = 0;
                array_shift($errors);
            }
            ++$i;

            // reopen the <code> tag again, if needed
            if (!$first)
                $return .= '<code>';
        }

        return strtr($return, array('  ' => ' &nbsp;'));

    } //end of errors_source()



    /* Return true if there are any internal errors */
    function internal_error() {

        return $this->errors != '';

    } //end of internal_error()



    /* Return and clean the internal error buffer */
    function internal_errors_string() {

        $return = rtrim($this->errors);
        $this->errors = '';
        return $return;

    } //end of internal_errors_string()



    /* find/check encoding */
    private function _check_encoding($text, $headers = false) {

        if ($this->config['charset'] == 'auto') {

            /* XML */
            if (!empty($this->config['output-xml']) || (isset($this->config['output']) && $this->config['output'] == 'xml')) {
                $regex = '/<\?xml[^>]+encoding\s*=\s*["\']?\s*([^"\' ]+)\s*["\']?[^>]+\?>/i';

            /* HTTP headers */
            } elseif ($headers) {
                $regex = '/^Content-Type:\s*\S+\s*charset\s*=\s*(\S+)/mi';

            /* plain X?HTML */
            } else {
                $regex = '/<\s*meta\s*http-equiv=[^>]+charset\s*=\s*([^"\' ]+)\s*[\'"]?\s*\/?\s*>/i';
            }

            /* Parse HTTP headers, HTML <meta> tags or XML */
            if (preg_match($regex, $text, $match)) {

                $charset = strtolower($match[1]);

                if (isset($GLOBALS['opts']['charset']['values'][$charset])) {
                    $this->charset = $charset;
                    return true;
                }

                if (isset($GLOBALS['charset_aliases'][$charset])) {
                    $this->charset = $GLOBALS['charset_aliases'][$charset];
                    return true;
                }

                /* Found a charset, but couldn't recognise */
                $this->_error(5, $charset);
                $this->charset = 'latin1';
                return true;


            /* Couldn't find the encoding automatically */
            } else {
                return false;
            }

        /* Manually entered encoding */
        } else {
            $this->charset = $this->config['charset'];
            return true;
        }

    } // end of _check_enconding()



    /* Handle errors */
    function _error($number, $additional = null) {

        $this->errors .= sprintf($GLOBALS['error'][$number] . "\n", $additional);

    } //end of _error()


    /* debug: print_r($this) */
    function debug($html = false) {

        if (!$html) {
            unset($this->html);
        }

         return print_r($this, true);

    }  //end of debug()

}

?>
