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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilMobStakeholder extends AbstractResourceStakeholder
{
    protected ilAccessHandler $access;
    protected int $owner = 6;
    private int $current_user;
    protected ?ilDBInterface $database = null;

    public function __construct(int $owner = 6)
    {
        global $DIC;
        $this->current_user = (!is_array($DIC) && (int) ($DIC->isDependencyAvailable('user'))
            ? $DIC->user()->getId()
            : (defined('ANONYMOUS_USER_ID') ? ANONYMOUS_USER_ID : 6));
        $this->owner = $owner;
    }

    public function getId(): string
    {
        return 'mob';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->owner;
    }

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool
    {
        $mob_id = $this->resolveObjectId($identification);
        if ($mob_id === null) {
            return true;
        }

        foreach (\ilObjMediaObject::lookupUsages($mob_id) as $usage) {
            $oid = ilObjMediaObject::getParentObjectIdForUsage($usage, true);
            // for content snippets we must get their usages and check them
            switch ($usage["type"]) {
                case "auth:pg":
                    // Mobs on the Loginpage should always be delivered
                    return true;
                case "mep:pg":
                    $usages2 = ilMediaPoolPage::lookupUsages($usage["id"]);
                    foreach ($usages2 as $usage2) {
                        $oid2 = ilObjMediaObject::getParentObjectIdForUsage($usage2, true);
                        if ($this->checkAccessMobUsage($usage2, $oid2)) {
                            return true;
                        }
                    }
                    break;

                case "clip":
                    if ($usage["id"] == $this->current_user) {
                        return true;
                    }
                    break;

                default:
                    if ($oid !== null && $this->checkAccessMobUsage($usage, $oid)) {
                        return true;
                    }

                    if ($oid === null) {
                        /*
                        $this->logger->error(
                            sprintf(
                                "Could not determine parent obj_id for usage: %s",
                                json_encode($usage, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
                            )
                        );*/
                    }
                    break;
            }
        }

        return false;
    }

    protected function checkAccessMobUsage(
        array $usage,
        int $oid
    ): bool {
        $user_id = $this->current_user;

        switch ($usage['type']) {
            case 'lm:pg':
                if ($this->checkAccessObject($oid, 'lm')) {
                    return true;
                }
                break;

            case 'news':
                // media objects in news (media casts)
                if ($this->checkAccessObject($oid)) {
                    return true;
                } elseif (ilObjMediaCastAccess::_lookupPublicFiles($oid) && ilNewsItem::_lookupVisibility($usage["id"]) == NEWS_PUBLIC) {
                    return true;
                }
                break;

            case 'frm~:html':
            case 'exca~:html':
                // $oid = userid
                //				foreach ($this->check_users as $user_id) {
                if (ilObject::_lookupType($oid) === 'usr' && $oid == $user_id) {
                    return true;
                }
                //				}
                break;

            case 'frm~d:html':
                $draft_id = $usage['id'];

                $oDraft = ilForumPostDraft::newInstanceByDraftId($draft_id);
                if ($user_id == $oDraft->getPostAuthorId()) {
                    return true;
                }
                break;
            case 'frm~h:html':
                $history_id = $usage['id'];
                $oHistoryDraft = new ilForumDraftsHistory($history_id);
                $oDraft = ilForumPostDraft::newInstanceByDraftId($oHistoryDraft->getDraftId());
                if ($user_id == $oDraft->getPostAuthorId()) {
                    return true;
                }
                break;
            case 'qpl:pg':
            case 'qpl:html':
                // test questions
                if ($this->checkAccessTestQuestion($oid, $usage['id'])) {
                    return true;
                }
                break;

            case 'term:pg':
                // special check for glossary terms
                if ($this->checkAccessGlossaryTerm($oid, $usage['id'])) {
                    return true;
                }
                break;

            case 'sahs:pg':
                // check for scorm pages
                if ($this->checkAccessObject($oid, 'sahs')) {
                    return true;
                }
                break;

            case 'prtf:pg':
                // special check for portfolio pages
                if ($this->checkAccessPortfolioPage($oid, $usage['id'])) {
                    return true;
                }
                break;

            case 'blp:pg':
                // special check for blog pages
                if ($this->checkAccessBlogPage($oid)) {
                    return true;
                }
                break;

            case 'lobj:pg':
                // special check for learning objective pages
                if ($this->checkAccessLearningObjectivePage($oid, $usage['id'])) {
                    return true;
                }
                break;

            case 'impr:pg':
                return (ilImprint::isActive() || $this->checkAccessObject(SYSTEM_FOLDER_ID, 'adm'));

            case 'cstr:pg':
            default:
                // standard object check
                if ($this->checkAccessObject($oid)) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Check access rights for an object by its object id
     */
    protected function checkAccessObject(
        int $obj_id,
        string $obj_type = ''
    ): bool {
        global $DIC;

        $ilAccess = $DIC->access();
        $user_id = $this->current_user;

        if (!$obj_type) {
            $obj_type = ilObject::_lookupType($obj_id);
        }
        $ref_ids = ilObject::_getAllReferences($obj_id);

        foreach ($ref_ids as $ref_id) {
            //			foreach ($this->check_users as $user_id) {
            if ($ilAccess->checkAccessOfUser($user_id, "read", "view", $ref_id, $obj_type, $obj_id)) {
                return true;
            }
            //			}
        }

        return false;
    }


    /**
     * Check access rights for a test question
     * This checks also tests with random selection of questions
     * @param int $obj_id object id (question pool or test)
     */
    protected function checkAccessTestQuestion(
        int $obj_id,
        int $usage_id = 0
    ): bool {
        // give access if direct usage is readable
        if ($this->checkAccessObject($obj_id)) {
            return true;
        }

        $obj_type = ilObject::_lookupType($obj_id);
        if ($obj_type == 'qpl') {
            // give access if question pool is used by readable test
            // for random selection of questions
            $tests = ilObjTestAccess::_getRandomTestsForQuestionPool($obj_id);
            foreach ($tests as $test_id) {
                if ($this->checkAccessObject($test_id, 'tst')) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Check access rights for glossary terms
     * This checks also learning modules linking the term
     * @param int $obj_id       object id (glossary)
     * @param int $page_id      page id (definition)
     * @return bool            access given (true/false)
     */
    protected function checkAccessGlossaryTerm(
        int $obj_id,
        int $page_id
    ): bool {
        // give access if glossary is readable
        if ($this->checkAccessObject($obj_id)) {
            return true;
        }

        $term_id = $page_id;

        $sources = ilInternalLink::_getSourcesOfTarget('git', $term_id, 0);

        if ($sources) {
            foreach ($sources as $src) {
                switch ($src['type']) {
                    // Give access if term is linked by a learning module with read access.
                    // The term including media is shown by the learning module presentation!
                    case 'lm:pg':
                        $src_obj_id = ilLMObject::_lookupContObjID($src['id']);
                        if ($this->checkAccessObject($src_obj_id, 'lm')) {
                            return true;
                        }
                        break;

                        // Don't yet give access if the term is linked by another glossary
                        // The link will lead to the origin glossary which is already checked
                        /*
                        case 'gdf:pg':
                            $src_term_id = ilGlossaryDefinition::_lookupTermId($src['id']);
                            $src_obj_id = ilGlossaryTerm::_lookGlossaryID($src_term_id);
                            if ($this->checkAccessObject($src_obj_id, 'glo'))
                            {
                                return true;
                            }
                            break;
                        */
                }
            }
        }
        return false;
    }


    /**
     * Check access rights for portfolio pages
     * @param int $obj_id object id (glossary)
     * @param int $page_id page id (definition)
     */
    protected function checkAccessPortfolioPage(
        int $obj_id,
        int $page_id
    ): bool {
        $access_handler = new ilPortfolioAccessHandler();
        if ($access_handler->checkAccessOfUser($this->current_user, "read", "view", $obj_id, "prtf")) {
            return true;
        }

        return false;
    }


    /**
     * Check access rights for blog pages
     * @param int $obj_id blog page id
     */
    protected function checkAccessBlogPage(
        int $obj_id
    ): bool {
        $tree = new ilWorkspaceTree(0);
        $node_id = $tree->lookupNodeId($obj_id);
        if (!$node_id) {
            return $this->checkAccessObject($obj_id);
        } else {
            $access_handler = new ilWorkspaceAccessHandler($tree);
            if ($access_handler->checkAccessOfUser($tree, $this->current_user, "read", "view", $node_id, "blog")) {
                return true;
            }
        }

        return false;
    }


    protected function checkAccessLearningObjectivePage(
        int $obj_id,
        int $page_id
    ): bool {
        $crs_obj_id = ilCourseObjective::_lookupContainerIdByObjectiveId($page_id);

        return $this->checkAccessObject($crs_obj_id, 'crs');
    }

    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        // at this place we could handle de deletion of a resource. not needed for instruction files IMO.

        return true;
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        $this->initDB();
        $object_id = $this->resolveObjectId($identification);
        if ($object_id !== null) {
            $references = ilObject::_getAllReferences($object_id);
            $ref_id = array_shift($references);

            return ilLink::_getLink($ref_id);
        }
        return null;
    }

    private function resolveObjectId(ResourceIdentification $identification): ?int
    {
        $this->initDB();
        $r = $this->database->queryF(
            "SELECT id FROM mob_data WHERE rid = %s;",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);

        return (isset($d->id) ? (int) $d->id : null);
    }

    private function initDB(): void
    {
        global $DIC;
        if ($this->database === null) {
            $this->database = $DIC->database();
        }
    }
}
