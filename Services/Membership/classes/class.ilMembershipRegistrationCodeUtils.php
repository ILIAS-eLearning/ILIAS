<?php declare(strict_types=1);
    
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
 
/**
 * Stores registration keys for key based registration on courses and groups
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMembership
 */
class ilMembershipRegistrationCodeUtils
{
    protected const CODE_LENGTH = 10;

    public static function handleCode(int $a_ref_id, string $a_type, string $a_code) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();

        $lng->loadLanguageModule($a_type);
        try {
            self::useCode($a_code, $a_ref_id);
            $title = ilObject::_lookupTitle(ilObject::_lookupObjectId($a_ref_id));
            $main_tpl->setOnScreenMessage('success', sprintf($lng->txt($a_type . "_admission_link_success_registration"), $title), true);
            ilUtil::redirect(ilLink::_getLink($a_ref_id));
        } catch (ilMembershipRegistrationException $e) {
            switch ($e->getCode()) {
                case ilMembershipRegistrationException::ADDED_TO_WAITINGLIST://added to waiting list
                    $main_tpl->setOnScreenMessage('success', $e->getMessage(), true);
                    break;
                case ilMembershipRegistrationException::OBJECT_IS_FULL://object is full
                    $main_tpl->setOnScreenMessage('failure', $lng->txt($a_type . "_admission_link_failure_membership_limited"), true);
                    break;
                case ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD://out of registration period
                    $main_tpl->setOnScreenMessage('failure', $lng->txt($a_type . "_admission_link_failure_registration_period"), true);
                    break;
                case ilMembershipRegistrationException::ADMISSION_LINK_INVALID://admission link is invalid
                    $main_tpl->setOnScreenMessage('failure', $lng->txt($a_type . "_admission_link_failure_invalid_code"), true);
                    break;
                case ilMembershipRegistrationException::REGISTRATION_INVALID_OFFLINE:
                    $main_tpl->setOnScreenMessage('failure', $lng->txt($a_type . '_admission_link_failure_offline'), true);
                    break;
                case ilMembershipRegistrationException::REGISTRATION_INVALID_AVAILABILITY:
                    $main_tpl->setOnScreenMessage('failure', $lng->txt($a_type . '_admission_link_failure_availability'), true);
                    break;
                default:
                    $main_tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                    break;
            }
            $parent_id = $tree->getParentId($a_ref_id);
            ilUtil::redirect(ilLink::_getLink($parent_id));
        }
    }

    /**
     * Use a registration code and assign the logged in user
     * to the (parent) course/group that offer the code.
     * @param int    $a_endnode Reference id of node in tree
     * @throws ilDatabaseException
     * @throws ilMembershipRegistrationException
     * @throws ilObjectNotFoundException
     * @todo: throw an error if registration fails (max members, availibility...)
     */
    protected static function useCode(string $a_code, int $a_endnode) : void
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilUser = $DIC->user();

        $obj_ids = self::lookupObjectsByCode($a_code);

        if (!$obj_ids) {
            throw new ilMembershipRegistrationException(
                'Admission code is not valid',
                ilMembershipRegistrationException::ADMISSION_LINK_INVALID
            );
        }

        foreach ($tree->getPathId($a_endnode) as $ref_id) {
            if (in_array(ilObject::_lookupObjId($ref_id), $obj_ids)) {
                $member_obj = ilObjectFactory::getInstanceByRefId($ref_id, false);
                if ($member_obj instanceof ilObjCourse) {
                    $member_obj->register($ilUser->getId(), ilCourseConstants::CRS_MEMBER);
                }
                if ($member_obj instanceof ilObjGroup) {
                    $member_obj->register($ilUser->getId(), ilParticipants::IL_GRP_MEMBER, true);
                }
            }
        }
    }

    /**
     * Generate new registration key
     */
    public static function generateCode() : string
    {
        // missing : 01iloO
        $map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

        $code = "";
        $max = strlen($map) - 1;
        for ($loop = 1; $loop <= self::CODE_LENGTH; $loop++) {
            $code .= $map[random_int(0, $max)];
        }
        return $code;
    }

    /**
     * Get all objects with enabled access codes
     * @return int[]
     */
    protected static function lookupObjectsByCode(string $a_code) : array
    {
        return array_merge(
            ilObjGroup::lookupObjectsByCode($a_code),
            ilObjCourse::lookupObjectsByCode($a_code)
        );
    }
}
