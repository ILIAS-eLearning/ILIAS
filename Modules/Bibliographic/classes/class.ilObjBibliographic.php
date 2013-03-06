<?php

    /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

    require_once "Services/Object/classes/class.ilObject2.php";
    require_once "Modules/Bibliographic/classes/class.ilBibliographicEntry.php";

    /**
     * Class ilObjBibliographic
     *
     * @author Oskar Truffer <ot@studer-raimann.ch>, Gabriel Comte <gc@studer-raimann.ch>
     * @version $Id: class.ilObjBibliographic.php 2012-01-11 10:37:11Z otruffer $
     *
     * @extends ilObject2
     */
class ilObjBibliographic extends ilObject2
{

    /**
     * Id of literary articles
     * @var int
     */
    protected $filename;


    /**
     * Id of literary articles
     * @var ilBibliographicEntry[]
     */
    protected $entries;


    /**
     * Models describing how the overview of each entry is showed
     * @var overviewModels[]
     */
    protected $overviewModels;


    /**
     * Models describing how the overview of each entry is showed
     * @var is_online
     */
    protected $is_online;


    /**
     * initType
     * @return void
     */
    public function initType()
    {
        $this->type = "bibl";
    }

    /**
     * If bibliographic object exists, read it's data from database, otherwise create it
     *
     * @param bool $existant_bibl_id is not set when object is getting created
     * @return void
     */
    public function __construct($existant_bibl_id = false)
    {
        if($existant_bibl_id){
            $this->setId($existant_bibl_id);
            $this->doRead();
        }

        parent::__construct();
    }

    /**
     * Create object
     * @return void
     */
    function doCreate()
    {
        global $ilDB;

        $ilDB->manipulate("INSERT INTO il_bibl_data " . "(id, filename, is_online) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," . // id
            $ilDB->quote($this->getFilename(), "text") . ","  . // filename
            $ilDB->quote($this->getOnline(), "integer") . // is_online
            ")");

    }

    function doRead()
    {

        global $ilDB;
        $set = $ilDB->query("SELECT * FROM il_bibl_data ".
            " WHERE id = ".$ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set))
        {
            if(!$this->getFilename()){
                $this->setFilename($rec["filename"]);
            }
            $this->setOnline($rec['is_online']);
        }

    }


    /**
     * Update data
     */
    function doUpdate()
    {
        global $ilDB;

        if(!empty($_FILES['bibliographic_file']['name'])){
            $this->deleteFile();
            $this->doDelete(true);
            $this->moveFile();
        }

        $ilDB->manipulate("UPDATE il_bibl_data SET " .
            "filename = " . $ilDB->quote($this->getFilename(), "text") . ", " .// filename
            "is_online = " . $ilDB->quote($this->getOnline(), "integer") . // is_online
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));

        $this->writeSourcefileEntriesToDb($this);

    }

    /*
    * Delete data from db
    */
    function doDelete($leave_out_il_bibl_data = false)
    {
        global $ilDB;

        $this->deleteFile();

        //il_bibl_attribute
        $ilDB->manipulate("DELETE FROM il_bibl_attribute WHERE il_bibl_attribute.entry_id IN " .
                           "(SELECT il_bibl_entry.id FROM il_bibl_entry WHERE il_bibl_entry.data_id = " .$ilDB->quote($this->getId(), "integer") . ");");
        //il_bibl_entry
        $ilDB->manipulate("DELETE FROM il_bibl_entry WHERE data_id = " . $ilDB->quote($this->getId(), "integer"));

        if(!$leave_out_il_bibl_data){
            //il_bibl_data
            $ilDB->manipulate("DELETE FROM il_bibl_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        }
    }

    public function moveFile($file_to_copy = false){

        $target_dir = ilUtil::getDataDir() . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . $this->getId();

        if(!is_dir($target_dir)){
                ilUtil::makeDir($target_dir);
            }
        if($_FILES['bibliographic_file']['name']){
            $target_full_filename = $target_dir . DIRECTORY_SEPARATOR . $_FILES['bibliographic_file']['name'];
        }else{
            //file is not uploaded, but a clone is made out of another bibl
            $split_path = explode(DIRECTORY_SEPARATOR, $file_to_copy);
            $target_full_filename = $target_dir . DIRECTORY_SEPARATOR . $split_path[sizeof($split_path)-1];
        }

        //If there is no file_to_copy (which is used for clones), copy the file from the temporary upload directory (new creation of object).
        //Therefore, a warning predicates nothing and can be suppressed.
        if(@!copy($file_to_copy, $target_full_filename)){
            ilUtil::moveUploadedFile($_FILES['bibliographic_file']['tmp_name'], $_FILES['bibliographic_file']['name'], $target_full_filename);
        }

        $this->setFilename($target_full_filename);

        ilUtil::sendSuccess($this->lng->txt("object_added"), true);

    }


    function  deleteFile(){
        $path = $this->getFilePath(true);

  	self::__force_rmdir($path);
      }

    /**
     * @param bool $without_filename
     * @return array with all filepath
     */
     public function getFilePath($without_filename = false){
        global $ilDB;

        $set = $ilDB->query("SELECT filename FROM il_bibl_data ".
                " WHERE id = ".$ilDB->quote($this->getId(), "integer")
        );

        $rec = $ilDB->fetchAssoc($set);
        {
            if($without_filename){
                return substr($rec['filename'], 0, strrpos($rec['filename'], DIRECTORY_SEPARATOR));
            }else{
                return $rec['filename'];
            }

        }
    }



    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }


    public function getFiletype()
    {
        return strtolower(substr($this->getFilename(), -3 ));
    }


    static function __getAllOverviewModels()
    {
        global $ilDB;

        $set = $ilDB->query('SELECT * FROM il_bibl_overview_model');
        while ($rec = $ilDB->fetchAssoc($set))
        {
            if($rec['literature_type']){
                $overviewModels[$rec['filetype']][$rec['literature_type']] = $rec['pattern'];
            }else{
                $overviewModels[$rec['filetype']] = $rec['pattern'];
            }
        }
        return $overviewModels;

    }

    /**
     * remove a directory recursively
     * @param $path
     * @return bool
     */
    protected static function __force_rmdir($path) {
        if (!file_exists($path)) return false;

        if (is_file($path) || is_link($path)) {
            return unlink($path);
        }

        if (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            $result = true;

            $dir = new DirectoryIterator($path);

            foreach ($dir as $file) {
                if (!$file->isDot()) {
                    $result &= self::__force_rmdir($path . $file->getFilename(), false);
                }
            }

            $result &= rmdir($path);
            return $result;
        }
    }


    static function __readRisFile($full_filename){

        $handle = fopen($full_filename, "r");

        //Get rid of UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom != "\xEF\xBB\xBF"){
            rewind($handle);
        }

        $entry = array();

        $key_before = null;

        while($line = fgets($handle)){

            $line = mb_convert_encoding($line, "HTML-ENTITIES", "UTF-8");

            //remove unwanted spaces or dashes in the beginning of the line
            $line = self::__removeSpacesAndDashesAtBeginning($line);

            //get first two signs as the key
            $key = substr($line, 0, 2);

            //When key equals to 'er', write the entry[] in to entries[] and reset the entry[] for filling in the next one.
            if(strtolower($key) != 'er'){

                $line = substr($line, 2);
                $value = self::__removeSpacesAndDashesAtBeginning($line);

                //remove char [new line] at the end
                while($value[strlen($value) - 1] == "\r" || $value[strlen($value) - 1] == "\n"){
                    $value = substr($value, 0, strlen($value)-1);
                }

                if($key != $key_before){
                    $key_before = $key;

                    $entry[] = array('name' => $key, 'value' => $value);
                }else{
                    foreach($entry as $entry_key => $attribute){
                        if($attribute['name'] == $key){
                            $entry[$entry_key]['value'] .= "; " . $value;
                        }
                    }
                }
            }else{
                $entries[] = $entry;
                $entry = array();
            }
        }

        return $entries;
    }

    static function __readBibFile($full_filename){

        $escapedChars['{\&}'] = '&';

        $handle = fopen($full_filename, "r");


        //Get rid of UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom != "\xEF\xBB\xBF"){
            rewind($handle);
        }

        $file_content = fread($handle, filesize($full_filename));

        $file_content = mb_convert_encoding($file_content, "HTML-ENTITIES", "UTF-8");

        //remove newlines
        $file_content = str_replace(array("\r", "\r\n", "\n"), '', $file_content);

        //replace escaped chars by it's actual meanings
        foreach($escapedChars as $escape => $actualChar){
            $file_content = str_replace($escape, $actualChar, $file_content);
        }

        //read every entry in the file
        $entry = -1;
        while(substr(trim($file_content), 0) != "}"){

            $attribute_id = 0;

            $entry++;

            //get entrys document type and remove it from the file content
            $pos_atSign = strpos($file_content, "@");
            $pos_curlyBracket = strpos($file_content, "{", $pos_atSign);
            $pos_curlyBracket_without_spaces = strpos(str_replace(' ', '', $file_content), "    {", $pos_atSign);

            $entries[$entry][$attribute_id]['name'] = 'TY';
            $entries[$entry][$attribute_id++]['value'] = substr(str_replace(' ', '', $file_content), $pos_atSign + 1, $pos_curlyBracket_without_spaces - $pos_atSign - 1);

            $file_content = substr($file_content, $pos_curlyBracket + 1);


            //read for each entry, all attributes from the file
            while(strpos(trim($file_content), "}") != 0){
                $pos_equal_sign = strpos($file_content, "=");
                $attribute_key = trim(substr($file_content, 0, $pos_equal_sign));
                $file_content = substr($file_content, $pos_equal_sign + 1);

                $pos_curlyBracket_open = strpos($file_content, "{");
                $pos_curlyBracket_close = strpos($file_content, "}");
                $attribute_value = trim(substr($file_content, $pos_curlyBracket_open + 1, $pos_curlyBracket_close - $pos_curlyBracket_open -1));

                //remove value
                $file_content = substr($file_content, $pos_curlyBracket_close + 1);

                $file_content = self::__removeSpacesAndDashesAtBeginning($file_content);

                //remove comma
                if(strpos($file_content, ",") <= 3 && strpos($file_content, ",") !== false){
                    $file_content = substr($file_content, strpos($file_content, ",")+1);
                }

                $entries[$entry][$attribute_id]['name'] = $attribute_key;
                $entries[$entry][$attribute_id++]['value'] = $attribute_value;

            }
        }

        return $entries;

    }



    /**
     * Clone DCL
     *
     * @param ilObjDataCollection new object
     * @param int target ref_id
     * @param int copy id
     * @return ilObjPoll
     */
    public function doCloneObject(ilObjBibliographic $new_obj, $a_target_id, $a_copy_id = 0)
    {
        $new_obj->cloneStructure($this->getId());

        return $new_obj;
    }


    /**
     * Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
     * @/** @varparam $original_id The original ID of the dataselection you want to clone it's structure
     * @return void
     */
    public function cloneStructure($original_id)
    {
        $original = new ilObjBibliographic($original_id);

        $this->moveFile($original->getFilename());

        $this->setOnline($original->getOnline());
        $this->setDescription($original->getDescription());
        $this->setTitle($original->getTitle());
        $this->setType($original->getType());

        $this->doUpdate();

        $this->writeSourcefileEntriesToDb();
    }


    protected static function __removeSpacesAndDashesAtBeginning($input){
        for($i = 0; $i < strlen($input); $i++){
            if($input[$i] != " " && $input[$i] != "-"){
                return substr($input, $i);
            }
        }

    }


    /**
     * Reads out the source file and writes all entries to the database
     * @return void
     */
    public function writeSourcefileEntriesToDb(){

        //Read File
        switch($this->getFiletype()){
            case("ris"):
                $entries_from_file = self::__readRisFile($this->getFilename());
                break;
            case("bib"):
                $entries_from_file = self::__readBibFile($this->getFilename());
                break;
        }

        //fill each entry into a ilBibliographicEntry object and then write it to DB by executing doCreate()
        foreach($entries_from_file as $file_entry){
            $type = null;
            foreach($file_entry as $key => $attribute){
                // ty is the type and is treated seperately
                if(strtolower($attribute['name']) == 'ty'){
                    $type = $attribute['value'];
                    unset($file_entry[$key]);
                    break;
                }
            }

            //create the entry and fill data into database by executing doCreate()
            $entry_model = new ilBibliographicEntry($this->getFiletype());
            $entry_model->setType($type);
            $entry_model->setAttributes($file_entry);
            $entry_model->setBibliographicObjId($this->getId());
            $entry_model->doCreate();
        }
    }




    /**
     * Set Online.
     *
     * @param	boolean	$a_online	Online
     * @return	void
     */
    function setOnline($a_online)
    {
        $this->is_online = $a_online;
    }


    /**
     * Get Online.
     *
     * @return	boolean	Online
     */
    function getOnline()
    {
        return $this->is_online;
    }

}
