<?php

declare(strict_types=1);

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

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\UI\Component\Dropzone\File\Wrapper;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\UI\Component\Input\Field\Group;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepoStandardUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    protected ?ilLogger $log = null;
    protected Closure $result_handler;
    protected string $file_id_parameter = "";

    public function __construct(
        Closure $result_handler,
        string $file_id_parameter,
        string $logger_id = ""
    ) {
        global $DIC;
        parent::__construct();

        if ($logger_id !== "") {
            $this->log = ilLoggerFactory::getLogger($logger_id);
        }
        $this->result_handler = $result_handler;
        $this->file_id_parameter = $file_id_parameter;
    }

    protected function debug(string $mess): void
    {
        if (!is_null($this->log)) {
            $this->log->debug($mess);
        }
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->debug("checking for uploads...");
        if ($this->upload->hasUploads()) {
            $this->debug("has upload...");
            try {
                $this->upload->process();
                $this->debug("nr of results: " . count($this->upload->getResults()));
                foreach ($this->upload->getResults(
                ) as $result) { // in this version, there will only be one upload at the time

                    $rh = $this->result_handler;
                    $id = $rh($this->upload, $result);

                    $result = new BasicHandlerResult(
                        $this->getFileIdentifierParameterName(),
                        BasicHandlerResult::STATUS_OK,
                        $id,
                        ''
                    );
                }
            } catch (Exception $e) {
                $result = new BasicHandlerResult(
                    $this->getFileIdentifierParameterName(),
                    BasicHandlerResult::STATUS_FAILED,
                    '',
                    $e->getMessage()
                );
            }
            $this->debug("end of 'has_uploads'");
        } else {
            $this->debug("has no upload...");
        }

        return $result;
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $identifier,
            ''
        );
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        return null;
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        return [];
    }

    public function getFileIdentifierParameterName(): string
    {
        return $this->file_id_parameter;
    }
}
