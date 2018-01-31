<?php
// ---------------------------------------------------------------------------------------------------------------------

/**
 * GENERAL INFORMATION:
 *
 * This script can be used to repair question ordering sequences of tests configured with a fixed question sequence.
 *
 * Due to a bug in earlier ilias versions it might be possible that the question ordering got broken like described in the corresponding mantis report.
 *
 * Mantis Report: https://ilias.de/mantis/view.php?id=20382
 *
 * USAGE INSTRUCTIONS:
 *
 * Save this script to your ilias root directory and either call it
 * with the browser or by using the command line interface instead.
 *
 * For using the web browser you need to login with an account
 * having administrative privileges.
 * http://<your.ilias.domain>/<ilias_path>/fixTestQuestionOrderingGaps.php
 *
 * For using the command line interface change to the ilias root directory
 * and call the script by using the following command. Make sure you made
 * php fixTestQuestionOrderingGaps.php <adminUser> <adminPass> <iliasClientId>
 *
 * The script will report a success message when it finishs its tasks.
 *
 * If any php fatal error occurs due to exhausting any ressource restrictions,
 * simply call the script again until it finishs with the success message.
 *
 */


// ---------------------------------------------------------------------------------------------------------------------

$task = 'show';

if( PHP_SAPI == 'cli' )
{
	include_once('include/inc.ilias_version.php');
	
	if($_SERVER['argc'] < 4)
	{
		die("Usage: php fixTestQuestionOrderingGaps.php username password client [show|fix]\n");
	}
	
	if( version_compare(ILIAS_VERSION_NUMERIC, '5.2.0', '>=') )
	{
		include_once './Services/Cron/classes/class.ilCronStartUp.php';
		
		$cron = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);
		
		try
		{
			$cron->initIlias();
			$cron->authenticate();
		}
		catch (Exception $e)
		{
			echo 'Unknown trouble during ilInit. Exception: ' . $e->getMessage();
			exit(126);
		}
	}
	else
	{
		include_once "Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_CRON);
		
		include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
		ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);
		
		$_COOKIE["ilClientId"] = $_SERVER['argv'][3];
		$_POST['username'] = $_SERVER['argv'][1];
		$_POST['password'] = $_SERVER['argv'][2];
		
		try
		{
			require_once 'include/inc.header.php';
		}
		catch(Exception $e)
		{
			echo 'Unknown trouble during ilInit. Exception: '.$e->getMessage();
			exit(126);
		}
	}
	
	if( isset($_SERVER['argv'][4]) )
	{
		switch($_SERVER['argv'][4])
		{
			case 'show':
			case 'fix':
				
				$task = $_SERVER['argv'][4];
		}
	}
	
	$PROCEED = "
		
		To fix your database call this script with the additional parameter 'fix' (!)
		
		Usage: php fixTestQuestionOrderingGaps.php username password client [show|fix]
		
	";
}
else
{
	if( isset($_GET['fix']) )
	{
		$task = 'fix';
	}
	
	$URL = "<br />
		<a href='http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?fix'>HTTP</a>
		or
		<a href='https://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?fix'>HTTPS</a>
	";
	
	$PROCEED = "
		
		To fix your database use the link below (!)
		{$URL}
		
	";
	
	require_once 'include/inc.header.php';
}

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	echo 'Sorry, this script requires administrative privileges!';
	exit(125);
}

// ---------------------------------------------------------------------------------------------------------------------

if( $task == 'show' )
{
	$res = $ilDB->query("
		SELECT COUNT(DISTINCT test_fi) num_tst, COUNT(test_question_id) num_qst
		FROM tst_test_question WHERE test_fi IN(
			SELECT test_fi FROM tst_test_question
			GROUP BY test_fi HAVING COUNT(test_fi) < MAX(sequence)
		)
	");
	
	$row = $ilDB->fetchAssoc($res);
	
	
	if( $row )
	{
		$numTests = $row['num_tst'];
		$numQuestions = $row['num_qst'];
		echo "<pre>
		
		DEAR ADMINISTRATOR !!
		
		Please read the following instructions CAREFULLY!
		
		-> Due to a bug in almost all earlier versions of ILIAS question orderings
		from the assessment component are broken but repairable.
		
		-> This fixing script can exhaust any php enviroment settings like
		max_execution_time or memory_limit for example.
		
		-> In the case of any php fatal error the script causes that is about exhausting
		any ressource or time restriction you just need to refresh the page by using F5 for example.
		
		Mantis Bug Report: https://ilias.de/mantis/view.php?id=20382
		
		In your database there were > {$numTests} tests < detected having > {$numQuestions} questions < overall,
		that are stored with gaps in the ordering index.
		
		</pre>
		
		{$PROCEED} 
		
		";
	}
}
elseif( $task == 'fix' )
{
	$res = $ilDB->query("
		SELECT test_fi, test_question_id
		FROM tst_test_question WHERE test_fi IN(
			SELECT test_fi FROM tst_test_question
			GROUP BY test_fi HAVING COUNT(test_fi) < MAX(sequence)
		) ORDER BY test_fi ASC, sequence ASC
	");
	
	$tests = array();
	
	while($row = $ilDB->fetchAssoc($res))
	{
		if( !isset($tests[ $row['test_fi'] ]) )
		{
			$tests[ $row['test_fi'] ] = array();
		}
		
		$tests[ $row['test_fi'] ][] = $row['test_question_id'];
	}
	
	foreach($tests as $testFi => $testQuestions)
	{
		for($i = 0, $m = count($testQuestions); $i <= $m; $i++)
		{
			$testQuestionId = $testQuestions[$i];
			
			$position = $i + 1;
			
			$ilDB->update('tst_test_question',
				array( 'sequence' => array('integer', $position) ),
				array( 'test_question_id' => array('integer', $testQuestionId) )
			);
		}
	}
	
	echo "fixing TestQuestionOrderingGaps successfully finished :-)\n";
}

// ---------------------------------------------------------------------------------------------------------------------
