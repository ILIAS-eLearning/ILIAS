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
	 * @var int
	 */
	protected $display_id;

	/**
	 * @var string
	 */
	protected $alias;

	/**
	 * @var string
	 */
	protected $import_name;

	/**
	 * @var array
	 */
	protected $public_profile_link_attributes = array();

	/**
	 * @var string
	 */
	protected $author_name;

	/**
	 * @var string
	 */
	protected $author_short_name;

	/**
	 * @var string
	 */
	protected $linked_public_name;

	/**
	 * @var string
	 */
	protected $linked_short_name;

	/**
	 * @var string
	 */
	protected $suffix = '';

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
	 * @var int
	 */
	protected $author_id;

	/**
	 * @param int    $author_id
	 * @param int    $display_id
	 * @param string $alias
	 * @param string $import_name
	 * @param array  $public_profile_link_attributes
	 */
	public function __construct($author_id, $display_id, $alias, $import_name, array $public_profile_link_attributes = array())
	{
		$this->author_id                      = $author_id;
		$this->display_id                     = $display_id;
		$this->alias                          = $alias;
		$this->import_name                    = $import_name;
		$this->public_profile_link_attributes = $public_profile_link_attributes;

		$this->init();
	}

	/**

	 */
	protected function initUserInstance()
	{
		if(is_numeric($this->display_id) && $this->display_id > 0)
		{
			// Try to read user instance from preloaded cache array
			$this->author = ilForumAuthorInformationCache::getUserObjectById($this->display_id);
			if(!$this->author)
			{
				// Get a user instance from forum module's cache method
				$this->author = ilObjForumAccess::getCachedUserInstance($this->display_id);
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

		return !$ilUser->isAnonymous();
	}

	/**
	 * @param bool $with_profile_link
	 */
	protected function buildAuthorProfileLink($with_profile_link = false)
	{
		$link = '';

		if($with_profile_link && $this->public_profile_link_attributes)
		{
			$link = '<a';

			foreach($this->public_profile_link_attributes as $attr => $value)
			{
				$link .= ' ' . $attr . '="' . $value . '"';
			}

			$link .= '>';
		}

		$linked_login = $link . $this->author_short_name;
		$link .= $this->author_name;

		if($with_profile_link && $this->public_profile_link_attributes)
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
				(
					(
						$this->isCurrentUserSessionLoggedIn() && $this->getAuthor()->getPref('public_profile') == 'y'
					) ||
					$this->getAuthor()->getPref('public_profile') == 'g')
			)
			{
				// Author is NOT anonymous and (the current user session is logged in and the profile is public (y) or the profile is globally public (g))
				$this->author_name       = $this->getAuthor()->getPublicName();
				$this->author_short_name = $this->getAuthor()->getLogin();

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
				$this->author_short_name = $this->author_name = $this->getAuthor()->getLogin();
				$this->buildAuthorProfileLink(false);
				$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
			}
		}
		else if($this->display_id > 0 && !$this->doesAuthorAccountExists() && strlen($this->alias))
		{
			// The author does not use a pseudonym, but the id does not exist anymore (deleted, lost on import etc.)
			// We have no import name,so we check the pseudonym  
			$this->author_short_name = $this->author_name = $this->alias . ' (' . $lng->txt('deleted') . ')';
			$this->suffix            = $lng->txt('deleted');
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
		else if(strlen($this->import_name))
		{
			// We have no user instance,so we check the import name
			$this->author_short_name = $this->author_name = $this->import_name . ' (' . $lng->txt('imported') . ')';
			$this->suffix            = $lng->txt('imported');
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
		else if(strlen($this->alias))
		{
			// We have no import name,so we check the pseudonym
			$this->author_short_name = $this->author_name = $this->alias . ' (' . $lng->txt('frm_pseudonym') . ')';
			$this->suffix            = $lng->txt('frm_pseudonym');
			$this->buildAuthorProfileLink(false);
			$this->profilePicture = ilUtil::getImagePath('no_photo_xsmall.jpg');
		}
		else
		{
			// If we did not find a pseudonym, the author could not be determined
			$this->author_short_name = $this->author_name = $lng->txt('forums_anonymous');
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
			return $this->author_name;
		}
		else
		{
			return trim(preg_replace('/\(' . $this->getAuthorShortName() . '\)/', '', $this->author_name));
		}
	}

	/**
	 * @return string
	 */
	public function getAuthorShortName()
	{
		return $this->author_short_name;
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
	public function hasSuffix()
	{
		return strlen($this->suffix);
	}

	/**
	 * @return string
	 */
	public function getSuffix()
	{
		return $this->suffix;
	}
}