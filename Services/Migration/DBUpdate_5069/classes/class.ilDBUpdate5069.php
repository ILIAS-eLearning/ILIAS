<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 */
class ilDBUpdate5069
{
	/**
	 * Migration for bug fix 19795
	 *
	 * @param
	 * @return
	 */
	function fix19795()
	{
		global $ilDB;

		//query only the feedback with uploaded files.
		$result = $ilDB->query("SELECT exc_assignment.exc_id, exc_assignment.peer_crit_cat, exc_assignment.id, exc_assignment_peer.giver_id, exc_assignment_peer.peer_id".
			" FROM exc_assignment_peer, exc_assignment".
			" WHERE exc_assignment.id = exc_assignment_peer.ass_id".
			" AND (exc_assignment.peer_file = 1 OR exc_assignment.peer_crit_cat > 0)".
			" AND exc_assignment_peer.tstamp IS NOT null"
		);

		while($row = $ilDB->fetchAssoc($result))
		{
			include_once("./Services/Migration/classes/class.ilFSStorageExercise5069.php");
			$storage = new ilFSStorageExercise5069($row['exc_id'], $row['id']);

			//if the assignment has criteria category
			if($row['peer_crit_cat'])
			{
				//get criteria id which is uploader
				$res_crit = $ilDB->query("SELECT id FROM exc_crit ".
					" WHERE parent = ".$ilDB->quote($row['peer_crit_cat'],"integer").
					" AND type = ".$ilDB->quote('file','string')
				);

				while($row_crit = $ilDB->fetchAssoc($res_crit))
				{
					$original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row_crit['id']);

// defined ?
					$path_peaces = explode('/', rtrim($original_path, '/'));
					array_pop($path_peaces);
					$previous_dir_path = "";
					foreach ($path_peaces as $piece)
					{
						$previous_dir_path .= $piece."/";
					}
// needed?
//					$dir = opendir($previous_dir_path);
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
//									mkdir($previous_dir_path.$row['giver_id']);
echo "make dir ".$previous_dir_path.$row['giver_id']."<br>";
								}

								if(!is_dir($previous_dir_path.$row['giver_id']."/".$row_crit['id']))
								{
//									mkdir($previous_dir_path.$row['giver_id']."/".$row_crit['id']);
echo "make dir ".$previous_dir_path.$row['giver_id']."/".$row_crit['id']."<br>";
								}

								//take the files of the wrong directory and copy them into the proper new directory.
								$sub_dir_content = array_diff(scandir($original_path), array('.', '..'));

								foreach($sub_dir_content as $sub_content)
								{
									if(!is_dir($original_path.$sub_content))
									{
//										$copy = copy($original_path.$sub_content, $previous_dir_path.$row['giver_id']."/".$row_crit['id']."/".$sub_content);
echo " copy ".$original_path.$sub_content." to ".$previous_dir_path.$row['giver_id']."/".$row_crit['id']."/".$sub_content."<br>";
									}
								}
								array_map("unlink",glob($previous_dir_path.$content."/*.*"));
//								rmdir($previous_dir_path.$content);
echo " rmdir ".$previous_dir_path.$content."<br>";
							}
						}
					}
				}
			}
			//assignment without criteria (just rename the file and move it to the proper directory)
			else
			{
				$original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row['peer_crit_cat']);

// defined?
				$path_peaces = explode('/', rtrim($original_path, '/'));
				array_pop($path_peaces);
				$previous_dir_path = "";
				foreach ($path_peaces as $piece)
				{
					$previous_dir_path .= $piece."/";
				}

// needed?
//				$dir = opendir($previous_dir_path);

				$dir_content = array_diff(scandir($previous_dir_path), array('.', '..'));

				foreach($dir_content as $content)
				{
					if(!is_dir($previous_dir_path.$content))
					{
						if (substr($content, 0, strlen($row['giver_id'])) === $row['giver_id'])
						{
							$new_filename = substr($content, strlen($row['giver_id']));
//							$copy = copy($previous_dir_path.$content, $original_path."/".$new_filename);
echo "copy ".$previous_dir_path.$content." to ".$original_path."/".$new_filename."<br>";
						}
					}
				}

			}

		}
	}
	
}
