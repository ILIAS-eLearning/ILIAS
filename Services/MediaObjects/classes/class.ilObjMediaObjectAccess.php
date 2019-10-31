<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');
require_once('./Services/MediaObjects/classes/class.ilObjMediaObject.php');

/**
 * Class ilObjMediaObjectAccess
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjMediaObjectAccess implements ilWACCheckingClass {
	/**
	 * @var ilObjectDataCache
	 */
	protected $obj_data_cache;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->obj_data_cache = $DIC["ilObjDataCache"];
		$this->user = $DIC->user();
		$this->access = $DIC->access();
	}


	/**
	 * @param ilWACPath $ilWACPath
	 *
	 * @return bool
	 */
	public function canBeDelivered(ilWACPath $ilWACPath) {
		preg_match("/.\\/data\\/.*\\/mm_([0-9]*)\\/.*/ui", $ilWACPath->getPath(), $matches);
		$obj_id = $matches[1];

		return $this->checkAccessMob($obj_id);
	}


	/**
	 * @param $obj_id
	 *
	 * @return bool
	 */
	protected function checkAccessMob($obj_id) {
		foreach (ilObjMediaObject::lookupUsages($obj_id) as $usage) {
			$oid = ilObjMediaObject::getParentObjectIdForUsage($usage, true);

			// for content snippets we must get their usages and check them
			switch ($usage["type"]) {
				case "auth:pg":
					// Mobs on the Loginpage should always be delivered
					return true;
				case "mep:pg":
					include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
					$usages2 = ilMediaPoolPage::lookupUsages($usage["id"]);
					foreach ($usages2 as $usage2) {
						$oid2 = ilObjMediaObject::getParentObjectIdForUsage($usage2, true);
						if ($this->checkAccessMobUsage($usage2, $oid2)) {
							return true;
						}
					}
					break;

				default:
					if ($this->checkAccessMobUsage($usage, $oid)) {
						return true;
					}
					break;
			}
		}

		return false;
	}


	/**
	 * @param $usage
	 * @param $oid
	 *
	 * @return bool
	 */
	protected function checkAccessMobUsage($usage, $oid) {
		/**
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		$ilObjDataCache = $this->obj_data_cache;
		$ilUser = $this->user;
		$user_id = $ilUser->getId();

		switch ($usage['type']) {
			case 'lm:pg':
				if ($this->checkAccessObject($oid, 'lm')) {
					return true;
				}
				break;

			case 'news':
				// media objects in news (media casts)
				include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
				include_once("./Services/News/classes/class.ilNewsItem.php");
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
				if ($ilObjDataCache->lookupType($oid) == 'usr' && $oid == $user_id) {
					return true;
				}
				//				}
				break;

			case 'frm~d:html':
				$draft_id = $usage['id'];
				
				include_once 'Modules/Forum/classes/class.ilForumPostDraft.php';
				$oDraft = ilForumPostDraft::newInstanceByDraftId($draft_id);
				if($user_id == $oDraft->getPostAuthorId())
				{
					return true;
				}
				break;
			case 'frm~h:html':
				$history_id = $usage['id'];
				include_once 'Modules/Forum/classes/class.ilForumDraftsHistory.php';
				include_once 'Modules/Forum/classes/class.ilForumPostDraft.php';
				
				$oHistoryDraft = new ilForumDraftsHistory($history_id);
				$oDraft = ilForumPostDraft::newInstanceByDraftId($oHistoryDraft->getDraftId());
				if($user_id == $oDraft->getPostAuthorId())
				{
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

			case 'gdf:pg':
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
				if ($this->checkAccessBlogPage($oid, $usage['id'])) {
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
				include_once 'Services/Imprint/classes/class.ilImprint.php';

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
	 *
	 * @param    int        object id
	 *
	 * @return   boolean     access given (true/false)
	 */
	protected function checkAccessObject($obj_id, $obj_type = '') {
		$ilAccess = $this->access;
		$ilUser = $this->user;
		$user_id = $ilUser->getId();

		if (! $obj_type) {
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
	 *
	 * @param    int         object id (question pool or test)
	 * @param    int         usage id (not yet used)
	 *
	 * @return   boolean     access given (true/false)
	 */
	protected function checkAccessTestQuestion($obj_id, $usage_id = 0) {
		$ilAccess = $this->access;

		// give access if direct usage is readable
		if ($this->checkAccessObject($obj_id)) {
			return true;
		}

		$obj_type = ilObject::_lookupType($obj_id);
		if ($obj_type == 'qpl') {
			// give access if question pool is used by readable test
			// for random selection of questions
			include_once('./Modules/Test/classes/class.ilObjTestAccess.php');
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
	 *
	 * @param    int         object id (glossary)
	 * @param    int         page id (definition)
	 *
	 * @return   boolean     access given (true/false)
	 */
	protected function checkAccessGlossaryTerm($obj_id, $page_id) {
		// give access if glossary is readable
		if ($this->checkAccessObject($obj_id)) {
			return true;
		}

		include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term_id = ilGlossaryDefinition::_lookupTermId($page_id);

		include_once('./Services/Link/classes/class.ilInternalLink.php');
		$sources = ilInternalLink::_getSourcesOfTarget('git', $term_id, 0);

		if ($sources) {
			foreach ($sources as $src) {
				switch ($src['type']) {
					// Give access if term is linked by a learning module with read access.
					// The term including media is shown by the learning module presentation!
					case 'lm:pg':
						include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
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
	}


	/**
	 * Check access rights for portfolio pages
	 *
	 * @param    int         object id (glossary)
	 * @param    int         page id (definition)
	 *
	 * @return   boolean     access given (true/false)
	 */
	protected function checkAccessPortfolioPage($obj_id, $page_id) {
		$ilUser = $this->user;
		include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler();
		if ($access_handler->checkAccessOfUser($ilUser->getId(), "read", "view", $obj_id, "prtf")) {
			return true;
		}

		return false;
	}


	/**
	 * Check access rights for blog pages
	 *
	 * @param    int         object id (glossary)
	 * @param    int         page id (definition)
	 *
	 * @return   boolean     access given (true/false)
	 */
	protected function checkAccessBlogPage($obj_id) {
		$ilUser = $this->user;
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree(0);
		$node_id = $tree->lookupNodeId($obj_id);
		if (! $node_id) {
			return $this->checkAccessObject($obj_id);
		} else {
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";

			$access_handler = new ilWorkspaceAccessHandler($tree);
			if ($access_handler->checkAccessOfUser($tree, $ilUser->getId(), "read", "view", $node_id, "blog")) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param $obj_id
	 * @param $page_id
	 *
	 * @return bool
	 */
	protected function checkAccessLearningObjectivePage($obj_id, $page_id) {
		include_once "Modules/Course/classes/class.ilCourseObjective.php";
		$crs_obj_id = ilCourseObjective::_lookupContainerIdByObjectiveId($page_id);

		return $this->checkAccessObject($crs_obj_id, 'crs');
	}
}

?>
