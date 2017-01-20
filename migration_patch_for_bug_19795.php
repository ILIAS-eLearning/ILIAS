<?php
/**
 * Migration Patch after apply this bugfix-> http://www.ilias.de/mantis/view.php?id=19795
 * Store the files provided for the users when they create feedback. (Peer-Feedback)
 * @author Jesús López <lopez@leifos.com>
 */
include ("./include/inc.header.php");

global $DIC;

$db = $DIC->database();
$log = $DIC->logger();

//query only the feedback with uploaded files.
$result = $db->query("SELECT exc_assignment.exc_id, exc_assignment.peer_crit_cat, exc_assignment.id, exc_assignment_peer.giver_id, exc_assignment_peer.peer_id".
	" FROM exc_assignment_peer, exc_assignment".
	" WHERE exc_assignment.id = exc_assignment_peer.ass_id".
	" AND (exc_assignment.peer_file = 1 OR exc_assignment.peer_crit_cat > 0)".
	" AND exc_assignment_peer.tstamp IS NOT null"
);

while($row = $db->fetchAssoc($result))
{
	include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
	$storage = new ilFSStorageExercise($row['exc_id'], $row['id']);

	//if the assignment has criteria category
	if($row['peer_crit_cat'])
	{
		$log->root()->debug("CRITERIA");

		//get criteria id which is uploader
		$res_crit = $db->query("SELECT id FROM exc_crit ".
			" WHERE parent = ".$row['peer_crit_cat'].
			" AND type = 'file'"
		);

		while($row_crit = $db->fetchAssoc($res_crit))
		{
			$original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row_crit['id']);

			$previous_dir_path = getPreviousPath($original_path);

			$dir = opendir($previous_dir_path);
			$dir_content = array_diff(scandir($previous_dir_path), array('.', '..'));

			//loop the directory content
			foreach($dir_content as $content)
			{
				if(is_dir($previous_dir_path.$content))
				{
					//if the directory name is wrong(giver_id+criteria_id)
					if($content == $row['giver_id'].$row_crit['id'])
					{
						if(!is_dir($previous_dir_path.$row['giver_id']))
						{
							$log->root()->debug("CREATED DIRECTORY => ".$previous_dir_path.$row['giver_id']);
							mkdir($previous_dir_path.$row['giver_id']);
						}

						if(!is_dir($previous_dir_path.$row['giver_id']."/".$row_crit['id']))
						{
							$log->root()->debug("CREATED DIRECTORY => ".$previous_dir_path.$row['giver_id']."/".$row_crit['id']);
							mkdir($previous_dir_path.$row['giver_id']."/".$row_crit['id']);
						}

						//take the files of the wrong directory and copy them into the proper new directory.
						$sub_dir_content = array_diff(scandir($original_path), array('.', '..'));

						foreach($sub_dir_content as $sub_content)
						{
							if(!is_dir($original_path.$sub_content))
							{
								$copy = copy($original_path.$sub_content, $previous_dir_path.$row['giver_id']."/".$row_crit['id']."/".$sub_content);
							}
						}
						//rename the old directory with _ ?¿?¿
						$log->root()->debug("COPY FILES FROM $original_path   TO   ".$previous_dir_path.$row['giver_id']."/".$row_crit['id']);
					}
				}
			}
		}
	}
	//assignment without criteria (just rename the file and move it to the proper directory)
	else
	{
		$log->root()->debug("NO criteria");

		$original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row['peer_crit_cat']);

		$previous_dir_path = getPreviousPath($original_path);

		$dir = opendir($previous_dir_path);

		$dir_content = array_diff(scandir($previous_dir_path), array('.', '..'));

		foreach($dir_content as $content)
		{
			if(!is_dir($previous_dir_path.$content))
			{
				$log->root()->debug("file => $content");
				if (substr($content, 0, strlen($row['giver_id'])) === $row['giver_id'])
				{
					$new_filename = substr($content, strlen($row['giver_id']));
					$copy = copy($previous_dir_path.$content, $original_path."/".$new_filename);
					$log->root()->debug("COPY FILE".$previous_dir_path.$content."   TO   ".$original_path.$new_filename);
				}
			}
		}


	}

}

function getPreviousPath($a_original_path)
{
	$path_peaces = explode('/', rtrim($a_original_path, '/'));
	array_pop($path_peaces);
	$previous_dir_path = "";
	foreach ($path_peaces as $piece)
	{
		$previous_dir_path .= $piece."/";
	}

	return $previous_dir_path;
}


?>