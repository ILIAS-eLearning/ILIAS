<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary actor class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryAct
{
	/**
	 * @var ilObjGlossary
	 */
	protected $glossary;

	/**
	 * @var ilObjUser acting user
	 */
	protected $user;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * construct
	 *
	 * @param
	 * @return
	 */
	protected function __construct(ilObjGlossary $a_glossary, ilObjUser $a_user)
	{
		global $DIC;

		$this->access = $DIC->access();
		$this->glossary = $a_glossary;
		$this->user = $a_user;
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return ilGlossaryAct
	 */
	static function getInstance(ilObjGlossary $a_glossary, ilObjUser $a_user)
	{
		return new self($a_glossary, $a_user);
	}

	/**
	 * Copy term
	 *
	 * @param ilObjGlossary $a_source_glossary
	 * @param int $a_term_id term id
	 */
	function copyTerm(ilObjGlossary $a_source_glossary, $a_term_id)
	{
		if (!$this->access->checkAccessOfUser($this->user->getId(), "write", "", $this->glossary->getRefId()))
		{
			return;
		}

		if (!$this->access->checkAccessOfUser($this->user->getId(), "read", "", $a_source_glossary->getRefId()))
		{
			return;
		}

		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		if (ilGlossaryTerm::_lookGlossaryID($a_term_id) != $a_source_glossary->getId())
		{
			return;
		}

		if ($this->glossary->getId() == $a_source_glossary->getId())
		{
			return;
		}

		ilGlossaryTerm::_copyTerm($a_term_id, $this->glossary->getId());
	}



}

?>