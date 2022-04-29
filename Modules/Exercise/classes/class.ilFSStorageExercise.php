<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFSStorageExercise extends ilFileSystemAbstractionStorage
{
    protected int $ass_id;
    protected string $submission_path;
    protected string $tmp_path;
    protected string $feedb_path;
    protected string $multi_feedback_upload_path;
    protected string $peer_review_upload_path;

    public function __construct(
        int $a_container_id = 0,
        int $a_ass_id = 0
    ) {
        $this->ass_id = $a_ass_id;
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }

    /**
     * Append ass_<ass_id> to path (assignment id)
     */
    public function init() : bool
    {
        if (parent::init()) {
            if ($this->ass_id > 0) {
                $this->submission_path = $this->path . "/subm_" . $this->ass_id;
                $this->tmp_path = $this->path . "/tmp_" . $this->ass_id;
                $this->feedb_path = $this->path . "/feedb_" . $this->ass_id;
                $this->multi_feedback_upload_path = $this->path . "/mfb_up_" . $this->ass_id;
                $this->peer_review_upload_path = $this->path . "/peer_up_" . $this->ass_id;
                $this->path .= "/ass_" . $this->ass_id;
            }
        } else {
            return false;
        }
        return true;
    }

    protected function getPathPostfix() : string
    {
        return 'exc';
    }

    protected function getPathPrefix() : string
    {
        return 'ilExercise';
    }

    public function getAbsoluteSubmissionPath() : string
    {
        return $this->submission_path;
    }

    public function getTempPath() : string
    {
        return $this->tmp_path;
    }

    public function getFeedbackPath(
        string $a_user_id
    ) : string {
        $path = $this->feedb_path . "/" . $a_user_id;
        if (!file_exists($path)) {
            ilFileUtils::makeDirParents($path);
        }
        return $path;
    }

    public function getGlobalFeedbackPath() : string
    {
        $path = $this->feedb_path . "/0";
        if (!file_exists($path)) {
            ilFileUtils::makeDirParents($path);
        }
        return $path;
    }

    /**
     * Get multi feedback upload path
     * (each uploader handled in a separate path)
     */
    public function getMultiFeedbackUploadPath(
        int $a_user_id
    ) : string {
        $path = $this->multi_feedback_upload_path . "/" . $a_user_id;
        if (!file_exists($path)) {
            ilFileUtils::makeDirParents($path);
        }
        return $path;
    }

    /**
     * Get pear review upload path
     * (each peer handled in a separate path)
     */
    public function getPeerReviewUploadPath(
        int $a_peer_id,
        int $a_giver_id,
        ?int $a_crit_id = null
    ) : string {
        $path = $this->peer_review_upload_path . "/" . $a_peer_id . "/" . $a_giver_id . "/";

        if ((int) $a_crit_id !== 0) {
            $path .= (int) $a_crit_id . "/";
        }
        if (!file_exists($path)) {
            ilFileUtils::makeDirParents($path);
        }
        return $path;
    }

    /**
     * Create directory
     */
    public function create() : void
    {
        parent::create();
        if (!file_exists($this->submission_path)) {
            ilFileUtils::makeDirParents($this->submission_path);
        }
        if (!file_exists($this->tmp_path)) {
            ilFileUtils::makeDirParents($this->tmp_path);
        }
        if (!file_exists($this->feedb_path)) {
            ilFileUtils::makeDirParents($this->feedb_path);
        }
    }

    public function getFiles() : array
    {
        $files = array();
        if (!is_dir($this->path)) {
            return $files;
        }

        $dp = opendir($this->path);
        while ($file = readdir($dp)) {
            if (!is_dir($this->path . '/' . $file)) {
                $files[] = array(
                    'name' => $file,
                    'size' => filesize($this->path . '/' . $file),
                    'ctime' => filectime($this->path . '/' . $file),
                    'fullpath' => $this->path . '/' . $file);
            }
        }
        closedir($dp);
        return ilArrayUtil::sortArray($files, "name", "asc");
    }


    ////
    //// Handle submitted files
    ////

    /**
     * store delivered file in filesystem
     * @param array $a_http_post_file
     * @param int   $user_id
     * @param bool  $is_unziped
     * @return ?array result array with filename and mime type of the saved file
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function uploadFile(
        array $a_http_post_file,
        int $user_id,
        bool $is_unziped = false
    ) : ?array {
        $this->create();
        // TODO:
        // CHECK UPLOAD LIMIT

        //
        $result = null;
        if (isset($a_http_post_file) && $a_http_post_file['size']) {
            $filename = $a_http_post_file['name'];

            $filename = ilFileUtils::getValidFilename($filename);
            // replace whitespaces with underscores
            $filename = preg_replace("/\s/", "_", $filename);
            // remove all special characters
            $filename = preg_replace("/[^_a-zA-Z0-9\.]/", "", $filename);

            if (!is_dir($savepath = $this->getAbsoluteSubmissionPath())) {
                ilFileUtils::makeDir($savepath);
            }
            $savepath .= '/' . $user_id;
            if (!is_dir($savepath)) {
                ilFileUtils::makeDir($savepath);
            }

            // CHECK IF FILE PATH EXISTS
            if (!is_dir($savepath)) {
                ilFileUtils::makeDir($savepath);
            }
            $now = getdate();
            $prefix = sprintf(
                "%04d%02d%02d%02d%02d%02d",
                $now["year"],
                $now["mon"],
                $now["mday"],
                $now["hours"],
                $now["minutes"],
                $now["seconds"]
            );

            if (!$is_unziped) {
                ilFileUtils::moveUploadedFile(
                    $a_http_post_file["tmp_name"],
                    $prefix . "_" . $filename,
                    $savepath . "/" . $prefix . "_" . $filename
                );
            } else {
                rename(
                    $a_http_post_file['tmp_name'],
                    $savepath . "/" . $prefix . "_" . $filename
                );
            }

            if (is_file($savepath . "/" . $prefix . "_" . $filename)) {
                $result = array(
                    "filename" => $prefix . "_" . $filename,
                    "fullname" => $savepath . "/" . $prefix . "_" . $filename,
                    "mimetype" => ilObjMediaObject::getMimeType($savepath . "/" . $prefix . "_" . $filename)
                );
            }
        }
        return $result;
    }

    /**
     * @return string[]
     */
    public function getFeedbackFiles(
        string $a_user_id
    ) : array {
        $files = array();

        $dir = $this->getFeedbackPath($a_user_id);
        if (is_dir($dir)) {
            $dp = opendir($dir);
            while ($file = readdir($dp)) {
                if (!is_dir($this->path . '/' . $file) && substr($file, 0, 1) != ".") {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    public function countFeedbackFiles(
        string $a_user_id
    ) : int {
        $fbf = $this->getFeedbackFiles($a_user_id);
        return count($fbf);
    }

    public function getAssignmentFilePath(string $a_file) : string
    {
        return $this->getAbsolutePath() . "/" . $a_file;
    }

    public function getFeedbackFilePath(
        string $a_user_id,
        string $a_file
    ) : string {
        $dir = $this->getFeedbackPath($a_user_id);
        return $dir . "/" . $a_file;
    }

    /**
     * @throws ilException
     */
    public function uploadAssignmentFiles(
        array $a_files
    ) : void {
        if (is_array($a_files["name"])) {
            foreach ($a_files["name"] as $k => $name) {
                if ($name != "") {
                    $tmp_name = $a_files["tmp_name"][$k];
                    ilFileUtils::moveUploadedFile(
                        $tmp_name,
                        basename($name),
                        $this->path . DIRECTORY_SEPARATOR . basename($name),
                        false
                    );
                }
            }
        }
    }
}
