<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * Some common methods to help with the creation of links to the course creation.
 */
trait LinkHelper {
	/**
	 * @return	\ilCtrl
	 */
	abstract protected function getCtrl();

	/**
	* @return \ilLanguage
	*/
	abstract protected function getLng();

	/**
	 * @return \ilObjUser
	 */
	abstract protected function getUser();

	/**
	 * Send an info message to user gui
	 *
	 * @param string 	$message
	 *
	 * @return void
	 */
	abstract protected function sendInfo($message);


	/**
	 * @return	string
	 */
	protected function getCreateCourseCommand() {
		return "create_course_from_template";
	}

	/**
	 * @param	string[]	$parent_guis
	 * @param	string	$parent_cmd
	 * @param	int	$parent_ref_id
	 * @param	int|string $template_ref_id
	 * @return	string
	 */
	protected function getCreateCourseCommandLink($parent_guis, $parent_cmd, $parent_ref_id, $template_ref_id, $async = false) {
		assert('is_string($parent_cmd)');
		assert('is_int($parent_ref_id)');
		assert('is_int($template_ref_id) || is_string($template_ref_id)');
		$ctrl = $this->getCtrl();
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_guis", implode(".", $parent_guis));
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_cmd", $parent_cmd);
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_ref_id", $parent_ref_id);
		$ctrl->setParameterByClass("ilCourseCreationGUI", "ref_id", $template_ref_id);
		return $ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilCourseCreationGUI"], $this->getCreateCourseCommand(), "", $async);
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLngVar() {
		$lng = $this->getLng();
		$lng->loadLanguageModule("tms");
		return "create_course_from_template";
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLabel() {
		return $this->getLng()->txt($this->getCreateCourseCommandLngVar());
	}

	/**
	 * Get radiogroup input gui
	 *
	 * @param	array<string,CourseTemplateInfo[]>	$info
	 * @param	string	$select_name
	 * @return	\ilRadioGroupInputGUI
	 */
	protected function getRadioGroupInputGUIForCourseTemplates(array $info, $select_name, $group_name) {
		assert('is_string($select_name)');
		assert('is_string($group_name)');

		require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Table/interfaces/interface.ilTableFilterItem.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		$templates = new \ilRadioGroupInputGUI($this->txt('settings_venue_source'), $group_name);
		$templates->setRequired(true);
		$set_default_value = true;

		foreach ($info as $type => $template_by_cat) {
			$type_md5 = md5($type);
			$ro_fromcourse = new \ilRadioOption($type, $type_md5);
			$ro_fromcourse->addSubItem($this->getGroupableSelectInputGUIForCourseTemplates($template_by_cat, $select_name, $type_md5));

			$templates->addOption($ro_fromcourse);
			if($set_default_value) {
				$templates->setValue($type_md5);
				$set_default_value = false;
			}
		}

		return $templates;
	}

	/**
	 * @param	array<string,CourseTemplateInfo[]>	$info
	 * @param	string	$select_name
	 * @return	\ilGroupableSelectInputGUI
	 */
	protected function getGroupableSelectInputGUIForCourseTemplates(array $info, $select_name, $type) {
		assert('is_string($select_name)');

		ksort($info, SORT_NATURAL);
		foreach ($info as $k => $is) {
			$group = [];
			foreach($is as $i) {
				$group[$i->getRefId()] = $i->getTitle();
			}
			asort($group, SORT_NATURAL);
			$info[$k] = $group;
		}

		require_once("Services/Form/classes/class.ilGroupableSelectInputGUI.php");
		$select = new \ilGroupableSelectInputGUI("", $select_name."[".$type."]");
		$select->setGroups($info);
		return $select;
	}

	/**
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	CourseTemplateInfo[]	$info
	 * @param	string[]	$parent_guis
	 * @param	string		$parent_cmd
	 * @param	int			$parent_ref_id
	 * @return	ILIAS\UI\Component\Modal\Modal
	 */
	protected function getCourseTemplateSelectionModal(\ILIAS\UI\Factory $ui_factory, array $info, array $parent_guis, $parent_cmd, $parent_ref_id) {
		assert('is_string($parent_cmd)');
		assert('is_int($parent_ref_id)');
		$placeholder = "_REF_ID_";
		$link = $this->getCreateCourseCommandLink($parent_guis, $parent_cmd, $parent_ref_id, $placeholder, true);
		$select_name = "course_template_select";
		$group_name = "tpl_group";

		$next_button = $ui_factory->button()
			->standard(
				ucfirst($this->g_lng->txt("next")),
				""
			)
			->withAdditionalOnLoadCode(function($id) use ($link, $select_name, $placeholder, $group_name) {
				return "$('#$id').on('click', function(ev) {
					var link = '$link';
					tpl_type = '';
					tpl_types = $('input:radio[name=$group_name]');

					$(tpl_types).each(function() {
						if($(this).prop('checked')) {
							tpl_type = $(this).val();
						}
					});

					select_name = '$select_name' + '\\\[' + tpl_type + '\\\]';
					var ref_id = $('select[name^=' + select_name + ']').val();

					link = link.replace('$placeholder', ref_id);
					window.location.href = link;
					ev.preventDefault();
				});";
			});

		$select = $this->getRadioGroupInputGUIForCourseTemplates($info, $select_name, $group_name);

		return $ui_factory->modal()
			->roundtrip(
				$this->getLng()->txt("choose_course_template"),
				$ui_factory->legacy($select->render())
			)
			->withActionButtons([$next_button]);
	}

	/**
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	\ILIAS\UI\Factory $ui_renderer
	 * @param	\ilToolbarGUI $toolbar
	 * @param	CourseTemplateInfo[]	$info
	 * @param	string[]	$parent_guis
	 * @param	string		$parent_cmd
	 * @param	int			$parent_ref_id
	 * @return void
	 */
	protected function addCourseTemplateSelectionModalToToolbar(\ILIAS\UI\Factory $ui_factory, \ILIAS\UI\Renderer $ui_renderer, \ilToolbarGUI $toolbar, array $info, array $parent_guis, $parent_cmd, $parent_ref_id) {
		assert('is_int($parent_ref_id)');
		assert('is_string($parent_cmd)');

		$modal = $this->getCourseTemplateSelectionModal($ui_factory, $info, $parent_guis, $parent_cmd, $parent_ref_id);
		$button = $ui_factory->button()
			->primary(
				$this->getCreateCourseCommandLabel(),
				""
			)->withOnClick($modal->getShowSignal());

		$toolbar->addText($ui_renderer->render([$button, $modal]));
	}

	/**
	 * Shows request info if it is neccessary
	 *
	 * @param \ilCourseCreationPlugin | null	$xccr_plugin
	 * @param int 	$waiting_time
	 *
	 * @return bool
	 */
	protected function maybeShowRequestInfo(\ilCourseCreationPlugin $xccr_plugin = null, $waiting_time = 30000)
	{
		$requests = $this->getUsersDueRequests($this->getUser(), $xccr_plugin);
		if (count($requests) === 0) {
			return false;
		}

		// This assertion assumes that every user is only allowed to create one training at
		// a time. See e.g. TMS-1013.
		assert('count($requests) == 1');
		list($request) = $requests;
		$this->sendInfo($this->getMessage($request, $waiting_time));

		return true;
	}

	/**
	 * Get the message should be shown
	 *
	 * @param \ILIAS\TMS\CourseCreation\Request 	$request
	 * @param int 	$waiting_time
	 *
	 * @return string
	 */
	protected function getMessage(\ILIAS\TMS\CourseCreation\Request $request, $waiting_time) {
		require_once("Services/UICore/classes/class.ilTemplate.php");
		$this->getLng()->loadLanguageModule("tms");
		$tpl = new \ilTemplate("tpl.open_requests.html", true, true, "src/TMS");
		$tpl->setVariable("MESSAGE",
			sprintf(
				$this->getLng()->txt("course_creation_message"),
				$this->getTrainingTitleByRequest($request)
			)
		);
		$tpl->setVariable("TIMEOUT", $waiting_time);

		return $tpl->get();
	}

	/**
	 * TODO: This does not belong here as well is also baldy suited for the job.
	 *
	 * @param	Request $request
	 * @throws	\RuntimeException if title could not be determined
	 * @return	string
	 */
	protected function getTrainingTitleByRequest(\ILIAS\TMS\CourseCreation\Request $request)
	{
		$configs = $request->getConfigurations();
		foreach ($configs as $obj => $css) {
			foreach ($css as $cs) {
				foreach ($cs as $key => $value) {
					if ($key === "title") {
						return $value;
					}
				}
			}
		}
		throw new \RuntimeException("Expected every request to have a configuration for the course title.");
	}

	/**
	 * Get the open requests for single user
	 *
	 * @param \ilObjUser 	$user
	 * @param \ilCourseCreationPlugin 	$xccr_plugin
	 *
	 * @return ILIAS\TMS\CourseCreation/Request[]
	 */
	protected function getUsersDueRequests(\ilObjUser $user, \ilCourseCreationPlugin $xccr_plugin = null)
	{
		$cached_requests = $this->getCachedRequests((int)$user->getId());
		if ($cached_requests !== null) {
			return $cached_requests;
		}

		if (is_null($xccr_plugin)) {
			return [];
		}

		$actions = $xccr_plugin->getActions();
		$this->setCachedRequests((int)$user->getId(), $actions->getDueRequestsOf($user));

		return $this->getCachedRequests((int)$user->getId());
	}

	/**
	 * Get the requests is cached for the user
	 *
	 * @param int 	$usr_id
	 *
	 * @return ILIAS\TMS\CourseCreation\Request[]
	 */
	protected function getCachedRequests($usr_id) {
		return $this->cached_requests[$usr_id];
	}

	/**
	 * Get the requests is cached for the user
	 * @param int 	$usr_id
	 * @param ILIAS\TMS\CourseCreation\Request[] 	$requests
	 *
	 * @return void
	 */
	protected function setCachedRequests($usr_id, array $requests) {
		$this->cached_requests[$usr_id] = $requests;
	}
}
