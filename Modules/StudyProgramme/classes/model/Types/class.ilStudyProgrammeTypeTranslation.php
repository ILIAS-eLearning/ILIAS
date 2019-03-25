<?php declare(strict_types = 1);
/**
 * Class ilStudyProgrammeTypeTranslation
 * This class represents a translation for a given ilStudyProgrammeType object and language.
 *
 * @author: Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeTranslation
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
    protected $prg_type_id = 0;

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
    protected $member  = '';

	/**
	 *
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      3500
	 */
	protected $value = '';

    public function __construct(int $id)
    {
	    $this->id = $id;
    }

	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId(int $id)
	{
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getPrgTypeId() : int
	{
		return $this->prg_type_id;
	}


	/**
	 * @param int $prg_type_id
	 */
	public function setPrgTypeId(int $prg_type_id) {
		$this->prg_type_id = $prg_type_id;
	}


	/**
	 * @return string
	 */
	public function getLang() : string
	{
		return $this->lang;
	}


	/**
	 * @param string $lang
	 */
	public function setLang(string $lang)
	{
		$this->lang = $lang;
	}


	/**
	 * @return string
	 */
	public function getMember() : string
	{
		return $this->member;
	}


	/**
	 * @param string $member
	 */
	public function setMember(string $member)
	{
		$this->member = $member;
	}


	/**
	 * @return string
	 */
	public function getValue() : string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue(string $value) {
		$this->value = $value;
	}

}