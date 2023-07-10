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
 * Class ilServicesTreeExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilServicesTreeExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return ilExtractedParams
     */
    public function extract(string $event, array $parameters): ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('tree');

        switch ($event) {
            case 'moveTree':
                $this->extractTree($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractTree(array $parameters): void
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('tree');
        $this->ilExtractedParams->setContextId($parameters['tree']);
    }
}
