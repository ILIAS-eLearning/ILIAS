<?php
use ILIAS\TMS\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');

/**
 * Class ilTMSICalBuilder
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilTMSICalBuilder implements Mailing\ICalBuilder
{
	const TITLE = "Titel";
	const DESCRIPTION = "Beschreibung";
	const DATE = "Datum";
	const VENUE = "Veranstalter";
	const TIME = "Zeit";
	const FILE_EXTENSION = ".ics";

	/**
	 * @inheritdoc
	 */
	public function getICalString(array $info)
	{
		return $this->buildICal($info);
	}

	/**
	 * Build an iCal string.
	 *
	 * @param 	array 	$info
	 * @return 	string
	 */
	protected function buildICal(array $info)
	{
		$crs_name = "";
		$description = "";
		$duration = "";
		$times = "";
		$venue = "";

		foreach($info as $i) {
			switch ($i->getLabel()) {
				case self::TITLE:
					$title =  $i->getValue();
					break;
				case self::DESCRIPTION:
					$description = $i->getValue();
					break;
				case self::DATE:
					$date = $i->getValue();
					break;
				case self::TIME:
					$times = $i->getValue();
					break;
				case self::VENUE:
					$venue = $i->getValue();
					break;
				default:
					break;
			}
		}

		$calendar = new \Eluceo\iCal\Component\Calendar($title);
		$tz_rule_daytime = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
		$tz_rule_daytime
			->setTzName('CEST')
			->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz))
			->setTzOffsetFrom('+0100')
			->setTzOffsetTo('+0200');
		$tz_rule_daytime_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_daytime_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(3)
			->setByDay('-1SU');
		$tz_rule_daytime->setRecurrenceRule($tz_rule_daytime_rec);
		$tz_rule_standart = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
		$tz_rule_standart
			->setTzName('CET')
			->setDtStart(new \DateTime('1996-10-27 03:00:00', $dtz))
			->setTzOffsetFrom('+0200')
			->setTzOffsetTo('+0100');
		$tz_rule_standart_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_standart_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(10)
			->setByDay('-1SU');
		$tz_rule_standart->setRecurrenceRule($tz_rule_standart_rec);
		$tz = new \Eluceo\iCal\Component\Timezone('Europe/Berlin');
		$tz->addComponent($tz_rule_daytime);
		$tz->addComponent($tz_rule_standart);
		$calendar->setTimezone($tz);

		if($times === "") {
			$event = new \Eluceo\iCal\Component\Event();
			$event
				->setDtStart(new \DateTime($date['start']))
				->setDtEnd(new \DateTime($date['end']))
				->setNoTime(false)
				->setLocation($venue, $venue)
				->setUseTimezone(true)
				->setSummary($title)
				->setDescription($description);
			$calendar
				->setTimezone($tz)
				->addComponent($event);
		} else {
			foreach ($times as $time) {
				$start = $time['date']." ".$time['start_time'].":00";
				$end = $time['date']." ".$time['end_time'].":00";

				$event = new \Eluceo\iCal\Component\Event();
				$event
					->setDtStart(new \DateTime($start))
					->setDtEnd(new \DateTime($end))
					->setNoTime(false)
					->setLocation($venue, $venue)
					->setUseTimezone(true)
					->setSummary($title)
					->setDescription($description);
				$calendar
					->setTimezone($tz)
					->addComponent($event);
			}
		}
		return $calendar->render();
	}

	/**
	 * Creates a iCal file and return its path.
	 *
	 * @param 	string 	$ical
	 * @param 	string 	$file_name
	 * @return 	string
	 */
	public function saveICal($ical, $file_name)
	{
		assert('is_string($ical)');

		$dir = sys_get_temp_dir();
		$tmp = $dir."/".$file_name.self::FILE_EXTENSION;
		file_put_contents($tmp, $ical);
		return $tmp;
	}
}