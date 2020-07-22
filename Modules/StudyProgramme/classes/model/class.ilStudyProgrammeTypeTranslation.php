<?php
require_once("./Services/ActiveRecord/class.ActiveRecord.php");
/**
 * Class ilStudyProgrammeTypeTranslation
 * This class represents a translation for a given ilStudyProgrammeType object and language.
 *
 * @author: Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeTranslation extends ActiveRecord
{
    /**
     *
     * @var int
     *
     * @con_is_primary  true
     * @con_sequence    true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $id;

    /**
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $prg_type_id;

    /**
     *
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      4
     */
    protected $lang = '';

    /**
     *
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      32
     */
    protected $member;

    /**
     *
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      3500
     */
    protected $value;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return "prg_translations";
    }

    public function __construct($primary_key = 0, $a_lang_code = '')
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $this->log = $ilLog;

        parent::__construct($primary_key);
    }

    /**
     * Removes all translations to a specific StudyProgramme-Type
     *
     * @param $type_id | Id of the studyProgrammeType
     */
    public static function deleteAllTranslations($type_id)
    {
        $translations = self::where(array('prg_type_id' => $type_id))->get();
        foreach ($translations as $translation) {
            $translation->delete();
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getPrgTypeId()
    {
        return $this->prg_type_id;
    }


    /**
     * @param int $prg_type_id
     */
    public function setPrgTypeId($prg_type_id)
    {
        $this->prg_type_id = $prg_type_id;
    }


    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }


    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }


    /**
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }


    /**
     * @param string $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
