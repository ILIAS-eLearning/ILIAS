<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilFilterGUI.php';
include_once 'Services/Payment/classes/class.ilPaymentSettings.php';

/**
 * Class ilAdvancedSearchFilterGUI
 * @author: Nadia Ahmad <nahmad@databay.de>
 * @version $Id:
 */
class ilAdvancedSearchFilterGUI extends ilFilterGUI
{
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		$this->settings = ilPaymentSettings::_getInstance();
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setFilterId('advanced_filter');
		$this->setFilterCols(3);
	}

	public function initFilter()
	{
		global $lng, $ilUser;
		
		// search term
		$search_term      = new ilTextInputGUI($lng->txt('search_search_term'), 'search_string');
		$search_term->setValue($_SESSION['shop_advanced_search']['string']);
		$search_term->setPostVar('search_string');
		$this->addFilterItem($search_term);
		$search_term->readFromSession();
		$this->filter["search_string"] = $search_term->getValue();

		
		// search combination
		$radio_grp = new ilRadioGroupInputGUI('','search_combination');
		
		$radio_or = new ilRadioOption($lng->txt('search_any_word'), 'or');
		$radio_and = new ilRadioOption($lng->txt('search_all_words'), 'and');
		
		$radio_grp->addOption($radio_or);
		$radio_grp->addOption($radio_and);
		$radio_grp->setValue($_POST['search_combination']);
		$this->addFilterItem($radio_grp);
		$radio_grp->readFromSession();
		$this->filter['search_combination'] = $radio_grp->getValue();
		
		// search objects
		$object_types = new ilCheckboxGroupInputGUI($lng->txt('obj_type'), 'search_details');
		
		$cb_crs = new ilCheckboxInputGUI($lng->txt('courses'), 'search_details["crs"]');
		$cb_crs->setValue('crs');
		
		$cb_lms = new ilCheckboxInputGUI($lng->txt('learning_resources'), 'search_details["lms"]');
		$cb_lms->setValue('lms');
		
		$cb_tst = new ilCheckboxInputGUI($lng->txt('tests'), 'search_details["tst"]');
		$cb_tst->setValue('tst');
		
		$cb_fil = new ilCheckboxInputGUI($lng->txt('objs_file'), 'search_details["fil"]');
		$cb_fil->setValue('fil');
		
		$object_types->addOption($cb_crs);
		$object_types->addOption($cb_lms);
		$object_types->addOption($cb_tst);
		$object_types->addOption($cb_fil);
		
		$object_types->setValue($_SESSION['shop_advanced_search']['details']);

		$this->addFilterItem($object_types);
		$object_types->readFromSession();
		$this->filter['search_details'] = $object_types->getValue(); 
		
		// search topics
		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		$topic_option = array();
		if(count(ilShopTopics::_getInstance()->getTopics()))
		{
			$topic_option[''] = $lng->txt('please_select');
			foreach(ilShopTopics::_getInstance()->getTopics() as $oTopic)
			{
				$topic_option[(string)$oTopic->getId()] = $oTopic->getTitle();
			}
		}
		else
		{
			$topic_option[''] = $lng->txt('no_topics_yet');
		}
		$o_topic = new ilSelectInputGUI();
		$o_topic->setTitle($lng->txt('topic'));
		$o_topic->setOptions($topic_option);
		$o_topic->setValue($_SESSION['shop_advanced_search']['topic']);
		$o_topic->setPostVar('search_topic');
		$this->addFilterItem($o_topic);
		$o_topic->readFromSession();
		$this->filter["search_topic"] = $o_topic->getValue();

		if((bool)$this->settings->get('objects_allow_custom_sorting'))
		{
			// sorting form
			$allow_objects_option = array(
				'title'  => $lng->txt('title'),
				'author' => $lng->txt('author'),
				'price'  => $lng->txt('price_a')
			);
			$o_allow_objects      = new ilSelectInputGUI();
			$o_allow_objects->setTitle($lng->txt('sort_by'));
			$o_allow_objects->setOptions($allow_objects_option);
			$o_allow_objects->setValue($this->getSortField());
			$o_allow_objects->setPostVar('order_field'); //objects_sorting_type
			$this->addFilterItem($o_allow_objects);
			$o_allow_objects->readFromSession();
			$this->filter["order_field"] = $o_allow_objects->getValue();

			$direction_option = array(
				'asc'  => $lng->txt('sort_asc'),
				'desc' => $lng->txt('sort_desc')
			);

			$o_object_direction = new ilSelectInputGUI();

			$o_object_direction->setOptions($direction_option);
			$o_object_direction->setValue($this->getSortDirection());
			$o_object_direction->setPostVar('order_direction'); //objects_sorting_direction

			$this->addFilterItem($o_object_direction);
			$o_object_direction->readFromSession();
			$this->filter["order_direction"] = $o_object_direction->getValue();
		}

		if((bool)$this->settings->get('topics_allow_custom_sorting'))
		{
			// sorting form
			$allow_topics_option = array(
				ilShopTopics::TOPICS_SORT_BY_TITLE      => $lng->txt('sort_topics_by_title'),
				ilShopTopics::TOPICS_SORT_BY_CREATEDATE => $lng->txt('sort_topics_by_date')
			);
			if(ANONYMOUS_USER_ID != $ilUser->getId())
			{
				$allow_topics_option[ilShopTopics::TOPICS_SORT_MANUALLY] = $lng->txt('sort_topics_manually');
			}

			$o_allow_topics = new ilSelectInputGUI();
			$o_allow_topics->setTitle($lng->txt('sort_topics_by'));
			$o_allow_topics->setOptions($allow_topics_option);

			$o_allow_topics->setValue($this->getSortingTypeTopics());
			$o_allow_topics->setPostVar('topics_sorting_type');
			$this->addFilterItem($o_allow_topics);
			$o_allow_topics->readFromSession();
			$this->filter["topics_sorting_type"] = $o_allow_topics->getValue();

			$direction_option = array(
				'asc'  => $lng->txt('sort_asc'),
				'desc' => $lng->txt('sort_desc')
			);

			$o_topics_direction = new ilSelectInputGUI();
			$o_topics_direction->setOptions($direction_option);
			$o_topics_direction->setValue($this->getSortingDirectionTopics());
			$o_topics_direction->setPostVar('topics_sorting_direction'); //objects_sorting_type

			$this->addFilterItem($o_topics_direction);
			$o_topics_direction->readFromSession();
			$this->filter["topics_sorting_direction"] = $o_topics_direction->getValue();
		}
	}
	public function setTopicId($a_topic_id)
	{
		$this->topic_id = $a_topic_id;
	}

	public function getTopicId()
	{
		return $this->topic_id;
	}

	public function setString($a_str)
	{
		$this->string = $a_str;
	}

	public function getString()
	{
		return $this->string;
	}

	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setSortDirection($a_sort_direction)
	{
		$this->sort_direction = $a_sort_direction;
	}

	public function getSortDirection()
	{
		return $this->sort_direction;
	}

	public function setSortField($a_field)
	{
		$this->sort_field = $a_field;
	}

	public function getSortField()
	{
		return $this->sort_field;
	}

	public function setSortingTypeTopics($a_field)
	{
		global $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId() &&
			$a_field == ilShopTopics::TOPICS_SORT_MANUALLY
		)
		{
			$a_field = ilShopTopics::TOPICS_SORT_BY_TITLE;
		}

		$this->sort_type_topics = $a_field;
	}

	public function getSortingTypeTopics()
	{
		global $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId() &&
			$this->sort_type_topics == ilShopTopics::TOPICS_SORT_MANUALLY
		)
		{
			$this->sort_type_topics = ilShopTopics::TOPICS_SORT_BY_TITLE;
		}

		return $this->sort_type_topics;
	}

	public function setSortingDirectionTopics($a_sort_direction)
	{
		$_SESSION['shop_content']['shop_topics_sorting_direction'] = $this->sort_direction_topics = $a_sort_direction;
	}

	public function getSortingDirectionTopics()
	{
		return $this->sort_direction_topics;
	}
}
