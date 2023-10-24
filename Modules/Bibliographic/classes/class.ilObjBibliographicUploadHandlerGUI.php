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
 */

declare(strict_types=1);

use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 * @ilCtrl_isCalledBy ilObjBibliographicUploadHandlerGUI: ilObjBibliographicGUI, ilRepositoryGUI, ilDashboardGUI
 */
class ilObjBibliographicUploadHandlerGUI extends ilCtrlAwareStorageUploadHandler
{
    public function __construct(private string $rid = "")
    {
        parent::__construct(new ilObjBibliographicStakeholder());
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult && $result->isOK()) {
            $resource_identification = $this->storage->manage()->find($this->rid);
            if ($resource_identification !== null) {
                $identifier = $this->storage->manage()->replaceWithUpload(
                    $resource_identification,
                    $result,
                    $this->stakeholder
                )->getIdentification()->serialize();
            } else {
                $identifier = $this->storage->manage()->upload($result, $this->stakeholder)->serialize();
            }
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }
}
