<?php

use ILIAS\UI\Component\Symbol\Avatar\Avatar;
use ILIAS\UI\Factory;

/**
 * Class ilUserAvatarResolver
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUserAvatarResolver
{
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $firstname;
    /**
     * @var string
     */
    private $lastname;
    /**
     * @var bool
     */
    private $has_public_profile = false;
    /**
     * @var bool
     */
    private $has_public_upload = false;
    /**
     * @var string
     */
    private $uploaded_file;
    /**
     * @var string
     */
    private $abbreviation;
    /**
     * @var bool
     */
    private $force_image = false;
    /**
     * @var string
     */
    private $size = 'small';
    /**
     * @var Factory
     */
    protected $ui;

    /**
     * @var bool
     */
    protected $letter_avatars_activated;
    
    /**
     *  constructor.
     * @param int $user_id
     */
    public function __construct(int $user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->ui = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->user_id = $user_id;
        $this->letter_avatars_activated = (bool) $DIC->settings()->get('letter_avatars');
        $this->init();
    }

    private function init() : void
    {
        $in = $this->db->in('usr_pref.keyword', array('public_upload', 'public_profile'), false, 'text');
        $res = $this->db->queryF(
            "
			SELECT usr_pref.*, ud.login, ud.firstname, ud.lastname
			FROM usr_data ud LEFT JOIN usr_pref ON usr_pref.usr_id = ud.usr_id AND $in
			WHERE ud.usr_id = %s",
            array('integer'),
            array($this->user_id)
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->login = $row['login'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];

            switch ($row['keyword']) {
                case 'public_upload':
                    $this->has_public_upload = $row['value'] === 'y';
                    break;
                case 'public_profile':
                    $this->has_public_profile = ($row['value'] === 'y' || $row['value'] === 'g');
                    break;
            }
        }

        // Uploaded file
        $webspace_dir = '';
        if (defined('ILIAS_MODULE')) {
            $webspace_dir = ('.' . $webspace_dir);
        }
        $webspace_dir .= ('./' . ltrim(ilUtil::getWebspaceDir(), "./"));

        $image_dir = $webspace_dir . '/usr_images';
        $this->uploaded_file = $image_dir . '/usr_' . $this->user_id . '.jpg';

        if ($this->has_public_profile) {
            $this->abbreviation = ilStr::subStr($this->firstname, 0, 1) . ilStr::subStr($this->lastname, 0, 1);
        } else {
            $this->abbreviation = ilStr::subStr($this->login, 0, 2);
        }
    }

    private function useUploadedFile() : bool
    {
        return (($this->has_public_upload && $this->has_public_profile) || $this->force_image) && is_file($this->uploaded_file);
    }

    /**
     * @param bool $name_as_text_visible_closely if the name is set as text close to the Avatar, the alternative
     *                                           text for screenreaders will be set differently, to reduce redundancy
     *                                           for screenreaders. See rules on the Avatar Symbol in the UI Components
     * @return Avatar
     */
    public function getAvatar(bool $name_as_set_as_text_closely = false) : Avatar
    {
        if ($name_as_set_as_text_closely) {
            $alternative_text = $this->lng->txt("user_avatar");
        } elseif ($this->user_id == $this->user->getId() && !$this->user::_isAnonymous($this->user_id)) {
            $alternative_text = $this->lng->txt("current_user_avatar");
        } else {
            $alternative_text = $this->lng->txt("user_avatar_of") . " " . $this->login;
        }

        if ($this->useUploadedFile()) {
            return $this->ui->symbol()->avatar()->picture($this->uploaded_file, $this->login)
                            ->withAlternativeText($alternative_text);
        }
    
        if ($this->letter_avatars_activated === false) {
            return $this->ui->symbol()->avatar()->picture(
                \ilUtil::getImagePath('no_photo_xsmall.jpg'),
                ilObjUser::_lookupLogin($this->user_id)
            );
        }

        return $this->ui->symbol()->avatar()->letter($this->login)->withAlternativeText($alternative_text);
    }

    public function getLegacyPictureURL() : string
    {
        global $DIC;
        if ($this->useUploadedFile()) {
            return $this->uploaded_file . '?t=' . rand(1, 99999);
        }
        /** @var $avatar ilUserAvatarBase */

        $avatar = $DIC["user.avatar.factory"]->avatar($this->size);
        $avatar->setName($this->abbreviation);
        $avatar->setUsrId($this->user_id);

        return $avatar->getUrl();
    }

    /**
     * @param bool $force_image
     */
    public function setForcePicture(bool $force_image) : void
    {
        $this->force_image = $force_image;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size) : void
    {
        if ($size === 'small' || $size === 'big') {
            $size = 'xsmall';
        }

        $this->size = $size;
    }
}
