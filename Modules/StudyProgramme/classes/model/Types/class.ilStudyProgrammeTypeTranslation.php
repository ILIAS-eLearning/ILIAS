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
	 */
	protected $id;

    /**
     *
     * @var int
     */
    protected $prg_type_id = 0;

	/**
	 *
	 * @var string
	 */
    protected $lang = '';

	/**
	 *
	 * @var string
	 */
    protected $member  = '';

	/**
	 *
	 * @var string
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