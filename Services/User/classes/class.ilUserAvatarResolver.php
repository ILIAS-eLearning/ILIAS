<?php

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

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
     * @var bool
     */
    private $has_public_upload = false;
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
     * @var Avatar
     */
    private $avatar;

    /**
     *  constructor.
     * @param int $user_id
     */
    public function __construct(int $user_id)
    {
        global $DIC;
        $this->db      = $DIC->database();
        $this->ui      = $DIC->ui()->factory();
        $this->user_id = $user_id;
        $this->init();
    }

    private function init() : void
    {
        $in  = $this->db->in('usr_pref.keyword', array('public_upload', 'public_profile'), false, 'text');
        $res = $this->db->queryF(
            "
			SELECT usr_pref.*, ud.login, ud.firstname, ud.lastname
			FROM usr_data ud LEFT JOIN usr_pref ON usr_pref.usr_id = ud.usr_id AND $in
			WHERE ud.usr_id = %s",
            array('integer'),
            array($this->user_id)
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->login     = $row['login'];
            $this->firstname = $row['firstname'];
            $this->lastname  = $row['lastname'];

            switch ($row['keyword']) {
                case 'public_upload':
                    $this->has_public_upload = $row['value'] === 'y';
                    break;
                case 'public_profile':
                    $this->has_public_upload = ($row['value'] === 'y' || $row['value'] === 'g');
                    break;
            }
        }

        if ($this->has_public_upload) {
            $webspace_dir = '';
            if (defined('ILIAS_MODULE')) {
                $webspace_dir = ('.' . $webspace_dir);
            }
            $webspace_dir .= ('./' . ltrim(ilUtil::getWebspaceDir(), "./"));

            $image_dir  = $webspace_dir . '/usr_images';
            $thumb_file = $image_dir . '/usr_' . $this->user_id . '.jpg';

            $this->avatar = $this->ui->symbol()->avatar()->image($thumb_file, $this->login);
        } else {
            $this->avatar = $this->ui->symbol()->avatar()->letter($this->login);
        }
    }

    public function getAvatar() : Avatar
    {
        return $this->avatar;
    }

}
