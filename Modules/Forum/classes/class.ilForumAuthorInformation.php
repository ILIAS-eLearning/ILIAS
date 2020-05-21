<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @var \ilLanguage|null
     */
    protected $lng;

    /**
     * @var \ilLanguage
     */
    protected $globalLng;

    /**
     * @var \ilObjUser
     */
    protected $globalUser;

    /**
     * @param int    $author_id
     * @param int    $display_id
     * @param string $alias
     * @param string $import_name
     * @param array  $public_profile_link_attributes
     * @param \ilLanguage|null  $lng
     */
    public function __construct($author_id, $display_id, $alias, $import_name, array $public_profile_link_attributes = array(), \ilLanguage $lng = null)
    {
        global $DIC;

        $this->globalUser = $DIC->user();
        $this->globalLng = $DIC->language();

        $this->author_id = $author_id;
        $this->display_id = $display_id;
        $this->alias = $alias;
        $this->import_name = $import_name;
        $this->public_profile_link_attributes = $public_profile_link_attributes;
        $this->lng = $lng;

        $this->init();
    }

    /**

     */
    protected function initUserInstance()
    {
        if (is_numeric($this->display_id) && $this->display_id > 0) {
            // Try to read user instance from preloaded cache array
            $this->author = ilForumAuthorInformationCache::getUserObjectById($this->display_id);
            if (!$this->author) {
                // Get a user instance from forum module's cache method
                $this->author = ilObjForumAccess::getCachedUserInstance($this->display_id);
            }
        }

        if (!$this->author) {
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
        return $this->doesAuthorAccountExists() && $this->getAuthor()->isAnonymous();
    }

    /**
     * @return bool
     */
    protected function isCurrentUserSessionLoggedIn()
    {
        return !$this->globalUser->isAnonymous();
    }

    /**
     * @param bool $with_profile_link
     */
    protected function buildAuthorProfileLink($with_profile_link = false)
    {
        $link = '';

        if ($with_profile_link && $this->public_profile_link_attributes) {
            $link = '<a';

            foreach ($this->public_profile_link_attributes as $attr => $value) {
                $link .= ' ' . $attr . '="' . $value . '"';
            }

            $link .= '>';
        }

        $linked_login = $link . $this->author_short_name;
        $link .= $this->author_name;

        if ($with_profile_link && $this->public_profile_link_attributes) {
            $link .= '</a>';
            $linked_login .= '</a>';
        }

        $this->linked_public_name = $link;
        $this->linked_short_name = $linked_login;
    }

    /**
     *
     */
    protected function init()
    {
        $translationLanguage = $this->globalLng;
        if ($this->lng instanceof \ilLanguage) {
            $translationLanguage = $this->lng;
        }

        $this->initUserInstance();

        if ($this->doesAuthorAccountExists()) {
            if (!$this->isAuthorAnonymous()
                && (($this->isCurrentUserSessionLoggedIn()
                        && $this->getAuthor()->getPref('public_profile') == 'y')
                    || $this->getAuthor()->getPref('public_profile') == 'g')
            ) {
                // Author is NOT anonymous and (the current user session is logged in and the profile is public (y) or the profile is globally public (g))
                $this->author_name = $this->getAuthor()->getPublicName();
                $this->author_short_name = $this->getAuthor()->getLogin();

                if ($this->getAuthor()->getPref('public_upload') == 'y') {
                    $this->profilePicture = $this->getUserImagePath($this->getAuthor());
                } else {
                    $this->profilePicture = $this->getAvatarImageSource(
                        ilStr::subStr($this->getAuthor()->getFirstname(), 0, 1) . ilStr::subStr($this->getAuthor()->getLastname(), 0, 1),
                        $this->getAuthor()->getId()
                    );
                }

                if ($this->getAuthor()->getPref('public_gender') != 'y') {
                    $this->getAuthor()->setGender('');
                }

                $this->buildAuthorProfileLink(true);
            } else {
                $this->getAuthor()->setGender('');
                $this->author_short_name = $this->author_name = $this->getAuthor()->getLogin();
                $this->buildAuthorProfileLink(false);
                $this->profilePicture = $this->getAvatarImageSource($this->author_short_name, $this->getAuthor()->getId());
            }
        } elseif ($this->display_id > 0 && !$this->doesAuthorAccountExists() && strlen($this->alias)) {
            // The author does not use a pseudonym, but the id does not exist anymore (deleted, lost on import etc.)
            // We have no import name,so we check the pseudonym
            $this->author_short_name = $this->author_name = $translationLanguage->txt('deleted');
            $this->suffix = $translationLanguage->txt('deleted');
            $this->buildAuthorProfileLink(false);
            $this->profilePicture = $this->getAvatarImageSource($this->author_short_name);
        } elseif (strlen($this->import_name)) {
            // We have no user instance,so we check the import name
            $this->author_short_name = $this->author_name = $this->import_name . ' (' . $translationLanguage->txt('imported') . ')';
            $this->suffix = $translationLanguage->txt('imported');
            $this->buildAuthorProfileLink(false);
            $this->profilePicture = $this->getAvatarImageSource($this->author_short_name);
        } elseif (strlen($this->alias)) {
            // We have no import name,so we check the pseudonym
            $this->author_short_name = $this->author_name = $this->alias . ' (' . $translationLanguage->txt('frm_pseudonym') . ')';
            $this->suffix = $translationLanguage->txt('frm_pseudonym');
            $this->buildAuthorProfileLink(false);
            $this->profilePicture = $this->getAvatarImageSource($this->author_short_name);
        } else {
            // If we did not find a pseudonym, the author could not be determined
            $this->author_short_name = $this->author_name = $translationLanguage->txt('forums_anonymous');
            $this->buildAuthorProfileLink(false);
            $this->profilePicture = $this->getAvatarImageSource($this->author_short_name);
        }
    }

    /**
     * @param ilObjUser $user
     * @return string
     */
    protected function getUserImagePath(\ilObjUser $user)
    {
        if (!\ilContext::hasHTML()) {
            return'';
        }

        return $user->getPersonalPicturePath('xsmall');
    }

    /**
     * @param string $name
     * @param int $usrId
     * @return string
     */
    protected function getAvatarImageSource($name, $usrId = ANONYMOUS_USER_ID)
    {
        global $DIC;

        if (!\ilContext::hasHTML()) {
            return'';
        }

        /** @var ilUserAvatar $avatar */
        $avatar = $DIC["user.avatar.factory"]->avatar('xsmall');
        $avatar->setUsrId($usrId);
        $avatar->setName(ilStr::subStr($name, 0, 2));

        return $avatar->getUrl();
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
        if (!$without_short_name) {
            return $this->author_name;
        } else {
            return trim(preg_replace('/\(' . preg_quote($this->getAuthorShortName()) . '\)/', '', $this->author_name));
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
