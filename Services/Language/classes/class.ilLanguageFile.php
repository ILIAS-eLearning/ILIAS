<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
* Class ilLanguageFile
*
* Provides methods for working with language files:
* read, check and write content, comments and parameters
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id$
*
* @ingroup ServicesLanguage
*/
class ilLanguageFile
{
    private static array $global_file_objects;
    private string $lang_file;
    private string $lang_key;
    private string $scope;
    private string $header;
    private string $file_start = "<!-- language file start -->";
    private string $separator;
    private string $comment_separator;
    private array $params;
    private array $values;
    private array $comments;
    private string $error_message = "";
    
    /**
    * Constructor
    * $a_file      language file path and name
    * $a_key      (optional) language key
    * $a_scope      (optional) scope ('global', 'local' or 'unchanged')
    */
    public function __construct(string $a_file, string $a_key = "", string $a_scope = "global")
    {
        global $DIC;
        $lng = $DIC->language();

        $this->separator = $lng->separator;
        $this->comment_separator = $lng->comment_separator;

        $this->lang_file = $a_file;
        $this->lang_key = $a_key;
        $this->scope = $a_scope;

        // initialize the header of a blank file
        $this->header = $this->file_start;
        
        // Set the default parameters to be written in a new file.
        // This ensures the correct order of parameters
        
        $this->params["module"] = "language file";
        $this->params["modulegroup"] = "language";
        
        if ($this->scope === "local") {
            $this->params["based_on"] = "";
        } else {
            $this->params["author"] = "";
            $this->params["version"] = "";
        }

        $this->params["il_server"] = ILIAS_HTTP_PATH;
        $this->params["il_version"] = ILIAS_VERSION;
        $this->params["created"] = "";
        $this->params["created_by"] = "";
    }

    /**
    * Read a language file
    * Return true, if reading successful. Otherwise return false
    */
    public function read() : bool
    {
        global $DIC;
        $lng = $DIC->language();
        
        $this->header = '';
        $this->params = array();
        $this->values = array();
        $this->comments = array();
        $this->error_message = "";

        $content = file($this->lang_file);
        $in_header = true;

        foreach ($content as $line_num => $line) {
            if ($in_header) {
                // store the header line
                $this->header .= $line . "\n";

                // check header end
                if (trim($line) === $this->file_start) {
                    $in_header = false;
                } else {
                    // get header params
                    $pos_par = strpos($line, "* @");

                    if (strpos($line, "* @") !== false) {
                        $pos_par += 3;
                        $pos_space = strpos($line, " ", $pos_par);
                        $pos_tab = strpos($line, "\t", $pos_par);
                        if ($pos_space !== false && $pos_tab !== false) {
                            $pos_white = min($pos_space, $pos_tab);
                        } elseif ($pos_space !== false) {
                            $pos_white = $pos_space;
                        } elseif ($pos_tab !== false) {
                            $pos_white = $pos_tab;
                        } else {
                            $pos_white = false;
                        }
                        if ($pos_white) {
                            $param = substr($line, $pos_par, $pos_white - $pos_par);
                            $value = trim(substr($line, $pos_white));

                            $this->params[$param] = $value;
                        }
                    }
                }
            } else {
                // separate the lang file entry
                $separated = explode($this->separator, trim($line));
                
                // not a valid line with module, identifier and value?
                if (count($separated) !== 3) {
                    $this->error_message =
                            $lng->txt("file_not_valid") . " "
                            . $lng->txt("err_in_line") . " " . $line_num . ". "
                            . $lng->txt("err_count_param");
                    return false;
                } else {
                    $key = $separated[0] . $this->separator . $separated[1];
                    $value = $separated[2];

                    // cut off comment
                    $pos = strpos($value, $this->comment_separator);
                    if ($pos !== false) {
                        $this->comments[$key]
                            = substr($value, $pos + strlen($this->comment_separator));
                            
                        $value = substr($value, 0, $pos);
                    }
                    $this->values[$key] = $value;
                }
            }
        }
        // still in header after parsing the whole file?
        if ($in_header) {
            $this->error_message = $lng->txt("file_not_valid") . " " . $lng->txt("err_wrong_header");
            return false;
        }

        return true;
    }
    
    /**
    * Write a language file
    *
    * $a_header      (optional) fixed header for the new file
    */
    public function write(string $a_header = "") : void
    {
        $fp = fopen($this->lang_file, 'wb');
        fwrite($fp, $this->build($a_header));
        fclose($fp);
    }

    /**
    * Build and get the file content
    *
    * $a_header     (optional) fixed header for the new file
    */
    public function build(string $a_header = '') : string
    {
        global $DIC;
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        if ($a_header) {
            // take the given header
            $content = $a_header;
        } else {
            // set default params
            $lng->loadLanguageModule("meta");
            $lang_name = $lng->txtlng("meta", "meta_l_" . $this->lang_key, "en");
            $this->params["module"] = "language file " . $lang_name;
            $this->params["created"] = date("Y-m-d H:i:s");
            $this->params["created_by"] = $ilUser->getFullname() . " <" . $ilUser->getEmail() . ">";

            // build the header
            $tpl = new ilTemplate("tpl.lang_file_header.html", true, true, "Services/Language");
            foreach ($this->getAllParams() as $name => $value) {
                $tabs = ceil((20 - 3 - strlen($name)) / 4);
                $tabs = $tabs > 0 ? $tabs : 1;

                $tpl->setCurrentBlock("param");
                $tpl->setVariable("PAR_NAME", $name);
                $tpl->setVariable("PAR_SPACE", str_repeat("\t", $tabs));
                $tpl->setVariable("PAR_VALUE", $value);
                $tpl->parseCurrentBlock();
            }
            $txt_scope = $lng->txtlng("administration", "language_scope_" . $this->scope, "en");
            $tpl->setVariable("SCOPE", $txt_scope);

            $content = $tpl->get();
        }

        // fault tolerant check for adding newline
        $add_newline = (substr($content, strlen($content) - 1, 1) !== "\n");

        // build the content
        foreach ($this->values as $key => $value) {
            // add the newline before the line!
            // a valid lang file should not have a newline at the end!
            if ($add_newline) {
                $content .= "\n";
            }
            $add_newline = true;

            $content .= $key . $this->separator . $value;

            if ($this->comments[$key]) {
                $content .= $this->comment_separator . $this->comments[$key];
            }
        }
        return $content;
    }
    
    
    /**
    * Get the error message of the last read/write operation
    */
    public function getErrorMessage() : string
    {
        return $this->error_message;
    }


    /**
    * Get the header of the original file
    */
    public function getHeader() : string
    {
        return $this->header;
    }

    
    /**
    * Get array of all parameters
    * Return array     [name => value]
    */
    public function getAllParams() : array
    {
        return $this->params;
    }

    /**
    * Get array of all values
    * Return array     [module.separator.identifier => value]
    */
    public function getAllValues() : array
    {
        return $this->values;
    }

    /**
    * Get array of all comments
    * Return array     [module.separator.identifier => comment]
    */
    public function getAllComments() : array
    {
        return $this->comments;
    }

    /**
    * Get a single parameter
    * $a_name    parameter name
    */
    public function getParam(string $a_name) : string
    {
        return $this->params[$a_name];
    }

    /**
    * Get a single value
    * $a_module      module name
    * $a_identifier      indentifier
    */
    public function getValue(string $a_module, string $a_identifier) : string
    {
        return $this->values[$a_module . $this->separator . $a_identifier];
    }

    /**
    * Get a single comment
    * $a_module      module name
    * $a_identifier      indentifier
    */
    public function getComment(string $a_module, string $a_identifier) : string
    {
        return $this->comments[$a_module . $this->separator . $a_identifier];
    }

    /**
    * Set a  parameter
    * $a_name    parameter name
    * $a_value   parameter value
    */
    public function setParam(string $a_name, string $a_value) : void
    {
        $this->params[$a_name] = $a_value;
    }

    /**
    * Set a single value
    * $a_module      module name
    * $a_identifier      indentifier
    * $a_value      value
    */
    public function setValue(string $a_module, string $a_identifier, string $a_value) : void
    {
        $this->values[$a_module . $this->separator . $a_identifier] = $a_value;
    }

    /**
    * Set all values
    * $a_values       [module.separator.identifier => value]
    */
    public function setAllValues(array $a_values) : void
    {
        $this->values = $a_values;
    }

    /**
    * Set all comments
    * $a_comments       [module.separator.identifier => comment]
    */
    public function setAllComments(array $a_comments) : void
    {
        $this->comments = $a_comments;
    }


    /**
    * Set a single comment
    * $a_module      module name
    * $a_identifier      indentifier
    * $a_comment      comment
    */
    public function setComment(string $a_module, string $a_identifier, string $a_comment) : string
    {
        return $this->comments[$a_module . $this->separator . $a_identifier] = $a_comment;
    }
    
    /**
    * Read and get a global language file as a singleton object
    * $a_lang_key     language key
    * @return   object      language file object (with contents)
    */
    public static function _getGlobalLanguageFile(string $a_lang_key)
    {
        global $DIC;
        $lng = $DIC->language();
        
        if (!isset(self::$global_file_objects[$a_lang_key])) {
            $file_object = new ilLanguageFile(
                $lng->lang_path . "/ilias_" . $a_lang_key . ".lang",
                $a_lang_key,
                "global"
            );
            $file_object->read();
            
            self::$global_file_objects[$a_lang_key] = $file_object;
        }
        
        return self::$global_file_objects[$a_lang_key];
    }
}
