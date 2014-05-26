<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSettingsChangeConfirmationGUI extends ilConfirmationGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;

	/**
	 * @var string
	 */
	private $oldQuestionSetType;

	/**
	 * @var string
	 */
	private $newQuestionSetType;

	/**
	 * @var bool
	 */
	private $questionLossInfoEnabled;

	/**
	 * @param ilLanguage $lng
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilLanguage $lng, ilObjTest $testOBJ)
	{
		$this->lng = $lng;
		$this->testOBJ = $testOBJ;
		
		parent::__construct();
	}

	/**
	 * @param string $oldQuestionSetType
	 */
	public function setOldQuestionSetType($oldQuestionSetType)
	{
		$this->oldQuestionSetType = $oldQuestionSetType;
	}

	/**
	 * @return string
	 */
	public function getOldQuestionSetType()
	{
		return $this->oldQuestionSetType;
	}

	/**
	 * @param string $newQuestionSetType
	 */
	public function setNewQuestionSetType($newQuestionSetType)
	{
		$this->newQuestionSetType = $newQuestionSetType;
	}

	/**
	 * @return string
	 */
	public function getNewQuestionSetType()
	{
		return $this->newQuestionSetType;
	}

	/**
	 * @param boolean $questionLossInfoEnabled
	 */
	public function setQuestionLossInfoEnabled($questionLossInfoEnabled)
	{
		$this->questionLossInfoEnabled = $questionLossInfoEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionLossInfoEnabled()
	{
		return $this->questionLossInfoEnabled;
	}

	private function buildHeaderText()
	{
		$headerText = sprintf(
			$this->lng->txt('tst_change_quest_set_type_from_old_to_new_with_conflict'),
			$this->testOBJ->getQuestionSetTypeTranslation($this->lng, $this->getOldQuestionSetType()),
			$this->testOBJ->getQuestionSetTypeTranslation($this->lng, $this->getNewQuestionSetType())
		);

		if( $this->isQuestionLossInfoEnabled() )
		{
			$headerText .= '<br /><br />'.$this->lng->txt('tst_nonpool_questions_get_lost_warning');
		}

		return $headerText;
	}

	public function build()
	{
		$this->setHeaderText( $this->buildHeaderText() );
	}

	public function populateParametersFromPost()
	{
		foreach ($_POST as $key => $value)
		{
			if (strcmp($key, "cmd") != 0)
			{
				if (is_array($value))
				{
					foreach ($value as $k => $v)
					{
						$this->addHiddenItem("{$key}[{$k}]", $v);
					}
				}
				else
				{
					$this->addHiddenItem($key, $value);
				}
			}
		}
	}

	/**
	 * @param ilPropertyForm $form
	 */
	public function populateParametersFromPropertyForm(ilPropertyFormGUI $form, $timezone)
	{
		foreach ($form->getInputItemsRecursive() as $key => $item)
		{
			//vd("$key // {$item->getType()} // ".json_encode($_POST[$item->getPostVar()]));

			switch( $item->getType() )
			{
				case 'section_header':

					continue;

				case 'datetime':

					list($date, $time) = explode(' ', $item->getDate()->get(IL_CAL_DATETIME));

					if( $item->getMode() == ilDateTimeInputGUI::MODE_SELECT )
					{
						list($y, $m, $d) = explode('-', $date);

						$this->addHiddenItem("{$item->getPostVar()}[date][y]", $y);
						$this->addHiddenItem("{$item->getPostVar()}[date][m]", $m);
						$this->addHiddenItem("{$item->getPostVar()}[date][d]", $d);

						if( $item->getShowTime() )
						{
							list($h, $m, $s) = explode('-', $time);

							$this->addHiddenItem("{$item->getPostVar()}[time][h]", $h);
							$this->addHiddenItem("{$item->getPostVar()}[time][m]", $m);
							$this->addHiddenItem("{$item->getPostVar()}[time][s]", $s);
						}
					}
					else
					{
						$this->addHiddenItem("{$item->getPostVar()}[date]", $date);
						$this->addHiddenItem("{$item->getPostVar()}[time]", $time);
					}

					break;

				case 'duration':

					$this->addHiddenItem("{$item->getPostVar()}[MM]", (int)$item->getMonths());
					$this->addHiddenItem("{$item->getPostVar()}[dd]", (int)$item->getDays());
					$this->addHiddenItem("{$item->getPostVar()}[hh]", (int)$item->getHours());
					$this->addHiddenItem("{$item->getPostVar()}[mm]", (int)$item->getMinutes());
					$this->addHiddenItem("{$item->getPostVar()}[ss]", (int)$item->getSeconds());

					break;

				case 'dateduration':

					$this->addHiddenItem(
						"{$item->getPostVar()}[start][date][y]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'Y',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[start][date][m]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'m',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[start][date][d]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'d',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[start][time][h]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'H',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[start][time][m]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'i',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[start][time][s]",
						(int) $item->getStart()->get(IL_CAL_FKT_DATE,'s',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][date][y]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'Y',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][date][m]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'m',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][date][d]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'d',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][time][h]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'H',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][time][m]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'i',$timezone)
					);
					$this->addHiddenItem(
						"{$item->getPostVar()}[end][time][s]",
						(int) $item->getEnd()->get(IL_CAL_FKT_DATE,'s',$timezone)
					);

					break;

				case 'checkboxgroup':

					if( is_array($item->getValue()) )
					{
						foreach( $item->getValue() as $option )
						{
							$this->addHiddenItem("{$item->getPostVar()}[]", $option);
						}
					}

					break;

				case 'checkbox':

					if( $item->getChecked() )
					{
						$this->addHiddenItem($item->getPostVar(), 1);
					}

					break;

				default:

					$this->addHiddenItem($item->getPostVar(), $item->getValue());
			}
		}
	}
} 