<?php

/**
 * @author JKN Inc. <itstaff@cpkn.ca>
 * @version $Id$
 *
 * @ingroup Services
 */

abstract class ilLPGradebook
{

    /** @var \ilTemplate */
    protected $tpl;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \ilTabsGUI */
    protected $tabs;

    /** @var \ilUser */
    protected $user;

    /** @var \ilLocator */
    protected $ilLocator;

    /** @var \ilDB */
    protected $ilDB;


    public function __construct($obj_id)
    {
        global $DIC;

        $this->lng = $DIC["lng"];
        $this->tpl = $DIC["tpl"];
        $this->locator = $DIC["ilLocator"];
        $this->db = $DIC["ilDB"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->access = $DIC["ilAccess"];
        $this->obj_id = $obj_id;
        $this->tree = $DIC["tree"];
    }


    public static function lookupRefId($a_id)
    {
        global $DIC;
        $set = $DIC["ilDB"]->query(
            "SELECT ref_id FROM object_reference WHERE " .
                "obj_id = " . $DIC["ilDB"]->quote($a_id, "integer")
        );
        $rec = $DIC["ilDB"]->fetchAssoc($set);
        return (int)$rec["ref_id"];
    }

    public function getGradebookVersions()
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookRevisionConfig.php');
        return ilGradebookRevisionConfig::where(
            [
                'deleted' => NULL,
                'gradebook_id' => $this->getGradebookId()
            ]
        )->orderBy('create_date', 'DESC')->get();
    }

    public function getLatestGradebookRevision()
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookRevisionConfig.php');
        $revision = ilGradebookRevisionConfig::where(['gradebook_id' => $this->getGradebookId()])
            ->where(['deleted' => null])->orderBy('id', 'desc')->first();
        return is_object($revision) ? $revision : new ilGradebookRevisionConfig();
    }

    /**
     * @param $usr_id
     * @return ActiveRecord
     */
    public function getUsersLatestRevision($usr_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookRevisionConfig.php');
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradeTotalConfig.php');
        $gradebookId = $this->getGradebookId();
        $revision = ilGradebookGradeTotalConfig::where(
            [
                'deleted' => NULL,
                'usr_id' => $usr_id,
                'gradebook_id' => $gradebookId
            ]
        )->orderBy('last_update', 'desc')->first();
        

        return is_object($revision) && !is_null($revision->getRevisionId()) ? ilGradebookRevisionConfig::where(['revision_id' => $revision->getRevisionId(), 'gradebook_id' => $gradebookId])->first()
            : $this->getLatestGradebookRevision();
    }

    public function getGradebook()
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookConfig.php');
        return ilGradebookConfig::firstOrCreate($this->obj_id);
    }

    public function getGradebookId()
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookConfig.php');
        $gradebook = ilGradebookConfig::firstOrCreate($this->obj_id);
        return is_object($gradebook) ? $gradebook->getId() : -1;
    }

    public function isMember($obj_id, $usr_id)
    {
        $participants = ilParticipants::getInstanceByObjId($obj_id);
        return $participants->isMember($usr_id);
    }

    public function getLPUrlForObjId($obj_id)
    {
        $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', $this->lookupRefId($obj_id));
        $link = $this->ctrl->getLinkTargetByClass(array('ilRepositoryGUI'));
        $link .= '&baseClass=ilRepositoryGUI';
        return $link;
    }



    public function getCourseMembers()
    {
        include_once('./Services/Membership/classes/class.ilParticipants.php');
        $participants = ilParticipants::getInstanceByObjId($this->obj_id);
        $user_arr = [];
        foreach ($participants->getMembers() as $participant) {
            $user = new ilObjUser($participant);
            $user_arr[] = [
                'usr_id' => $participant,
                'login' => $user->getLogin(),
                'full_name' => $user->getFullname()
            ];
        }
        return $user_arr;
    }
}
