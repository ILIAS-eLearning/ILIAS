<?php
declare(strict_types=1);

namespace ILIAS\MyStaff\ListCourses;

use ilObjCourse;
use ilObjUser;

/**
 * Class ilMStListCourse
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCourse
{
    public const MEMBERSHIP_STATUS_REQUESTED = 1;
    public const MEMBERSHIP_STATUS_WAITINGLIST = 2;
    public const MEMBERSHIP_STATUS_REGISTERED = 3;

    protected int $crs_ref_id;
    protected string $crs_title;
    protected int $usr_id;
    protected int $usr_reg_status;
    protected int $usr_lp_status;
    protected string $usr_login;
    protected string $usr_firstname;
    protected string $usr_lastname;
    protected string $usr_email;

    final public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    final public function setCrsRefId(int $crs_ref_id) : void
    {
        $this->crs_ref_id = $crs_ref_id;
    }

    final public function getCrsTitle() : string
    {
        return $this->crs_title;
    }

    final public function setCrsTitle(string $crs_title) : void
    {
        $this->crs_title = $crs_title;
    }

    final public function getUsrId() : int
    {
        return $this->usr_id;
    }

    final public function setUsrId(int $usr_id) : void
    {
        $this->usr_id = $usr_id;
    }

    final public function getUsrRegStatus() : int
    {
        return $this->usr_reg_status;
    }

    final public function setUsrRegStatus(int $usr_reg_status) : void
    {
        $this->usr_reg_status = $usr_reg_status;
    }

    final public function getUsrLpStatus() : int
    {
        return $this->usr_lp_status;
    }

    final public function setUsrLpStatus(int $usr_lp_status) : void
    {
        $this->usr_lp_status = $usr_lp_status;
    }

    final public function getUsrLogin() : string
    {
        return $this->usr_login;
    }

    final public function setUsrLogin(string $usr_login)
    {
        $this->usr_login = $usr_login;
    }

    final public function getUsrFirstname() : string
    {
        return $this->usr_firstname;
    }

    final public function setUsrFirstname(string $usr_firstname) : void
    {
        $this->usr_firstname = $usr_firstname;
    }

    final public function getUsrLastname() : string
    {
        return $this->usr_lastname;
    }

    final public function setUsrLastname(string $usr_lastname)
    {
        $this->usr_lastname = $usr_lastname;
    }

    final public function getUsrEmail() : string
    {
        return $this->usr_email;
    }

    final public function setUsrEmail(string $usr_email)
    {
        $this->usr_email = $usr_email;
    }

    //Other
    final public function returnIlUserObj() : ilObjUser
    {
        return new ilObjUser($this->usr_id);
    }

    final public function returnIlCourseObj() : ilObjCourse
    {
        return new ilObjCourse($this->crs_ref_id);
    }

    final public static function getMembershipStatusText(int $status) : string
    {
        global $DIC;

        switch ($status) {
            case self::MEMBERSHIP_STATUS_WAITINGLIST:
                return $DIC->language()->txt('mst_memb_status_waitinglist');
                break;

            case self::MEMBERSHIP_STATUS_REGISTERED:
                return $DIC->language()->txt('mst_memb_status_registered');
                break;

            case self::MEMBERSHIP_STATUS_REQUESTED:
                return $DIC->language()->txt('mst_memb_status_requested');
                break;
        }

        return "";
    }
}
