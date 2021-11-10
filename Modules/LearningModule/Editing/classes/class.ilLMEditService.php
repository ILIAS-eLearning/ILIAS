<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Main service init and factory
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMEditService
{
    protected int $ref_id;
    protected ilObjLearningModule $lm;
    protected ilLMEditRequest $request;

    public function __construct(
        array $query_params
    ) {
        $this->request = new ilLMEditRequest($query_params);
        $this->ref_id = $this->request->getRequestedRefId();
        $this->lm = new ilObjLearningModule($this->ref_id);
    }

    public function getLearningModule() : ilObjLearningModule
    {
        return $this->lm;
    }

    public function getRequest() : ilLMEditRequest
    {
        return $this->request;
    }
}
