<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* language handling for setup
*
* this class offers the language handling for an application.
* the constructor is called with a small language abbreviation
* e.g. $lng = new Language("en");
* the constructor reads the single-languagefile en.lang and puts this into an array.
* with 
* e.g. $lng->txt("user_updated");
* you can translate a lang-topic into the actual language
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
* 
* @todo Das Datefeld wird bei �nderungen einer Sprache (update, install, deinstall) nicht richtig gesetzt!!!
*  Die Formatfunktionen geh�ren nicht in class.Language. Die sind auch woanders einsetzbar!!!
*  Daher->besser in class.Format
*/
class ilLanguage
{
    /**
    * text elements
    * @var array
    * @access private
    */
    var $text = array();
    
    /**
    * indicator for the system language
    * this language must not be deleted
    * @var      string
    * @access   private
    */
    var $lang_default = "en";

    /**
    * path to language files
    * relative path is taken from ini file
    * and added to absolute path of ilias
    * @var      string
    * @access   private
    */
    var $lang_path;

    /**
    * language key in use by current user
    * @var      string  languagecode (two characters), e.g. "de", "en", "in"
    * @access   private
    */
    var $lang_key;

    /**
    * separator value between module,identivier & value 
    * @var      string
    * @access   private
    */
    var $separator = "#:#";
    
    /**
    * separator value between the content and the comment of the lang entry
    * @var      string
    * @access   private
    */
    var $comment_separator = "###";

    /**
    * Constructor
    * read the single-language file and put this in an array text.
    * the text array is two-dimensional. First dimension is the language.
    * Second dimension is the languagetopic. Content is the translation.
    * @access   public
    * @param    string      languagecode (two characters), e.g. "de", "en", "in"
    * @return   boolean     false if reading failed
    */
    function ilLanguage($a_lang_key)
    {
        $this->lang_key = ($a_lang_key) ? $a_lang_key : $this->lang_default;
        //$_GET["lang"] = $this->lang_key;  // only for downward compability (old setup)
        $this->lang_path = ILIAS_ABSOLUTE_PATH."/lang";

        // set lang file...
        $txt = file($this->lang_path."/setup_".$this->lang_key.".lang");

        // ...and load langdata
        if (is_array($txt))
        {
            foreach ($txt as $row)
            {
                if ($row[0] != "#")
                {
                    $a = explode($this->separator,trim($row));
                    $this->text[trim($a[0])] = trim($a[1]);
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
    * gets the text for a given topic
    *
    * if the topic is not in the list, the topic itself with "-" will be returned
    * @access   public 
    * @param    string  topic
    * @return   string  text clear-text
    */
    function txt($a_topic)
    {
        global $log;
        
        if (empty($a_topic))
        {
            return "";
        }

        $translation = $this->text[$a_topic];
        
        //get position of the comment_separator
        $pos = strpos($translation, $this->comment_separator);

        if ($pos !== false)
        {
            // remove comment
            $translation = substr($translation,0,$pos);
        }

        if ($translation == "")
        {
            $log->writeLanguageLog($a_topic,$this->lang_key);
            return "-".$a_topic."-";
        }
        else
        {
            return $translation;
        }
    }

    /**
    * get all setup languages in the system
    *
    * the functions looks for setup*.lang-files in the languagedirectory
    * @access   public
    * @return   array   langs
    */
    function getLanguages()
    {
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir ($this->lang_path);

        // get available setup-files
        while ($entry = $d->read())
        {
            if (is_file($entry) && (ereg ("(^setup_.{2}\.lang$)", $entry)))
            {
                $lang_key = substr($entry,6,2);
                $languages[] = $lang_key;
            }
        }

        chdir($tmpPath);

        return $languages;
    }

    /**
    * install languages
    * @param    array   array with lang_keys of languages to install
    * @return   boolean true on success
    */
    function installLanguages($a_lang_keys)
    {
        if (empty($a_lang_keys))
        {
            $a_lang_keys = array();
        }
        
        $err_lang = array();

        $this->flushLanguages();
        
        $db_langs = $this->getAvailableLanguages();

        foreach ($a_lang_keys as $lang_key)
        {
            if ($this->checkLanguage($lang_key))
            {
                // ...re-insert data from lang-file
                $this->insertLanguage($lang_key);
                
                // register language first time install
                if (!array_key_exists($lang_key,$db_langs))
                {
                    $q = "INSERT INTO object_data ".
                         "(type,title,description,owner,create_date,last_update) ".
                         "VALUES ".
                         "('lng','".$lang_key."','installed','-1',now(),now())";
                    $this->db->query($q);
                }
            }
            else
            {
                $err_lang[] = $lang_key;
            }
        }
        
        foreach ($db_langs as $key => $val)
        {
            if (!in_array($key,$err_lang))
            {
                if (in_array($key,$a_lang_keys))
                {
                    if ($val["status"] != "installed")
                    {
                        $q = "UPDATE object_data SET ".
                             "description = 'installed',last_update = now() ".
                             "WHERE obj_id='".$val["obj_id"]."' ".
                             "AND type='lng'";
                        $this->db->query($q);
                    }
                }
                else
                {
                    if ($val["status"] == "installed")
                    {
                        $q = "UPDATE object_data SET ".
                             "description = 'not_installed',last_update = now() ".
                             "WHERE obj_id='".$val["obj_id"]."' ".
                             "AND type='lng'";
                        $this->db->query($q);
                    }
                }
            }
        }

        return ($err_lang) ? $err_lang : true;
    }

    /**
    * get already installed languages (in db)
    * @return   array   array with inforamtino of each installed language
    */
    function getInstalledLanguages()
    {
        $arr = array();

        $q = "SELECT * FROM object_data ".
             "WHERE type = 'lng' ".
             "AND description = 'installed'";
        $r = $this->db->query($q);

        while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
        {
            $arr[] = $row->title;
        }

        return $arr;
    }
    
    /**
    * get already registered languages (in db)
    * @return   array   array with information about languages that has been registered in db
    */
    function getAvailableLanguages()
    {
        $arr = array();

        $q = "SELECT * FROM object_data ".
             "WHERE type = 'lng'";
        $r = $this->db->query($q);

        while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
        {
            $arr[$row->title]["obj_id"] = $row->obj_id;
            $arr[$row->title]["status"] = $row->description;
        }

        return $arr;
    }

    /**
    * validate the logical structure of a lang-file
    *
    * This function checks if a lang-file of a given lang_key exists,
    * the file has a header and each lang-entry consist of exact three elements
    * (module,identifier,value)
    *
    * @param    string      $lang_key   international language key (2 digits)
    * @return   string      $info_text  message about results of check OR "1" if all checks successfully passed
    */
    function checkLanguage ($a_lang_key)
    {
        $tmpPath = getcwd();
        chdir ($this->lang_path);

        // compute lang-file name format
        $lang_file = "ilias_".$a_lang_key.".lang";

        // file check
        if (!is_file($lang_file))
        {
            chdir($tmpPath);
            return false;
        }

        // header check
        if (!$content = $this->cut_header(file($lang_file)))
        {
            chdir($tmpPath);
            return false;
        }

        // check (counting) elements of each lang-entry
        foreach ($content as $key => $val)
        {
            $separated = explode ($this->separator,trim($val));
            $num = count($separated);

            if ($num != 3)
            {
                chdir($tmpPath);
                return false;
            }
        }

        chdir($tmpPath);

        // no error occured
        return true;
    }

    /**
    * remove lang-file haeder information from '$content'
    *
    * This function seeks for a special keyword where the language information starts.
    * if found it returns the plain language information, otherwise returns false
    *
    * @param    string  $content    expect an ILIAS lang-file
    * @return   string  $content    content without header info OR false if no valid header was found
    * @access   private
    */
    function cut_header ($content)
    {
        foreach ($content as $key => $val)
        {
            if (trim($val) == "<!-- language file start -->")
            {
                return array_slice($content,$key +1);
            }
        }

        return false;
    }

    /**
    * remove all languagee from database
    */
    function flushLanguages ()
    {
        $q = "DELETE FROM lng_data";
        $this->db->query($q);
    }

    //TODO: remove redundant checks here!
    /**
    * insert language data form file in database
    *
    * @param    string  $lang_key   international language key (2 digits)
    *
    * @return   void
    */
    function insertLanguage ($lang_key)
    {
        $tmpPath = getcwd();
        chdir($this->lang_path);

        $lang_file = "ilias_".$lang_key.".lang";

        if ($lang_file)
        {
            // remove header first
            if ($content = $this->cut_header(file($lang_file)))
            {
                foreach ($content as $key => $val)
                {
                    $separated = explode ($this->separator,trim($val));

                    //get position of the comment_separator
                    $pos = strpos($separated[2], $this->comment_separator);

                    if ($pos !== false)
                    {
                        //cut comment of
                        $separated[2] = substr($separated[2] , 0 , $pos);
                    }

                    $num = count($separated);

                    $q = "INSERT INTO lng_data ".
                         "(module,identifier,lang_key,value) ".
                         "VALUES ".
                         "('".$separated[0]."','".$separated[1]."','".$lang_key."','".addslashes($separated[2])."')";
                    $this->db->query($q);
                }
            }
        }

        chdir($tmpPath);
    }
    
    function getInstallableLanguages()
    {
        $setup_langs = $this->getLanguages();

        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir ($this->lang_path);

        // get available lang-files
        while ($entry = $d->read())
        {
            if (is_file($entry) && (ereg ("(^ilias_.{2}\.lang)", $entry)))
            {
                $lang_key = substr($entry,6,2);
                $languages1[] = $lang_key;
            }
        }
        
        //$languages = array_intersect($languages1,$setup_langs);   

        chdir($tmpPath);

        return $languages1;
    }
    
    /**
    * set db handler object
    * @string   object  db handler
    * @return   boolean true on success
    */
    function setDbHandler ($a_db_handler)
    {
        if (empty($a_db_handler) or !is_object($a_db_handler))
        {
            return false;
        }
        
        $this->db =& $a_db_handler;
        
        return true;
    }
} // END class.ilLanguage
?>
