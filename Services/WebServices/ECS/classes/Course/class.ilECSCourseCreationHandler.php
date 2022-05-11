<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseCreationHandler
{
    private ilLogger $logger;
    private ilLanguage $lng;
    private ilTree $tree;


    private ilECSSetting $server;
    private ilECSNodeMappingSettings $mapping;
    private ?\ilECSCourseUrl $course_url = null;
    private bool $object_created = false;
    private array $courses_created = array();
    
    private int $mid;

    public function __construct(ilECSSetting $server, int $a_mid)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        
        $this->server = $server;
        $this->mid = $a_mid;
        $this->mapping = ilECSNodeMappingSettings::getInstanceByServerMid($this->server->getServerId(), $this->getMid());
        
        $this->course_url = new ilECSCourseUrl();
    }
    
    
    /**
     * Get server settings
     */
    public function getServer() : ilECSSetting
    {
        return $this->server;
    }
    
    /**
     * Get mapping settings
     * @return ilECSNodeMappingSettings
     */
    public function getMapping() : \ilECSNodeMappingSettings
    {
        return $this->mapping;
    }
    
    /**
     * Get course url
     * @return ilECSCourseUrl Description
     */
    public function getCourseUrl() : ?\ilECSCourseUrl
    {
        return $this->course_url;
    }
    
    /**
     * Check if an object (course / group) has been created.
     */
    public function isObjectCreated() : bool
    {
        return $this->object_created;
    }
    
    /**
     * Set object created status
     */
    public function setObjectCreated(bool $a_status) : void
    {
        $this->object_created = $a_status;
    }
    
    /**
     * get created courses
     */
    protected function getCreatedCourses() : array
    {
        return $this->courses_created;
    }
    
    /**
     * Get mid of course event
     */
    public function getMid() : int
    {
        return $this->mid;
    }
    
    /**
     * Handle sync request
     * @param int $a_content_id ecs content id
     */
    public function handle(int $a_content_id, $course) : bool
    {
        // prepare course url
        // if any object (course group) will be created, a list of all course urls
        // will be sent to ecs.
        $this->setObjectCreated(false);
        $this->course_url->setECSId($a_content_id);
        
        
        if ($this->getMapping()->isAttributeMappingEnabled()) {
            $this->logger->debug('Handling advanced attribute mapping');
            return $this->doAttributeMapping($a_content_id, $course);
        }
        
        if ($this->getMapping()->isAllInOneCategoryEnabled()) {
            $this->logger->debug('Handling course all in one category setting');
            $this->doSync($a_content_id, $course, ilObject::_lookupObjId($this->getMapping()->getAllInOneCategory()));
            return true;
        }

        $parent_obj_id = $this->syncParentContainer($a_content_id, $course);
        if ($parent_obj_id) {
            $this->logger->info('Using already mapped category: ' . ilObject::_lookupTitle($parent_obj_id));
            $this->doSync($a_content_id, $course, $parent_obj_id);
            return true;
        }
        $this->logger->info('Using course default category');
        $this->doSync($a_content_id, $course, ilObject::_lookupObjId($this->getMapping()->getDefaultCourseCategory()));
        return true;
    }
    
    /**
     * Sync attribute mapping
     */
    protected function doAttributeMapping($a_content_id, $course) : bool
    {
        // Check if course is already created
        $course_id = $course->lectureID;
        $obj_id = $this->getImportId($course_id);
        
        if ($obj_id) {
            // do update
            $this->logger->debug('Performing update of already imported course.');
            
            $refs = ilObject::_getAllReferences($obj_id);
            $ref = end($refs);
            
            $this->doSync(
                $a_content_id,
                $course,
                ilObject::_lookupObjId($this->tree->getParentId($ref))
            );
            return true;
        }
        
        // Get all rules
        $matching_rules = [];
        foreach (ilECSCourseMappingRule::getRuleRefIds($this->getServer()->getServerId(), $this->getMid()) as $ref_id) {
            $matching_index = ilECSCourseMappingRule::isMatching(
                $course,
                $this->getServer()->getServerId(),
                $this->getMid(),
                $ref_id
            );
            if (strcmp($matching_index, '0') !== 0) {
                $matching_rules[$matching_index] = $ref_id;
            }
        }
        ksort($matching_rules);
        
        $this->logger->dump($matching_rules);
        
        if (!count($matching_rules)) {
            // Put course in default category
            $this->logger->debug('No matching attribute mapping rule found.');
            $this->logger->info('Using course default category');
            $this->doSync($a_content_id, $course, ilObject::_lookupObjId($this->getMapping()->getDefaultCourseCategory()));
            return true;
        }
        
        $this->logger->debug('Matching rules:');
        $this->logger->dump($matching_rules, ilLogLevel::DEBUG);
        
        $all_parent_refs = [];
        foreach ($matching_rules as $matching_rule) {
            $this->logger->debug('Handling matching rule: ' . $matching_rule);
            $parent_refs = ilECSCourseMappingRule::doMappings($course, $this->getServer()->getServerId(), $this->getMid(), $matching_rule);
            // map according mapping rules
            $this->logger->debug('Adding parent references: ' . print_r($parent_refs, true));
            if (count($parent_refs)) {
                $all_parent_refs = array_unique(array_merge($all_parent_refs, $parent_refs));
            }
        }
        
        // parent refs are an array of created categories
        // the first ref should contain the main course or parallel courses.
        // all other refs wil contain course references.
        $first = true;
        foreach ($all_parent_refs as $category_ref) {
            if ($first) {
                $this->logger->debug('Creating new course instance in: ' . $category_ref);
                $this->doSync($a_content_id, $course, ilObject::_lookupObjId($category_ref));
                $first = false;
                continue;
            }
            $this->logger->debug('Creating new course reference instance in: ' . $category_ref);
            $this->createCourseReferenceObjects($category_ref);
        }
        return true;
    }
    
    /**
     * Create course reference objects
     */
    protected function createCourseReferenceObjects($a_parent_ref_id) : void
    {
        $this->logger->debug('Created new course reference in : ' . ilObject::_lookupTitle(ilObject::_lookupObjId($a_parent_ref_id)));
        foreach ($this->getCreatedCourses() as $ref_id) {
            $crsr = new ilObjCourseReference();
            $crsr->setOwner(SYSTEM_USER_ID);
            $crsr->setTargetRefId($ref_id);
            $crsr->setTargetId(ilObject::_lookupObjId($ref_id));
            $crsr->create();
            $crsr->update();
            $crsr->createReference();
            $crsr->putInTree($a_parent_ref_id);
            $crsr->setPermissions($a_parent_ref_id);
            
            $this->logger->debug('Created new course reference for : ' . ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)));
        }
    }
    
    /**
     * Sync parent container
     */
    protected function syncParentContainer($a_content_id, $course) : int
    {
        if (!is_array($course->allocations)) {
            $this->logger->debug('No allocation in course defined.');
            return 0;
        }
        if (!$course->allocations[0]->parentID) {
            $this->logger->debug('No allocation parent in course defined.');
            return 0;
        }
        $parent_id = $course->allocations[0]->parentID;
        
        $parent_tid = ilECSCmsData::lookupFirstTreeOfNode($this->getServer()->getServerId(), $this->getMid(), $parent_id);
        return $this->syncNodetoTop($parent_tid, $parent_id);
    }
    
    /**
     * Sync node to top
     * @return int obj_id of container
     */
    protected function syncNodeToTop($tree_id, $cms_id) : int
    {
        $obj_id = $this->getImportId($cms_id);
        if ($obj_id) {
            // node already imported
            return $obj_id;
        }

        $tobj_id = ilECSCmsData::lookupObjId(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $tree_id,
            $cms_id
        );
        
        // node is not imported
        $this->logger->debug('ecs node with id ' . $cms_id . ' is not imported for mid ' . $this->getMid() . ' tree_id ' . $tree_id);
        
        // check for mapping: if mapping is available create category
        $ass = new ilECSNodeMappingAssignment(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $tree_id,
            $tobj_id
        );
        
        if ($ass->isMapped()) {
            $this->logger->debug('node is mapped');
            return $this->syncCategory($tobj_id, $ass->getRefId());
        }
        
        // Start recursion to top
        $tree = new ilECSCmsTree($tree_id);
        $parent_tobj_id = $tree->getParentId($tobj_id);
        if ($parent_tobj_id) {
            $cms_ids = ilECSCmsData::lookupCmsIds(array($parent_tobj_id));
            $obj_id = $this->syncNodeToTop($tree_id, $cms_ids[0]);
        }
        
        if ($obj_id) {
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_id = end($refs);
            return $this->syncCategory($tobj_id, $ref_id);
        }
        return 0;
    }
    
    /**
     * Sync category
     */
    protected function syncCategory($tobj_id, $parent_ref_id) : int
    {
        $data = new ilECSCmsData($tobj_id);
        
        $cat = new ilObjCategory();
        $cat->setOwner(SYSTEM_USER_ID);
        $cat->setTitle($data->getTitle());
        $cat->create(); // true for upload
        $cat->createReference();
        $cat->putInTree($parent_ref_id);
        $cat->setPermissions($parent_ref_id);
        $cat->deleteTranslation($this->lng->getDefaultLanguage());
        $cat->addTranslation(
            $data->getTitle(),
            $cat->getLongDescription(),
            $this->lng->getDefaultLanguage(),
            $this->lng->getDefaultLanguage()
        );
            
        // set imported
        $import = new ilECSImport(
            $this->getServer()->getServerId(),
            $cat->getId()
        );
        $import->setMID($this->getMid());
        $import->setContentId($data->getCmsId());
        $import->setImported(true);
        $import->save();
        
        return $cat->getId();
    }

    /**
     * Handle all in one setting
     *
     * @return bool created course reference references TODO fix
     */
    protected function doSync($a_content_id, $course, $a_parent_obj_id) : bool
    {
        // Check if course is already created
        $course_id = $course->lectureID;
        $this->course_url->setCmsLectureId($course_id);
        
        $obj_id = $this->getImportId($course_id);
        
        $this->logger->debug('Found obj_id ' . $obj_id . ' for course_id ' . $course_id);
        
        // Handle parallel groups
        if ($obj_id) {
            // update multiple courses/groups according to parallel scenario
            $this->logger->debug('Group scenario ' . $course->groupScenario);
            switch ((int) $course->groupScenario) {
                case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
                    $this->logger->debug('Performing update for parallel groups in course.');
                    $this->updateParallelGroups($a_content_id, $course, $obj_id);
                    break;
                
                case ilECSMappingUtils::PARALLEL_ALL_COURSES:
                    $this->logger->debug('Performing update for parallel courses.');
                    $this->updateParallelCourses($a_content_id, $course, $a_parent_obj_id);
                    break;
                
                case ilECSMappingUtils::PARALLEL_ONE_COURSE:
                default:
                    // nothing to do
                    break;
                
            }
            
            // do update
            $this->updateCourseData($course, $obj_id);
        } else {
            switch ((int) $course->groupScenario) {
                case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
                    $this->logger->debug('Parallel scenario "groups in courses".');
                    $crs = $this->createCourseData($course);
                    $crs = $this->createCourseReference($crs, $a_parent_obj_id);
                    $this->setImported($course_id, $crs, $a_content_id);

                    // Create parallel groups under crs
                    $this->createParallelGroups($a_content_id, $course, $crs->getRefId());
                    break;
                
                case ilECSMappingUtils::PARALLEL_COURSES_FOR_LECTURERS:
                    $this->logger->debug('Parallel scenario "Courses foreach Lecturer".');
                    // Import empty to store the ecs ressource id (used for course member update).
                    $this->setImported($course_id, null, $a_content_id);
                    break;

                case ilECSMappingUtils::PARALLEL_ALL_COURSES:
                    $this->logger->debug('Parallel scenario "Many courses".');
                    $refs = ilObject::_getAllReferences($a_parent_obj_id);
                    $ref = end($refs);
                    // do not create master course for this scenario
                    //$crs = $this->createCourseData($course);
                    //$this->createCourseReference($crs, $a_parent_obj_id);
                    //$this->setImported($course_id, $crs, $a_content_id);
                    $this->createParallelCourses($a_content_id, $course, $ref);
                    break;
                    
                default:
                case ilECSMappingUtils::PARALLEL_ONE_COURSE:
                    $this->logger->debug('Parallel scenario "One Course".');
                    $crs = $this->createCourseData($course);
                    $this->createCourseReference($crs, $a_parent_obj_id);
                    $this->setImported($course_id, $crs, $a_content_id);
                    break;
                
                    
            }
        }
        // finally update course urls
        $this->handleCourseUrlUpdate();
        return true;
    }
    
    /**
     * Create parallel courses
     */
    protected function createParallelCourses(int $a_content_id, $course, $parent_ref) : bool
    {
        foreach ((array) $course->groups as $group) {
            $this->createParallelCourse($a_content_id, $course, $group, $parent_ref);
        }
        return true;
    }
    
    /**
     * Create parallel course
     */
    protected function createParallelCourse($a_content_id, $course, $group, $parent_ref) : bool
    {
        if ($this->getImportId($course->lectureID, $group->id)) {
            $this->logger->debug('Parallel course already created');
            return false;
        }
        
        $course_obj = new ilObjCourse();
        $course_obj->setOwner(SYSTEM_USER_ID);
        $title = $course->title;
        if ($group->title !== '') {
            $title .= ' (' . $group->title . ')';
        }
        $this->logger->debug('Creating new parallel course instance from ecs : ' . $title);
        $course_obj->setTitle($title);
        $course_obj->setSubscriptionMaxMembers((int) $group->maxParticipants);
        $course_obj->setOfflineStatus(true);
        $course_obj->create();
        
        $this->createCourseReference($course_obj, ilObject::_lookupObjId($parent_ref));
        $this->setImported($course->lectureID, $course_obj, $a_content_id, $group->id);
        $this->setObjectCreated(true);
        return true;
    }
    
    /**
     * Update parallel group data
     */
    protected function updateParallelCourses($a_content_id, $course, $parent_obj) : bool
    {
        $parent_refs = ilObject::_getAllReferences($parent_obj);
        $parent_ref = end($parent_refs);
        
        foreach ((array) $course->groups as $group) {
            $title = $course->title;
            if ($group->title !== '') {
                $title .= ' (' . $group->title . ')';
            }
            
            $obj_id = $this->getImportId($course->lectureID, $group->id);
            $this->logger->debug('Imported obj id is ' . $obj_id);
            if (!$obj_id) {
                $this->createParallelCourse($a_content_id, $course, $group, $parent_ref);
            } else {
                $course_obj = ilObjectFactory::getInstanceByObjId($obj_id, false);
                if ($course_obj instanceof ilObjCourse) {
                    $this->logger->debug('New title is ' . $title);
                    $course_obj->setTitle($title);
                    $course_obj->setSubscriptionMaxMembers($group->maxParticipants);
                    $course_obj->update();
                }
            }
            $this->addUrlEntry($this->getImportId($course->lectureID, $group->ID));
        }
        return true;
    }
    
    
    
    /**
     * This create parallel groups
     */
    protected function createParallelGroups($a_content_id, $course, $parent_ref) : bool
    {
        foreach ((array) $course->groups as $group) {
            $this->createParallelGroup($a_content_id, $course, $group, $parent_ref);
        }
        return true;
    }

    /**
     * Create parallel group
     */
    protected function createParallelGroup($a_content_id, $course, $group, $parent_ref) : void
    {
        $group_obj = new ilObjGroup();
        $group_obj->setOwner(SYSTEM_USER_ID);
        $title = $group->title !== '' ? $group->title : $course->title;
        $group_obj->setTitle($title);
        $group_obj->setMaxMembers((int) $group->maxParticipants);
        $group_obj->create();
        $group_obj->createReference();
        $group_obj->putInTree($parent_ref);
        $group_obj->setPermissions($parent_ref);
        $group_obj->updateGroupType(ilGroupConstants::GRP_TYPE_CLOSED);
        $this->setImported($course->lectureID, $group_obj, $a_content_id, $group->id);
        $this->setObjectCreated(true);
    }


    /**
     * Update parallel group data
     */
    protected function updateParallelGroups($a_content_id, $course, int $parent_obj) : void
    {
        $parent_refs = ilObject::_getAllReferences($parent_obj);
        $parent_ref = end($parent_refs);
        
        foreach ((array) $course->groups as $group) {
            $obj_id = $this->getImportId($course->lectureID, $group->id);
            $this->logger->debug('Imported obj id is ' . $obj_id);
            if (!$obj_id) {
                $this->createParallelGroup($a_content_id, $course, $group, $parent_ref);
            } else {
                $group_obj = ilObjectFactory::getInstanceByObjId($obj_id, false);
                if ($group_obj instanceof ilObjGroup) {
                    $title = $group->title !== '' ? $group->title : $course->title;
                    $this->logger->debug('New title is ' . $title);
                    $group_obj->setTitle($title);
                    $group_obj->setMaxMembers((int) $group->maxParticipants);
                    $group_obj->update();
                }
            }
            $this->addUrlEntry($this->getImportId($course->lectureID, $group->id));
        }
    }
    
    /**
     * Get import id of remote course
     * Return 0 if object isn't imported.
     * Searches for the (hopefully) unique content id of an imported object
     */
    protected function getImportId(int $a_content_id, string $a_sub_id = null) : int
    {
        return ilECSImportManager::getInstance()->lookupObjIdByContentId(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $a_content_id,
            $a_sub_id
        );
    }
    
    /**
     * Update course data
     */
    protected function updateCourseData($course, $obj_id) : bool
    {
        // do update
        $refs = ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        $crs_obj = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$crs_obj instanceof ilObject) {
            $this->logger->debug('Cannot instantiate course instance');
            return true;
        }
            
        // Update title
        $title = $course->title;
        $this->logger->debug('new title is : ' . $title);
            
        $crs_obj->setTitle($title);
        $crs_obj->update();
        return true;
    }
    
    /**
     * Create course data from json
     */
    protected function createCourseData($course) : \ilObjCourse
    {
        $course_obj = new ilObjCourse();
        $course_obj->setOwner(SYSTEM_USER_ID);
        $title = $course->title;
        $this->logger->debug('Creating new course instance from ecs : ' . $title);
        $course_obj->setTitle($title);
        $course_obj->setOfflineStatus(true);
        $course_obj->create();
        return $course_obj;
    }
    
    /**
     * Create course reference
     */
    protected function createCourseReference(ilObjCourse $crs, int $a_parent_obj_id) : \ilObjCourse
    {
        $ref_ids = ilObject::_getAllReferences($a_parent_obj_id);
        $ref_id = end($ref_ids);
        
        $crs->createReference();
        $crs->putInTree($ref_id);
        $crs->setPermissions($ref_id);
        
        $this->setObjectCreated(true);
        $this->addUrlEntry($crs->getId());
        
        $this->courses_created[] = $crs->getRefId();
        
        return $crs;
    }
    
    /**
     * Set new course object imported
     */
    protected function setImported(int $a_content_id, $object, $a_ecs_id = 0, $a_sub_id = null) : bool
    {
        $import = new ilECSImport(
            $this->getServer()->getServerId(),
            is_object($object) ? $object->getId() : 0
        );

        
        $import->setSubId($a_sub_id);
        $import->setMID($this->getMid());
        $import->setEContentId($a_ecs_id);
        $import->setContentId($a_content_id);
        $import->setImported(true);
        $import->save();
        return true;
    }
    
    /**
     * Add an url entry
     */
    protected function addUrlEntry(int $a_obj_id) : bool
    {
        $refs = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($refs);
        
        if (!$ref_id) {
            return false;
        }
        $lms_url = new ilECSCourseLmsUrl();
        $lms_url->setTitle(ilObject::_lookupTitle($a_obj_id));
        
        $lms_url->setUrl(ilLink::_getLink($ref_id));
        $this->course_url->addLmsCourseUrls($lms_url);
        return true;
    }
    
    /**
     * Update course url
     */
    protected function handleCourseUrlUpdate() : void
    {
        $this->logger->debug('Starting course url update');
        if ($this->isObjectCreated()) {
            $this->logger->debug('Sending new course group url');
            $this->course_url->send($this->getServer(), $this->getMid());
        } else {
            $this->logger->debug('No courses groups created. Aborting');
        }
    }
}
