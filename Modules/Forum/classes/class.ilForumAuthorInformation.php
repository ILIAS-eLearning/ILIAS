<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Forum/classes/class.ilObjForumAccess.php';
require_once 'Modules/Forum/classes/class.ilForumAuthorInformationCache.php';

/**
 * ilForumAuthorInformation
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumAuthorInformation
{
	/**
	 * @var int|ilObjUser
	 */
	protected $authorEntity;

	/**
	 * @var string
	 */
	protected $pseudonym;

	/**
	 * @var string
	 */
	protected $importName;

	/**
	 * @var array
	 */
	protected $publicProfileLinkAttributes = array();

	/**
	 * @var string
	 */
	protected $authorName;

	/**
	 * @var string
	 */
	protected $authorShortName;

	/**
	 * @var string
	 */
	protected $linked_public_name;

	/**
	 * @var string
	 */
	protected $linked_short_name;

	/**
	 * @var bool
	 */
	protected $is_pseudonym = false;

	/**
	 * @var string
	 */
	protected $profilePicture;

	/**
	 * @var ilObjUser
	 */
	protected $author;

	/**
	 * @var array
	 */
	protected $files = array();

	/**
	 * @param ilObjUser|int $authorEntity
	 * @param			   $pseudonym
	 * @param			   $importName
	 * @param array		 $publicProfileLinkAttributes
	 */
	public function __construct($authorEntity, $pseudonym, $importName, array $publicProfileLinkAttributes = array())
	{
		$this->authorEntity                = $authorEntity;
		$this->pseudonym                   = $pseudonym;
		$this->importName                  = $importName;
		$this->publicProfileLinkAttributes = $publicProfileLinkAttributes;

		$this->init();
	}

	/**

	 */
	protected function initUserInstance()
	{
		if($this->authorEntity instanceof ilObjUser && $this->authorEntity->getId())
		{
			$this->author = $this->authorEntity;
		}
		else if(is_numeric($this->authorEntity) && $this->authorEntity)
		{
			// Try to read user instance from preloaded cache array
			$this->author = ilForumAuthorInformationCache::getUserObjectById($this->authorEntity);

			if(!$this->author)
			{
				// Get a user instance from forum module's cache method
				$this->author = ilObjForumAccess::getCachedUserInstance($this->authorEntity);
			}
		}

		if(!$this->author)
		{
			$this->author = new ilObjUser();
			$this->author->setId(0);
			$this->author->setPref('public_profile', 'n');
			$this->author->setGender('');
		}
	}

	/**
	 * @return bool
	 */
	protected function doesAuthorAccountExists()
	{
		return $this->getAuthor() instanceof ilObjUser && $this->getAuthor()->getId();
	}

	/**
	 * @return bool
	 */
	protected function isAuthorAnonymous()
	{
		return $this->doesAuthorAccountExists() && $this->getAuthor()->getId() == ANONYMOUS_USER_ID;
	}

	/**
	 * @return bool
	 */
	protected function isCurrentUserSessionLoggedIn()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		return $ilUser->getId() != ANONYMOUS_USER_ID;
	}

	/**
	 * @param bool $with_profile_link
	 */
	protected function buildAuthorProfileLink($with_profile_link = false)
	{
		$link = '';

		if($with_profile_link && $this->publicProfileLinkAttributes)
		{
			$link = '<a';

			foreach($this->publicProfileLinkAttributes as $attr => $value)
			{
				$link .= ' ' . $attr . '="' . $value . '"';
			}

			$link .= '>';
		}

		$linked_login = $link . $this->authorShortName;
		$link .= $this->authorName;

		if($with_profile_link && $this->publicProfileLinkAttributes)
		{
			$link .= '</a>';
			$linked_login .= '</a>';
		}

		$this->linked_public_name = $link;
		$this->linked_short_name  = $linked_login;
	}

	/** 
	 * 
	 */
	protected function init()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		include_once 'Modules/Forum/classes/class.ilObjForumAccess.php';

		$this->initUserInstance();

		if($this->doesAuthorAccountExists())
		{
			if(!$this->isAuthorAnonymous() &&
				($this->isCurrentUserSessionLoggedIn() && $this->getAuthor()->getPref('public_profile') == 'y' || $this->getAuthor()->getPref('public_profile') == 'g')
			)
			{
				// Author is NOT anonymous and (the current user session is logged in and the profile is public (y) or the profile is globally public (g))
				$this->authorName      = $this->getAuthor()->getPublicName();
				$this->authorShortName = $this->getAuthor()->getLogin();

				if($this->getAuthor()->getPref('public_upload') == 'y')
				{
					$this->profilePicture = $this->getAuthor()->getPersonalPicturePath('xsmall');
				}
				else
				{
					$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
				}

				if($this->getAuthor()->getPref('public_gender') != 'y')
				{
					$this->getAuthor()->setGender('');
				}

				$this->buildAuthorProfileLink(true);
			}
			else
			{
				$this->getAuthor()->setGender('');
				$this->authorShortName = $this->authorName = $this->getAuthor()->getLogin();
				$this->buildAuthorProfileLink(false);
				$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
			}
		}
		else if(strlen($this->importName))
		{
			// We have no user instance,so we check the import name
			$this->authorShortName = $this->authorName = $this->importName ?
				$this->importName . ' (' . $lng->txt('imported') . ')' :
				$lng->txt('unknown');
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
		else if(strlen($this->pseudonym))
		{
			// We have no import name,so we check the pseudonym
			$this->authorShortName = $this->authorName   = $this->pseudonym . ' (' . $lng->txt('frm_pseudonym') . ')';
			$this->is_pseudonym = true;
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
		else
		{
			// If we did not find a pseudonym, the author could not be determined
			$this->authorShortName = $this->authorName = $lng->txt('forums_anonymous');
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
	}

	/**
	 * @return string
	 */
	public function getProfilePicture()
	{
		return $this->profilePicture;
	}

	/**
	 * @return ilObjUser
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @param bool $without_short_name
	 * @return string
	 */
	public function getAuthorName($without_short_name = false)
	{
		if(!$without_short_name)
		{
			return $this->authorName;
		}
		else
		{
			return trim(preg_replace('/\(?'.$this->getAuthorShortName().'\)?/', '', $this->authorName));
		}
	}

	/**
	 * @return string
	 */
	public function getAuthorShortName()
	{
		return $this->authorShortName;
	}

	/**
	 * @return string
	 */
	public function getLinkedAuthorName()
	{
		return $this->linked_public_name;
	}

	/**
	 * @return string
	 */
	public function getLinkedAuthorShortName()
	{
		return $this->linked_short_name;
	}


	/**
	 * @return bool
	 */
	public function isPseudonymUsed()
	{
		return $this->is_pseudonym;
	}
}