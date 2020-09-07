<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 */
class ilDBUpdate5069
{
    /**
     * Migration for bug fix 19795
     */
    public function fix19795()
    {
        global $ilDB;

        //query only the feedback with uploaded files.
        $result = $ilDB->query(
            "SELECT exc_assignment.exc_id, exc_assignment.peer_crit_cat, exc_assignment.id, exc_assignment_peer.giver_id, exc_assignment_peer.peer_id" .
            " FROM exc_assignment_peer, exc_assignment" .
            " WHERE exc_assignment.id = exc_assignment_peer.ass_id" .
            " AND (exc_assignment.peer_file = 1 OR exc_assignment.peer_crit_cat > 0)" .
            " AND exc_assignment_peer.tstamp IS NOT null"
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            include_once("./Services/Migration/DBUpdate_5069/classes/class.ilFSStorageExercise5069.php");
            $storage = new ilFSStorageExercise5069($row['exc_id'], $row['id']);
            //echo "0 -".$row['peer_crit_cat']."<br>";
            //if the assignment has criteria category
            if ($row['peer_crit_cat']) {
                //get criteria id which is uploader
                // *** string -> text
                $res_crit = $ilDB->query(
                    $q = "SELECT id FROM exc_crit " .
                    " WHERE parent = " . $ilDB->quote($row['peer_crit_cat'], "integer") .
                    " AND type = " . $ilDB->quote('file', 'text')
                );
                //echo "#".$q."#<br>";
                while ($row_crit = $ilDB->fetchAssoc($res_crit)) {
                    $original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row_crit['id']);
                    //echo "a-$original_path-<br>";
                    $path_peaces = explode('/', rtrim($original_path, '/'));
                    array_pop($path_peaces);
                    $previous_dir_path = "";
                    foreach ($path_peaces as $piece) {
                        $previous_dir_path .= $piece . "/";
                    }
                    $dir_content = array_diff(scandir($previous_dir_path), array('.', '..'));
                    //echo $previous_dir_path."<br>";
                    //loop the directory content
                    foreach ($dir_content as $content) {
                        if (is_dir($previous_dir_path . $content)) {
                            //if the directory name is wrong(giver_id+criteria_id)
                            if ($content == $row['giver_id'] . $row_crit['id']) {
                                if (!is_dir($previous_dir_path . $row['giver_id'])) {
                                    mkdir($previous_dir_path . $row['giver_id']);
                                    //echo "1 make dir ".$previous_dir_path.$row['giver_id']."<br>";
                                }

                                if (!is_dir($previous_dir_path . $row['giver_id'] . "/" . $row_crit['id'])) {
                                    mkdir($previous_dir_path . $row['giver_id'] . "/" . $row_crit['id']);
                                    //echo "2 make dir ".$previous_dir_path.$row['giver_id']."/".$row_crit['id']."<br>";
                                }


                                $old = substr($original_path, 0, strlen($original_path) - 1);
                                $new = $previous_dir_path . $row['giver_id'] . "/" . $row_crit['id'];
                                if (is_dir($old)) {
                                    rename($old, $new);
                                }
                                //echo "rename ".$old." to ".$new."<br>";
                            }
                        }
                    }
                }
            }
            //assignment without criteria (just rename the file and move it to the proper directory)
            else {
                $original_path = $storage->getPeerReviewUploadPath($row['peer_id'], $row['giver_id'], $row['peer_crit_cat']);

                $path_peaces = explode('/', rtrim($original_path, '/'));
                array_pop($path_peaces);
                $previous_dir_path = "";
                foreach ($path_peaces as $piece) {
                    $previous_dir_path .= $piece . "/";
                }

                $dir_content = array_diff(scandir($previous_dir_path), array('.', '..'));

                foreach ($dir_content as $content) {
                    if (!is_dir($previous_dir_path . $content)) {
                        if (substr($content, 0, strlen($row['giver_id'])) === $row['giver_id']) {
                            $new_filename = substr($content, strlen($row['giver_id']));
                            copy($previous_dir_path . $content, $original_path . "/" . $new_filename);
                            //echo "5 copy ".$previous_dir_path.$content." to ".$original_path."/".$new_filename."<br>";
                        }
                    }
                }
            }
        }

        //		die("ende");
    }
}
