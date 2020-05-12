<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Stores registration keys for key based registration on courses and groups
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
class ilMembershipRegistrationCodeUtils
{
    const CODE_LENGTH = 10;
    

    /**
     * Handle target parameter
     * @param object $a_target
     * @return
     */
    public static function handleCode($a_ref_id, $a_type, $a_code)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];
        include_once './Services/Link/classes/class.ilLink.php';
        $lng->loadLanguageModule($a_type);
        try {
            self::useCode($a_code, $a_ref_id);
            $title = ilObject::_lookupTitle(ilObject::_lookupObjectId($a_ref_id));
            ilUtil::sendSuccess(sprintf($lng->txt($a_type . "_admission_link_success_registration"), $title), true);
            ilUtil::redirect(ilLink::_getLink($a_ref_id));
        } catch (ilMembershipRegistrationException $e) {
            switch ($e->getCode()) {
                case ilMembershipRegistrationException::ADDED_TO_WAITINGLIST://added to waiting list
                    ilUtil::sendSuccess($e->getMessage(), true);
                    break;
                case ilMembershipRegistrationException::OBJECT_IS_FULL://object is full
                    ilUtil::sendFailure($lng->txt($a_type . "_admission_link_failure_membership_limited"), true);
                    break;
                case ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD://out of registration period
                    ilUtil::sendFailure($lng->txt($a_type . "_admission_link_failure_registration_period"), true);
                    break;
                case ilMembershipRegistrationException::ADMISSION_LINK_INVALID://admission link is invalid
                    ilUtil::sendFailure($lng->txt($a_type . "_admission_link_failure_invalid_code"), true);
                    break;
                case ilMembershipRegistrationException::REGISTRATION_INVALID_OFFLINE:
                    ilUtil::sendFailure($lng->txt($a_type . '_admission_link_failure_offline'), true);
                    break;
                case ilMembershipRegistrationException::REGISTRATION_INVALID_AVAILABILITY:
                    ilUtil::sendFailure($lng->txt($a_type . '_admission_link_failure_availability'), true);
                    break;
                default:
                    ilUtil::sendFailure($e->getMessage(), true);
                    break;
            }
            $parent_id = $tree->getParentId($a_ref_id);
            ilUtil::redirect(ilLink::_getLink($parent_id));
        }
    }
    
    
    
    /**
     * Use a registration code and assign the logged in user
     * to the (parent) course/group that offer the code.
     *
     * @todo: throw an error if registration fails (max members, availibility...)
     *
     * @param string $a_code
     * @param int $a_endnode Reference id of node in tree
     * @return
     */
    protected static function useCode($a_code, $a_endnode)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];
        
        $obj_ids = self::lookupObjectsByCode($a_code);

        if (!$obj_ids) {
            include_once './Services/Membership/exceptions/class.ilMembershipRegistrationException.php';
            throw new ilMembershipRegistrationException('Admission code is not valid', ilMembershipRegistrationException::ADMISSION_LINK_INVALID);
        }

        foreach ($tree->getPathId($a_endnode) as $ref_id) {
            if (in_array(ilObject::_lookupObjId($ref_id), $obj_ids)) {
                $factory = new ilObjectFactory();
                $member_obj = $factory->getInstanceByRefId($ref_id, false);
                if ($member_obj instanceof ilObjCourse) {
                    $member_obj->register($ilUser->getId(), ilCourseConstants::CRS_MEMBER);
                }
                if ($member_obj instanceof ilObjGroup) {
                    $member_obj->register($ilUser->getId(), IL_GRP_MEMBER, true);
                }
            }
        }
    }
    
    /**
     * Generate new registration key
     * @return
     */
    public static function generateCode()
    {
        // missing : 01iloO
        $map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
        
        $code = "";
        $max = strlen($map) - 1;
        for ($loop = 1; $loop <= self::CODE_LENGTH; $loop++) {
            $code .= $map[mt_rand(0, $max)];
        }
        return $code;
    }
    
    /**
     * Get all objects with enabled access codes
     * @param string $a_code
     * @return
     */
    protected static function lookupObjectsByCode($a_code)
    {
        include_once './Modules/Group/classes/class.ilObjGroup.php';
        include_once './Modules/Course/classes/class.ilObjCourse.php';
        
        return array_merge(
            ilObjGroup::lookupObjectsByCode($a_code),
            ilObjCourse::lookupObjectsByCode($a_code)
        );
    }
}
