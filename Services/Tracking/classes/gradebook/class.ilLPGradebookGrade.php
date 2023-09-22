<?php

/**
 * @author JKN Inc.
 * @copyright 2017
 */
include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebook.php");
class ilLPGradebookGrade extends ilLPGradebook
{
    /**
     * ilLPGradebookGrade constructor.
     * @param $obj_id
     */
    public function __construct($obj_id)
    {
        parent::__construct($obj_id);
    }

    /**
     * @param $usr_id
     * @return array|string
     */
    public function getUsersGrades($usr_id)
    {
        $lastest_revision = $this->getUsersLatestRevision($usr_id);
        //if a users groups are all set up properly move on and get their course layout.

        if ($lastest_revision->getRevisionId() !== NULL) {
            if ($this->userGroupCheck($usr_id, $lastest_revision)) {
                $course_layout = $this->getUsersCourseLayout($usr_id, $lastest_revision);
                $users_grade_data = [];
                foreach ($course_layout as $key => $revision_object) {
                    $users_grade_data['object_data'][] = $this->mapGradeData($revision_object, $usr_id);
                }
                $users_grade_data['overall_data'] = $this->getOverallUserGrades($usr_id);
                return $users_grade_data;
            }
        } else {
            throw new Exception('Please finish first Gradebook Revision before grading');
        }
    }

    /**
     * @param $usr_id
     * @param $old_revision_id
     * @param $new_revision_id
     */
    public function changeUsersRevision($usr_id, $old_revision_id, $new_revision_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookRevisionConfig.php');
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradeTotalConfig.php');
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookConfig.php');

        $gradebook = ilGradebookConfig::firstOrCreate($this->obj_id);

        $old_revision = ilGradebookRevisionConfig::where(['revision_id' => $old_revision_id, 'gradebook_id' => $gradebook->getId()])->first();
        $new_revision = ilGradebookRevisionConfig::where(['revision_id' => $new_revision_id, 'gradebook_id' => $gradebook->getId()])->first();


        //first set their old revision grade total to deleted.
        if ($old_total_object = ilGradebookGradeTotalConfig::where([
            'revision_id' => $old_revision_id,
            'usr_id' => $usr_id,
            'gradebook_id' => $gradebook->getId()
        ])->first()) {
            //if it was found, delete it.
            $old_total_object->setDeleted(date("Y-m-d H:i:s"));
            $old_total_object->update();
        }

        foreach ($this->getUsersCourseLayout($usr_id, $old_revision) as $obj) {
            //foreach object in the old gradebook if it was gradeable assume
            if ($obj['is_gradeable']) {
                $new_revision_object = $new_revision->getGradebookObject($obj['obj_id']);
                if (!empty($new_revision_object)) {
                    $old_grade = $this->determineGrade($obj, $usr_id);
                    $this->saveGrade($new_revision_object, $usr_id, $old_grade['actual'], $old_grade['status']);
                }
            }
        }

        //then we'll update their total mark.
        $this->refreshGrades($usr_id, $new_revision);
    }

    /**
     * Get A Users Layout of Course Items based on their user id.
     * This will mostly be the same but it will differ based on
     * groups that they are in.
     * @param $usr_id
     * @param ilGradebookRevisionConfig $latest_revision
     * @return array
     */
    public function getUsersCourseLayout($usr_id, ilGradebookRevisionConfig $latest_revision)
    {
        $revision_objects = $latest_revision->getWeightedGradebookObjects();
        foreach ($revision_objects as $k => &$revision_object) {
            $revision_objects[$k]['is_gradeable'] = TRUE;
            $object_instance = ilObjectFactory::getInstanceByObjId($revision_object['obj_id']);
            if ($object_instance->getType() == 'grp') {
                $group_participants_instance = ilParticipants::getInstanceByObjId($revision_object['obj_id']);
                $child_assets = $latest_revision->getAllChildObjects($revision_object['id']);
                if (!$group_participants_instance->isMember($usr_id)) {
                    //if they're not a member of that group unset that group from their course layout.
                    unset($revision_objects[$k]);
                    //then get all the items under it and unset those.
                    foreach ($child_assets as $key => $value) {
                        unset($revision_objects[$key]);
                    }
                } else {
                    //if they are a member of the group check if there is any weighted objects under it.
                    //if there is this group isn't gradeable anymore.
                    if (count($child_assets) > 0) {
                        $revision_objects[$k]['is_gradeable'] = FALSE;
                    }
                }
            }
            if (in_array($object_instance->getType(), ['cat', 'fold'])) {
                $child_assets = $latest_revision->getAllChildObjects($revision_object['id']);
                if (count($child_assets) > 0) {
                    $revision_objects[$k]['is_gradeable'] = FALSE;
                }
            }
        }
        $revision_objects = $this->sortByDepthAndOrder($revision_objects);
        return $revision_objects;
    }

    /**
     * @return mixed
     */
    private function sortByDepthAndOrder($revision_objects)
    {
        $tree = $this->buildSortedTree($revision_objects);
        $tree = $this->flattenTree($tree);
        return $tree;
    }

    /**
     * @param $nodes
     * @return array
     */
    private function flattenTree($nodes)
    {
        $result = [];
        foreach ($nodes as $node) {
            array_push($result, $node);
            if (array_key_exists('children', $node)) {
                $result = array_merge($result, $this->flattenTree($node['children']));
            }
        }
        return $result;
    }

    /**
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    private function buildSortedTree(array &$elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as &$element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildSortedTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['id']] = $element;
                unset($element);
            }
        }
        usort($branch, function ($a, $b) {
            return $a['placement_order'] - $b['placement_order'];
        });
        return $branch;
    }


    /**
     * Given the users grades. Save everything.
     *
     * @param [type] $usr_id
     * @param array $grades_array
     * @return void
     */
    public function saveUsersGrades($usr_id, array $grades_array)
    {
        $latest_revision = $this->getUsersLatestRevision($usr_id);
        //first we'll grade all the items that were sent in.
        foreach ($grades_array as $grade_array) {
            $gradebook_object = $latest_revision->getGradebookObject($grade_array['obj_id']);
            $this->saveGrade($gradebook_object, $usr_id, $grade_array['actual'], $grade_array['status']);
        }
        //then we'll update their total mark.
        $this->refreshGrades($usr_id, $latest_revision);
        return true;
    }


    /**
     * update the status of the users revision.
     *
     * @param [type] $usr_id
     * @return void
     */
    public function updateStatus($usr_id)
    {
        $latest_revision = $this->getUsersLatestRevision($usr_id);


        //then we'll update their total mark.
        $this->refreshGrades($usr_id, $latest_revision);
        return true;
    }

    /**
     * Given the usr_id and revision, refresha  users grades in total object
     * without requiring a status.
     *
     * @param [type] $usr_id
     * @param [type] $latest_revision
     * @return void
     */
    private function refreshGrades($usr_id, $latest_revision)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradeTotalConfig.php');

        $gradebook_id = $this->getGradebookId();
        $objects = $latest_revision->getObjectsAtDepth(0);
        $grades_array = $this->getGradesForObjects($objects, $usr_id);
        $overall_grade = array_sum($grades_array);
        $progress = $this->getOverallProgress($usr_id, $latest_revision);

        //refresh the users nested grade objects.
        $this->updateUsersNestedGradeObjects($usr_id, $latest_revision);

        $structured_gradebook = $latest_revision->getUsersStructuredGradebook($usr_id);
        $adjusted_grade = array_sum($this->getOverallAdjustedGrade($usr_id, $structured_gradebook));

        $total_object = ilGradebookGradeTotalConfig::firstOrNew($latest_revision->getRevisionId(), $usr_id, $gradebook_id);
        //have to undelete if it decided to reuse an old deleted one.
        $total_object->setDeleted(NULL);
        $total_object->setAdjustedGrade($adjusted_grade);
        $total_object->setOverallGrade($overall_grade);
        $total_object->setProgress($progress);

        $overall_status = $total_object->getStatus();

        if ((int) $progress === 100) {
            //know they've completed the gradebook, if their mark is less than the passing grade for their revision, mark em failed. otherwise passed.
            $overall_status = (int) $overall_grade < $latest_revision->getPassingGrade() ? 3 : 2;
        }

        //if the overallprogress is greater than 0, assume that they're at least in progress.
        if ($progress > 0 && (int) $total_object->getStatus() === 0) {
            $overall_status = 1;
        }

        if ($total_object->getStatus() !== (int) $overall_status) {
            //if the status has changed. update. it.
            $total_object->setStatus((int) $overall_status);
            ilLPStatus::writeStatus($this->obj_id, $usr_id, (int) $overall_status);
        }

        $total_object->setLastUpdate(date("Y-m-d H:i:s"));
        if ($total_object->getRecentlyCreated()) {
            $total_object->save();
        } else {
            $total_object->update();
        }
        return true;
    }



    /**
     * @param $usr_id
     * @param $objects
     * @return array
     */
    private function getOverallAdjustedGrade($usr_id, $objects)
    {
        $grades_arr = [];
        foreach ($objects as $obj) {
            if (in_array($obj['object_data_type'], ['grp', 'fold', 'cat'])) {
                if (array_key_exists('children', $obj)) {
                    if (!empty($obj['children'])) {
                        $sum_of_adjusted = array_sum($this->getOverallAdjustedGrade($usr_id, $obj['children']));
                        $grade = $sum_of_adjusted * ($obj['object_weight'] * 0.01);
                        array_push($grades_arr, $grade);
                    } else {
                        array_push($grades_arr, $this->determineAdjustedGrade($obj, $usr_id));
                    }
                }
            } else {
                array_push($grades_arr, $this->determineAdjustedGrade($obj, $usr_id));
            }
        }
        return $grades_arr;
    }

    /**
     * @param $revision_object
     * @param $usr_id
     * @return array
     */
    private function determineAdjustedGrade($revision_object, $usr_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradesConfig.php');
        //if the learning progress is done in the object itself grab the marks from there.
        if ($revision_object['lp_type'] == 0) {
            $mark = ilLPMarks::_lookupMark($usr_id, $revision_object['obj_id']);
            $status = ilLPStatus::_lookupStatus($revision_object['obj_id'], $usr_id, false);
            //if mark is 0 and status isn't completed, or mark is null or empty assume 100 (for adjusted);
            $mark = (($mark == 0 && !in_array((int)$status, [2, 3])) || is_null($mark)) ? 100 : $mark;
            $adjusted = (int)$mark * ((int) $revision_object['object_weight'] * 0.01);
        } else {
            $gradebook_grade = array_shift(ilGradebookGradesConfig::where([
                'usr_id' => $usr_id,
                'revision_id' => $revision_object['revision_id'],
                'gradebook_object_id' => $revision_object['id'],
                'deleted' => null
            ])->getArray());

            //if 0 and not completed, or null. Assume 100% for now.    
            $adjusted = (($gradebook_grade['actual_grade'] == 0 && !in_array((int)$gradebook_grade['status'], [2, 3])) ||
                is_null($gradebook_grade['actual_grade']))
                ? 100 * ($revision_object['object_weight'] * 0.01) : $gradebook_grade['adjusted_grade'];
        }

        return $adjusted;
    }


    /**
     * @param $usr_id
     * @param $latest_revision
     * @return string
     */
    private function getOverallProgress($usr_id, $latest_revision)
    {
        $users_objects = $this->getUsersCourseLayout($usr_id, $latest_revision);
        $total_count = count($users_objects);
        $passed_count = 0;
        foreach ($users_objects as $users_object) {
            if (!in_array($this->getStatus($users_object, $usr_id), [0, 1])) {
                $passed_count++;
            }
	    }
        return $total_count ? number_format(($passed_count / $total_count) * 100, 2) : 0;
    }

    /**
     * @param $usr_id
     * @param $latest_revision
     * @return bool
     */
    private function updateUsersNestedGradeObjects($usr_id, $latest_revision)
    {
        //grab all the users groups that have objects determining their grade.
        $users_groups = $latest_revision->getUsersGroupsWithCalculatedGrading($usr_id);


        foreach ($users_groups as $group) {
            //get all the child object under this group.
            $children = $latest_revision->getAllChildObjects($group['id']);



            $child_grades = $this->getGradesForObjects($children, $usr_id);
            $this->saveGrade($group, $usr_id, array_sum($child_grades), NULL);
        }
        return true;
    }

    /**
     * Gets the grades for all objects passed in,
     * useful if you have a list of groups and unsure if they're a member or not.
     *
     *
     * @param $grade_objects
     * @param $usr_id
     * @return array
     */
    private function getGradesForObjects($grade_objects, $usr_id)
    {
        $object_grades = [];
        foreach ($grade_objects as $object) {
            $grade = $this->determineGrade($object, $usr_id);
            //now if the child is a group. check if they're a member.
            if ($object['object_data_type'] == 'grp') {
                if ($this->isMember($object['obj_id'], $usr_id)) {
                    array_push($object_grades, $grade['adjusted']);
                }
            } else {
                array_push($object_grades, $grade['adjusted']);
            }
        }
        return $object_grades;
    }

    /**
     * @param $gradebook_object
     * @param $usr_id
     * @param $actual_grade
     * @param $status
     * @return bool
     */
    private function saveGrade($gradebook_object, $usr_id, $actual_grade, $status)
    {
        global $ilUser;

        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradesConfig.php');
        $gradebook_id = $this->getGradebookId();

        $gradebook_grade = ilGradebookGradesConfig::firstOrNew(
            $gradebook_object['revision_id'],
            $gradebook_object['id'],
            $usr_id
        );

        $adjusted_grade = (int)$actual_grade * ($gradebook_object['object_weight'] * 0.01);

        //determine whether we should update or not.
        $update = false;

        if ($gradebook_grade) {
            //if any of the parameters changed we should update it, if status was pushed in as null, we know it came from a group update, so don't update it.
            if (
                (int) $gradebook_grade->getActualGrade() !== (int) $actual_grade
                || (int)$gradebook_grade->getAdjustedGrade() !== (int) $adjusted_grade
                || ((int)$gradebook_grade->getStatus() !== (int)$status && !is_null($status))
            ) {
                $update = true;
            }
        }
  
    

        $gradebook_grade->setGradebookId($gradebook_id);
        if (!empty($actual_grade) || (int)$actual_grade === 0) {
            $gradebook_grade->setActualGrade($actual_grade);
        }
        $gradebook_grade->setAdjustedGrade($adjusted_grade);

        if (!is_null($status)) {
            $gradebook_grade->setStatus($status);
        }
       
        if ($gradebook_grade->getRecentlyCreated()) {
            if($actual_grade !== '') {
                $gradebook_grade->setLastUpdate(date("Y-m-d H:i:s"));
                $gradebook_grade->save();
            }
        } else {
            if ($update) {
                if ($revision_object['lp_type'] !== 0) {
                    $gradebook_grade->setLastUpdate(date("Y-m-d H:i:s"));
                    $gradebook_grade->setOwner($ilUser->getId());
                }
                $gradebook_grade->update();
            }
        }
        return true;
    }

    /**
     * @param $revision_object
     * @param $usr_id
     * @return mixed
     */
    private function getStatus($revision_object, $usr_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradesConfig.php');

        if ($revision_object['lp_type'] == 0) {
            $status = ilLPStatus::_lookupStatus($revision_object['obj_id'], $usr_id, false);
        } else {
            $gradebook_grade = array_shift(ilGradebookGradesConfig::where([
                'usr_id' => $usr_id,
                'revision_id' => $revision_object['revision_id'],
                'gradebook_object_id' => $revision_object['id'],
                'deleted' => null
            ])->getArray());

            $status = $gradebook_grade['status'];
        }
        return $status;
    }

    /**
     * Returns mapped out data for the student view.
     * @param $usr_id
     * @return array
     */
    public function getUserGradeData($usr_id)
    {
        $gradebook_objects = $this->getUsersGrades($usr_id);
        $overall = $this->getOverallUserGrades($usr_id);
        $revision = $this->getUsersLatestRevision($usr_id);
        $data = [
            'passing_grade' => $revision->getPassingGrade(),
            'overall' => $overall,
            'grade_objects' => $gradebook_objects
        ];
        return $data;
    }

    /**
     *
     */
    public function getCourseParticipantsData()
    {
        $user_grades = $this->getOverallUserGrades();
        $average_adjusted = 0;
        $average_overall = 0;
        $average_progress = 0;
        $user_count = 0;
        foreach ($user_grades as $user_grade) {
            if ($user_grade['status'] !== 0) {
                $average_progress += $user_grade['progress'];
                $user_count++;
            }
        }
        return [
            'average_progress' => $average_progress == 0 ? 0 : (int)$average_progress / (int)$user_count,
            'user_grades' => $user_grades
        ];
    }

    /**
     * @param null $usr_id
     * @return array
     */
    public function getOverallUserGrades($usr_id = NULL)
    {
        $gradebook_id = $this->getGradebookId();
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradeTotalConfig.php');

        if (is_null($usr_id)) {
            $members = $this->getCourseMembers($usr_id);
        } else {
            $user = new ilObjUser($usr_id);
            $members[] = [
                'usr_id' => $usr_id,
                'login' => $user->getLogin(),
                'full_name' => $user->getFullname()
            ];
        }
        $grades_arr = [];
        foreach ($members as $member) {
            $revision = $this->getUsersLatestRevision($member['usr_id']);
            $this->refreshGrades($member['usr_id'], $revision);
            $grades = ilGradebookGradeTotalConfig::firstOrNew($revision->getRevisionId(), $member['usr_id'], $gradebook_id);

            $grades_arr[] = [
                'student_name' => $member['full_name'],
                'login' => $member['login'],
                'revision' => $revision->getRevisionId(),
                'overall_grade' => $grades->getOverallGrade(),
                'adjusted_grade' => $grades->getAdjustedGrade(),
                'progress' => $grades->getProgress(),
                'status' => $grades->getStatus(),
                'img' => ilLearningProgressBaseGUI::_getImagePathForStatus($grades->getStatus()),
                'img_Alt' => ilLearningProgressBaseGUI::_getStatusText($grades->getStatus())
            ];
        }

        return $grades_arr;
    }


    /**
     * @param $revision_object
     * @param $usr_id
     * @return array
     */
    private function determineGrade($revision_object, $usr_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookGradesConfig.php');

        $data = [];
    
        //if the learning progress is done in the object itself grab the marks from there.
        if ($revision_object['lp_type'] == 0) {
            $marks = new ilLPMarks($revision_object['obj_id'], $usr_id);
            switch (ilObject::_lookupType($revision_object['obj_id'])) {
                case 'tst':
                    $mark = $marks->getMark() ? $marks->getMark() : ilLPStatus::_lookupPercentage($revision_object['obj_id'], $usr_id);
                    break;
                case 'fold':
                    $mark = $marks->getMark();
                    break;
                default:
                    $mark = $marks->getMark();
            }

            $status_changed = $marks->getStatusChanged();
            $data = [
                'status' => ilLPStatus::_lookupStatus($revision_object['obj_id'], $usr_id, false),
                'actual' => (int)$mark,
                'adjusted' => empty($mark) ? '' : (int)$mark * ($revision_object['object_weight'] * 0.01),
                'graded_on' => empty($mark) ? '' : $status_changed,
                'graded_by' => empty($mark) ? '' : 'Graded by Instructor/Tutor'
            ];
        } else {
            $gradebook_grade = array_shift(ilGradebookGradesConfig::where([
                'usr_id' => $usr_id,
                'revision_id' => $revision_object['revision_id'],
                'gradebook_object_id' => $revision_object['id'],
                'deleted' => null
            ])
                ->getArray());

            $username = ilObjUser::_lookupName($gradebook_grade['owner']);
            $data = [
                'status' => $gradebook_grade['status'],
                'actual' => $gradebook_grade['actual_grade'],
                'adjusted' => $gradebook_grade['adjusted_grade'],
                'graded_on' => $gradebook_grade['last_update'],
                'graded_by' => $username['firstname'] . ' ' . $username['lastname']
            ];
        }
        return $data;
    }

    /**
     * @param $revision_object
     * @param $usr_id
     * @return array
     */
    private function mapGradeData($revision_object, $usr_id)
    {
        $object_instance = ilObjectFactory::getInstanceByObjId($revision_object['obj_id']);
        $grades = $this->determineGrade($revision_object, $usr_id);

        $arr = [
            'obj_id' => $revision_object['obj_id'],
            'lp_type' => $revision_object['lp_type'],
            'placement_depth' => $revision_object['placement_depth'],
            'weight' => $revision_object['object_weight'],
            'type' => $object_instance->getType(),
            'type_Alt' => $this->lng->txt($object_instance->getType()),
            'title' => $object_instance->getTitle(),
            'url' => $this->getLPUrlForObjId($revision_object['obj_id']),
            'actual' => $grades['actual'],
            'adjusted' => $grades['adjusted'],
            'status' => $grades['status'],
            'is_gradeable' => $revision_object['is_gradeable'],
            'graded_on' => $grades['graded_on'],
            'graded_by' => $grades['graded_by'],
            'img' => ilLearningProgressBaseGUI::_getImagePathForStatus($grades['status']),
            'img_Alt' => ilLearningProgressBaseGUI::_getStatusText($grades['status'])
        ];

        return $arr;
    }

    /**
     * @param $usr_id
     * @param ilGradebookRevisionConfig $revision
     * @param int $parent_id
     * @return bool
     * @throws Exception
     */
    private function userGroupCheck($usr_id, ilGradebookRevisionConfig $revision, $parent_id = 0)
    {
        $groups = $revision->getGroupsForParentId($parent_id);
        $user = new ilObjUser($usr_id);
        //sort into subarrays based on colour id.
        $colour_groups_arr = [];
        foreach ($groups as $key => $group) {
            $colour_groups_arr[$group['object_colour']][$key] = $group;
        }
        //if theres groups at this depth go through and check the colours.
        if (!empty($colour_groups_arr)) {
            foreach ($colour_groups_arr as $ck => $colour_group) {
                $groups_where_participant = [];
                $groups_where_non_participant = [];
                foreach ($colour_group as $k => $group) {

                    $group_participants_instance = ilParticipants::getInstanceByObjId($group['obj_id']);
                    if ($group_participants_instance->isMember($usr_id)) {
                        array_push($groups_where_participant, $group);
                        $this->userGroupCheck($usr_id, $revision, $group['id']);
                    } else {
                        array_push($groups_where_non_participant, $group);
                    }
                }

                if (count($groups_where_participant) == 0) {
                    foreach ($groups_where_non_participant as $g) {
                        $obj = ilObjectFactory::getInstanceByObjId($g['obj_id']);
                        $txt = '<a target="_blank" href="' . $this->getLPUrlForObjId($g['obj_id']) . '">'
                            . $obj->getTitle() . '</a>';
                        $group_arr[] = $txt;
                    }
                    throw new Exception(
                        $user->getFullname() . ' has a group member mismatch.
                        They do not belong to any groups within a colour group ( ' . implode(", ", $group_arr) . ' ).
                         Please Fix this before proceeding.'
                    );
                } else if (count($groups_where_participant) > 1) {
                    $group_arr = [];
                    foreach ($groups_where_participant as $g) {
                        $obj = ilObjectFactory::getInstanceByObjId($g['obj_id']);
                        $txt = '<a target="_blank" href="' . $this->getLPUrlForObjId($g['obj_id']) . '">' . $obj->getTitle() . '</a>';
                        $group_arr[] = $txt;
                    }
                    throw new Exception(
                        $user->getFullname() . ' has a group member mismatch.
                        They belong to multiple groups within a colour group ( ' . implode(", ", $group_arr) . ' ).
                         Please Fix this before proceeding.'
                    );
                }
            }
        }
        return true;
    }
}
