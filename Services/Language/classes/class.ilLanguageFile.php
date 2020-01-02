<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
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
    /**
     * Created global file objects
     * @static		array
     */
    private static $global_file_objects = array();
    
    /**
     * file name and path
     * @var		string
     */
    private $lang_file;

    /**
     * language key
     * @var		string
     */
    private $lang_key;

    /**
     * scope of the language file ('global', 'local' or 'unchanged')
     * @var		string
     */
    private $scope;


    /**
     * header of the language file including the starting line
     * @var		string
     */
    private $header;

    /**
     * starting line below the header
     * @var		string
     */
    private $file_start = "<!-- language file start -->";


    /**
     * separator value between module,identivier & value
     * @var		string
     */
    private $separator;

    /**
     * separator value between the content and the comment of the lang entry
     * @var		string
     */
    private $comment_separator;

    /**
    * header parameters
    * @var		array        name => value
    */
    private $params = array();
    
    /**
    * text values
    * @var		array        module.separator.identifier => value
    */
    private $values = array();

    /**
    * comments
    * @var		array        module.separator.identifier => comment
    */
    private $comments = array();

    /**
    * error message of the last read/write operation
    * @var		string        error message
    */
    private $error_message = "";
    
    /**
    * Constructor
    * @param   string      language file path and name
    * @param   string      (optional) language key
    * @param   string      (optional) scope ('global', 'local' or 'unchanged')
    */
    public function __construct($a_file, $a_key = "", $a_scope = 'global')
    {
        global $DIC;
        $lng = $DIC->language();

        $this->separator = $lng->separator;
        $this->comment_separator = $lng->comment_separator;

        $this->lang_file = $a_file;
        $this->lang_key = $a_key;
        $this->scope = $a_scope;

        // initialize the header of a blank file
        $this->header = $file_start;
        
        // Set the default parameters to be written in a new file.
        // This ensures the correct order of parameters
        
        $this->params["module"] = "language file";
        $this->params["modulegroup"] = "language";
        
        if ($this->scope == "local") {
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
    * @return   boolean     reading successful
    */
    public function read()
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
                if (trim($line) == $this->file_start) {
                    $in_header = false;
                    continue;
                } else {
                    // get header params
                    $pos_par = strpos($line, "* @");

                    if ($pos_par !== false) {
                        $pos_par += 3;
                        $pos_space = strpos($line, " ", $pos_par);
                        $pos_tab = strpos($line, "\t", $pos_par);
                        if ($pos_space !== false and $pos_tab !== false) {
                            $pos_white = min($pos_space, $pos_tab);
                        } elseif ($pos_space !== false) {
                            $pos_white = $pos_space;
                        } elseif ($pos_tab !== false) {
                            $pos_white = $pos_tab;
                        } else {
                            $pos_white = false;
                        }
                        if ($pos_white) {
                            $param = substr($line, $pos_par, $pos_white-$pos_par);
                            $value = trim(substr($line, $pos_white));

                            $this->params[$param] = $value;
                        }
                    }
                }
            } else {
                // separate the lang file entry
                $separated = explode($this->separator, trim($line));
                
                // not a valid line with module, identifier and value?
                if (count($separated) != 3) {
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
        } else {
            return true;
        }
    }
    
    /**
    * Write a language file
    *
    * @param    string      (optional) fixed header for the new file
    */
    public function write($a_header = '')
    {
        $fp = fopen($this->lang_file, "w");
        fwrite($fp, $this->build($a_header));
        fclose($fp);
    }

    /**
    * Build and get the file content
    *
    * @param    string      (optional) fixed header for the new file
    * @return   string      language file content
    */
    public function build($a_header = '')
    {
        global $DIC;
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        if ($a_header) {
            // take the given header
            $content = $a_header;
        } else {
            // set default params
            $lng->loadLanguageModule('meta');
            $lang_name = $lng->txtlng('meta', 'meta_l_' . $this->lang_key, 'en');
            $this->params["module"] = "language file " . $lang_name;
            $this->params["created"] = date('Y-m-d H:i:s');
            $this->params["created_by"] = $ilUser->getFullname() . " <" . $ilUser->getEmail() . ">";

            // build the header
            $tpl = new ilTemplate("tpl.lang_file_header.html", true, true, "Services/Language");
            foreach ($this->getAllParams() as $name => $value) {
                $tabs = ceil((20 - 3 - strlen($name)) / 4);
                $tabs = $tabs > 0 ? $tabs : 1;

                $tpl->setCurrentBlock('param');
                $tpl->setVariable('PAR_NAME', $name);
                $tpl->setVariable('PAR_SPACE', str_repeat("\t", $tabs));
                $tpl->setVariable('PAR_VALUE', $value);
                $tpl->parseCurrentBlock();
            }
            $txt_scope = $lng->txtlng('administration', 'language_scope_' . $this->scope, 'en');
            $tpl->setVariable('SCOPE', $txt_scope);

            $content = $tpl->get();
        }

        // fault tolerant check for adding newline
        $add_newline = (substr($content, strlen($content)-1, 1) != "\n");

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
    * @return   string      error message
    */
    public function getErrorMessage()
    {
        return $this->error_message;
    }


    /**
    * Get the header of the original file
    * @return   string
    */
    public function getHeader()
    {
        return $this->header;
    }

    
    /**
    * Get array of all parameters
    * @return   array      name => value
    */
    public function getAllParams()
    {
        return $this->params;
    }

    /**
    * Get array of all values
    * @return   array      module.separator.identifier => value
    */
    public function getAllValues()
    {
        return $this->values;
    }

    /**
    * Get array of all comments
    * @return   array      module.separator.identifier => comment
    */
    public function getAllComments()
    {
        return $this->comments;
    }

    /**
    * Get a single parameter
    * @param    string  	parameter name
    * @return   string  	parameter value
    */
    public function getParam($a_name)
    {
        return $this->params[$a_name];
    }

    /**
    * Get a single value
    * @param    string      module name
    * @param    string      indentifier
    * @return   string      value
    */
    public function getValue($a_module, $a_identifier)
    {
        return $this->values[$a_module . $this->separator . $a_identifier];
    }

    /**
    * Get a single comment
    * @param    string      module name
    * @param    string      indentifier
    * @return   string      value
    */
    public function getComment($a_module, $a_identifier)
    {
        return $this->comments[$a_module . $this->separator . $a_identifier];
    }

    /**
    * Set a  parameter
    * @param    string  	parameter name
    * @param   	string  	parameter value
    */
    public function setParam($a_name, $a_value)
    {
        $this->params[$a_name] = $a_value;
    }

    /**
    * Set a single value
    * @param    string      module name
    * @param    string      indentifier
    * @param    string      value
    */
    public function setValue($a_module, $a_identifier, $a_value)
    {
        $this->values[$a_module . $this->separator . $a_identifier] = $a_value;
    }

    /**
    * Set all values
    * @param    array       module.separator.identifier => value
    */
    public function setAllValues($a_values)
    {
        $this->values = $a_values;
    }

    /**
    * Set all comments
    * @param    array       module.separator.identifier => comment
    */
    public function setAllComments($a_comments)
    {
        $this->comments = $a_comments;
    }


    /**
    * Set a single comment
    * @param    string      module name
    * @param    string      indentifier
    * @param    string      comment
    */
    public function setComment($a_module, $a_identifier, $a_value)
    {
        return $this->comments[$a_module . $this->separator . $a_identifier] = $a_comment;
    }
    
    /**
    * Read and get a global language file as a singleton object
    * @param    string      language key
    * @return   object      language file object (with contents)
    */
    public static function _getGlobalLanguageFile($a_lang_key)
    {
        global $DIC;
        $lng = $DIC->language();
        
        if (!isset(self::$global_file_objects[$a_lang_key])) {
            $file_object = new ilLanguageFile(
                $lng->lang_path . "/ilias_" . $a_lang_key . ".lang",
                $a_lang_key,
                'global'
            );
            $file_object->read();
            
            self::$global_file_objects[$a_lang_key] = $file_object;
        }
        
        return self::$global_file_objects[$a_lang_key];
    }
}
