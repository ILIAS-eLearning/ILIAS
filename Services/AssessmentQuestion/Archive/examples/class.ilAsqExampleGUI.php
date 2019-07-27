<?php
namespace ILIAS\AssessmentQuestion\Example;

require_once "./Services/AssessmentQuestion/examples/Config/SQLiteDB.php";

use ILIAS\AssessmentQuestion\Example\Config\SQLiteDB;
use ILIAS\Data\Domain\AggregateHistory;

/**
 * Class ilAsqExampleGUI
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ILIAS\AssessmentQuestion\Example\ilAsqExampleGUI: ilUIPluginRouterGUI
 */
class ilAsqExampleGUI {

	/**
	 * ilAsqExampleGUI constructor
	 */
	public function __construct() {
		global $DIC;

		//TODO
		//Display Form with Title / Descritption AND create Question
		//Display Form for adding Answers And add possible Answers
		//Set Status to Online and Project to MemeberView

		//Display MemberView with the possibility to answer to the question.

		//Display Form for changing Answers and publish that;

		//-> Member has previus version of Question!




		$DIC->ui()->mainTemplate()->setContent(var_dump($events));
exit;
	}
}