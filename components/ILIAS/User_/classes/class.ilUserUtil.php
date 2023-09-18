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

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ilMyStaffCachedAccessDecorator;

/**
 * Class ilUserUtil
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserUtil
{
    /**
     * Default behaviour is:
     * - lastname, firstname if public profile enabled
     * - [loginname] (always)
     * modifications by jposselt at databay . de :
     * if $a_user_id is an array of user ids the method returns an array of
     * 'id' => 'NamePresentation' pairs.
     * @param int|int[]    $a_user_id
     * @param string|array $a_ctrl_path
     * @return array|false|mixed
     * @throws ilWACException
     */
    public static function getNamePresentation(
        $a_user_id,
        bool $a_user_image = false,
        bool $a_profile_link = false,
        string $a_profile_back_link = '',
        bool $a_force_first_lastname = false,
        bool $a_omit_login = false,
        bool $a_sortable = true,
        bool $a_return_data_array = false,
        $a_ctrl_path = 'ilpublicuserprofilegui'
    ) {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];

        if (!is_array($a_ctrl_path)) {
            $a_ctrl_path = [$a_ctrl_path];
        }

        if (!($return_as_array = is_array($a_user_id))) {
            $a_user_id = [$a_user_id];
        }

        $sql = 'SELECT
					a.usr_id,
					firstname,
					lastname,
					title,
					login,
					b.value public_profile,
					c.value public_title
				FROM
					usr_data a
					LEFT JOIN
						usr_pref b ON
							(a.usr_id = b.usr_id AND
							b.keyword = %s)
					LEFT JOIN
						usr_pref c ON
							(a.usr_id = c.usr_id AND
							c.keyword = %s)
				WHERE ' . $ilDB->in('a.usr_id', $a_user_id, false, 'integer');

        $userrow = $ilDB->queryF($sql, ['text', 'text'], ['public_profile', 'public_title']);

        $names = [];

        $data = [];
        while ($row = $ilDB->fetchObject($userrow)) {
            $pres = '';
            $d = [
                'id' => (int) $row->usr_id,
                'title' => '',
                'lastname' => '',
                'firstname' => '',
                'img' => '',
                'link' => ''
            ];
            $has_public_profile = in_array($row->public_profile, ['y', 'g']);
            if ($a_force_first_lastname || $has_public_profile) {
                $title = '';
                if ($row->public_title == 'y' && $row->title) {
                    $title = $row->title . ' ';
                }
                $d['title'] = $title;
                if ($a_sortable) {
                    $pres = $row->lastname;
                    if (strlen($row->firstname)) {
                        $pres .= (', ' . $row->firstname . ' ');
                    }
                } else {
                    $pres = $title;
                    if (strlen($row->firstname)) {
                        $pres .= $row->firstname . ' ';
                    }
                    $pres .= ($row->lastname . ' ');
                }
                $d['firstname'] = $row->firstname;
                $d['lastname'] = $row->lastname;
            }
            $d['login'] = $row->login;
            $d['public_profile'] = $has_public_profile;


            if (!$a_omit_login) {
                $pres .= '[' . $row->login . ']';
            }

            if ($a_profile_link && $has_public_profile) {
                $ilCtrl->setParameterByClass(end($a_ctrl_path), 'user_id', $row->usr_id);
                if ($a_profile_back_link != '') {
                    $ilCtrl->setParameterByClass(
                        end($a_ctrl_path),
                        'back_url',
                        rawurlencode($a_profile_back_link)
                    );
                }
                $link = $ilCtrl->getLinkTargetByClass($a_ctrl_path, 'getHTML');
                $pres = '<a href="' . $link . '">' . $pres . '</a>';
                $d['link'] = $link;
            }

            if ($a_user_image) {
                $img = ilObjUser::_getPersonalPicturePath($row->usr_id, 'xxsmall');
                $pres = '<img class="ilUserXXSmall" src="' . $img . '" alt="' . $lng->txt('icon') .
                    ' ' . $lng->txt('user_picture') . '" /> ' . $pres;
                $d['img'] = $img;
            }

            $names[$row->usr_id] = $pres;
            $data[$row->usr_id] = $d;
        }

        foreach ($a_user_id as $id) {
            if (!isset($names[$id]) || !$names[$id]) {
                $names[$id] = $lng->txt('usr_name_undisclosed');
            }
        }

        if ($a_return_data_array) {
            if ($return_as_array) {
                return $data;
            } else {
                return current($data);
            }
        }
        return $return_as_array ? $names : $names[$a_user_id[0]];
    }

    public static function hasPublicProfile(int $a_user_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            'SELECT value FROM usr_pref ' .
            ' WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer') .
            ' and keyword = ' . $ilDB->quote('public_profile', 'text')
        );
        $rec = $ilDB->fetchAssoc($set);

        return in_array($rec['value'] ?? '', ['y', 'g']);
    }


    /**
     * Get link to personal profile
     * Return empty string in case of not public profile
     */
    public static function getProfileLink(int $a_usr_id): string
    {
        global $DIC;
        $ctrl = $DIC['ilCtrl'];

        $public_profile = ilObjUser::_lookupPref($a_usr_id, 'public_profile');
        if ($public_profile != 'y' and $public_profile != 'g') {
            return '';
        }

        $ctrl->setParameterByClass('ilpublicuserprofilegui', 'user', $a_usr_id);
        return $ctrl->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
    }

    public static function getStartingPointAsUrl(): string
    {
        global $DIC;
        $starting_point_repository = new ilUserStartingPointRepository(
            $DIC['ilUser'],
            $DIC['ilDB'],
            $DIC['tree'],
            $DIC['rbacreview'],
            $DIC['ilSetting']
        );
        return $starting_point_repository->getStartingPointAsUrl();
    }
}
