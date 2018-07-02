<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Naive fetching of the course template infos using ILIAS.
 */
class ilCourseTemplateDB implements CourseTemplateDB {
	use CourseAccessExtension;

	const REPOSITORY_REF_ID = 1;

	/**
	 * @var	\ilTree
	 */
	protected $tree;

	/**
	 * @var \ilObjectDefinition
	 */
	protected $obj_definition;

	public function __construct(\ilTree $tree, \ilObjectDefinition $obj_definition) {
		$this->tree = $tree;
		$this->obj_definition = $obj_definition;
	}

	/**
	 * @inheritdocs
	 */
	public function getCreatableCourseTemplates($user_id) {
		assert('is_int($user_id)');

		$node = $this->tree->getNodeData(self::REPOSITORY_REF_ID);

		$copy_setting_nodes = $this->tree->getSubTree($node, false, ["xcps"]);
		$crs_template_info = [];

		foreach ($copy_setting_nodes as $cs_node) {
			$path = array_reverse($this->tree->getPathFull($cs_node));
			while($p = array_shift($path)) {
				if ($p["type"] == "crs") {
					if (!self::_mayUserCreateCourseFromTemplate($user_id, (int)$p["child"])) {
						break;
					}

					$type = $this->getCourseType($p["child"]);
					// If there is no type defined the template is misconfigured and should not be displayed
					if(is_null($type)) {
						continue;
					}
					if (!array_key_exists($type, $crs_template_info)) {
						$crs_template_info[$type] = [];
					}
					$cat = $this->getCategoryTitle($path);
					if (!array_key_exists($cat, $crs_template_info[$type])) {
						$crs_template_info[$type][$cat] = [];
					}
					$crs_template_info[$type][$cat][] = new CourseTemplateInfo(
						$this->purgeTemplateInTitle($p["title"]),
						(int)$p["child"],
						$cat,
						$type
					);
				}
			}
		}

		return $crs_template_info;
	}

	/**
	 * @param	array	$path	strange format from ilTree
	 * @return	string|null
	 */
	protected function getCategoryTitle(array $path) {
		// there need to be a least three elements in the path to
		// get the appropriate category:
		// "repo > cat 1 > cat 2 > crs" should yield "cat 1"
		if (count($path) < 3) {
			return null;
		}
		array_shift($path);
		$node = array_shift($path);
		return $node["title"];
	}

	/**
	 * @param	string	$title
	 * @return	string
	 */
	protected function purgeTemplateInTitle($title) {
		$matches = [];
		if (preg_match("/^[^:]*:(.*)$/", $title, $matches)) {
			return $matches[1];
		}
		return $title;
	}

	/**
	 * Get course classifiaction type of the course template
	 *
	 * @param int 	$ref_id
	 *
	 * @return string
	 */
	protected function getCourseType($ref_id) {
		$xccl = $this->getFirstChildOfByType($ref_id, "xccl");

		if(is_null($xccl)) {
			return null;
		}

		$type_id = $xccl->getCourseClassification()->getType();
		if(is_null($type_id)) {
			return null;
		}

		$actions = $xccl->getActions();
		return array_shift($actions->getTypeName($type_id));
	}

	/**
	 * Get first child by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getFirstChildOfByType($ref_id, $search_type) {
		$childs = $this->tree->getChilds($ref_id);

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				return \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($this->obj_definition->isContainer($type)) {
				$ret = $this->getFirstChildOfByType($child["child"], $search_type);
				if(! is_null($ret)) {
					return $ret;
				}
			}
		}

		return null;
	}
}
