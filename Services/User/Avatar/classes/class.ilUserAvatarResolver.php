<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropSquare;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;
use ILIAS\UI\Factory;

/**
 * Class ilUserAvatarResolver
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUserAvatarResolver
{
    private ilDBInterface $db;
    private \ILIAS\ResourceStorage\Services $irss;
    private Factory $ui;
    private ilLanguage $lng;
    private bool $letter_avatars_activated;
    private ilUserProfilePictureDefinition $flavour_definition;
    private string $size;
    private array $available_sizes = [];
    private bool $is_current_user;
    private string $login_name;
    private ilObjUser $for_user;
    private bool $has_public_upload = false;
    private bool $has_public_profile = false;
    private ilUserAvatarFactory $avatar_factory;
    private string $abbreviation = '';
    private ?string $rid = null;
    private bool $force_image = false;

    public function __construct(
        private int $user_id
    ) {
        global $DIC;
        $this->is_current_user = $DIC->user()->getId() === $this->user_id;
        $this->for_user = $this->is_current_user ? $DIC->user() : new ilObjUser($this->user_id);

        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
        $this->ui = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->avatar_factory = $DIC["user.avatar.factory"];

        $this->letter_avatars_activated = (bool) $DIC->settings()->get('letter_avatars');
        $this->flavour_definition = new ilUserProfilePictureDefinition();
        $this->size = 'small';
        $this->readUserSettings();
    }

    private function readUserSettings(): void
    {
        $in = $this->db->in('usr_pref.keyword', ['public_upload', 'public_profile'], false, 'text');
        $res = $this->db->queryF(
            "
			SELECT usr_data.rid, usr_pref.*
			FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND $in
			WHERE usr_data.usr_id = %s",
            ['integer'],
            [$this->user_id]
        );

        while ($row = $this->db->fetchAssoc($res)) { // MUST be loop
            $this->rid = $row['rid'] ?? null;
            switch ($row['keyword']) {
                case 'public_upload':
                    $this->has_public_upload = $row['value'] === 'y';
                    break;
                case 'public_profile':
                    $this->has_public_profile = ($row['value'] === 'y' || $row['value'] === 'g');
                    break;
            }
        }

        if ($this->has_public_profile) {
            $sub_str_firstname = ilStr::subStr($this->for_user->getFirstname(), 0, 1);
            $sub_str_lastname = ilStr::subStr($this->for_user->getLastname(), 0, 1);
            $this->abbreviation = $sub_str_firstname . $sub_str_lastname;
        } else {
            $this->abbreviation = ilStr::subStr($this->for_user->getLogin(), 0, 2);
        }
    }

    public function hasProfilePicture(): bool
    {
        if (!$this->force_image) {
            if (!$this->has_public_profile || !$this->has_public_upload) {
                return false;
            }
        }

        // IRSS
        if ($this->rid !== null && $this->irss->manage()->find($this->rid) !== null) {
            return true;
        }
        // LEGACY
        return is_file($this->resolveLegacyPicturePath());
    }

    private function resolveLegacyPicturePath(): string
    {
        // Legacy Uploaded file - can be removed in ILIAS 10
        $webspace_dir = ('./' . ltrim(ilFileUtils::getWebspaceDir(), "./"));
        $image_dir = $webspace_dir . '/usr_images';
        return $image_dir . '/usr_' . $this->user_id . '.jpg';
    }

    private function resolveProfilePicturePath(): string
    {
        $rid = $this->irss->manage()->find($this->rid);
        $flavour = $this->irss->flavours()->get($rid, $this->flavour_definition);
        $urls = $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray(false);

        $available_sizes = array_flip(array_keys($this->flavour_definition->getSizes()));
        $size_index = $available_sizes[$this->size];

        return $urls[$size_index] ?? '';
    }

    /**
     * @param bool $name_as_set_as_text_closely  if the name is set as text close to the Avatar, the alternative
     *                                           text for screenreaders will be set differently, to reduce redundancy
     *                                           for screenreaders. See rules on the Avatar Symbol in the UI Components
     */
    public function getAvatar(bool $name_as_set_as_text_closely = false): Avatar
    {
        if ($name_as_set_as_text_closely) {
            $alternative_text = $this->lng->txt("user_avatar");
        } elseif ($this->is_current_user && !$this->for_user->isAnonymous()) {
            $alternative_text = $this->lng->txt("current_user_avatar");
        } else {
            $alternative_text = $this->lng->txt("user_avatar_of") . " " . $this->for_user->getLogin();
        }

        if ($this->hasProfilePicture()) {
            $picture = ilWACSignedPath::signFile($this->getLegacyPictureURL());
            return $this->ui->symbol()->avatar()->picture(
                $picture,
                $this->for_user->getLogin()
            )->withLabel($alternative_text);
        }

        // Fallback Image
        if ($this->letter_avatars_activated === false) {
            return $this->ui->symbol()->avatar()->picture(
                \ilUtil::getImagePath('no_photo_xsmall.jpg'),
                $this->for_user->getLogin()
            );
        }

        return $this->ui->symbol()->avatar()->letter($this->abbreviation)->withLabel($alternative_text);
    }

    /**
     * This method returns the URL to the Profile Picture of a User.
     * Depending on Settings and the Availability of a Prodile Picture,
     * there's a Fallback to the Letter Avatar as well (as data-URL).
     *
     * @deprecated use getAvatar() instead
     */
    public function getLegacyPictureURL(): string
    {
        if ($this->hasProfilePicture()) {
            // IRSS
            if ($this->rid !== null) {
                return $this->resolveProfilePicturePath();
            }

            // LEGACY
            return $this->resolveLegacyPicturePath();
        }

        // LETTER AVATAR
        $avatar = $this->avatar_factory->avatar($this->size);
        $avatar->setName($this->abbreviation);
        $avatar->setUsrId($this->user_id);

        return $avatar->getUrl();
    }

    /**
     * There are places where we want wo show the Profile Picture of a User, even if the user
     * doesn't want to show it. (e.g. in Administration)
     */
    public function setForcePicture(bool $force_image): void
    {
        $this->force_image = $force_image;
    }

    /**
     * There are the Sizes  'big', 'small', 'xsmall', 'xxsmall', @see ilUserProfilePictureDefinition
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }
}
