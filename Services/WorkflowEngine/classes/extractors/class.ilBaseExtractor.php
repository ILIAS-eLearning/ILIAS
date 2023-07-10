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
 * Class ilBaseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
abstract class ilBaseExtractor implements ilExtractor
{
    protected ilExtractedParams $ilExtractedParams;

    public function __construct(ilExtractedParams $ilExtractedParams)
    {
        $this->ilExtractedParams = $ilExtractedParams;
    }

    /**
     * @param string $event
     * @param array  $parameters
     */
    abstract public function extract(string $event, array $parameters): ilExtractedParams;

    /**
     * @param array $parameters
     */
    protected function extractWithUser(array $parameters): void
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['usr_id'] ?? 0);
    }

    /**
     * @param array $parameters
     */
    protected function extractWithoutUser(array $parameters): void
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }
}
