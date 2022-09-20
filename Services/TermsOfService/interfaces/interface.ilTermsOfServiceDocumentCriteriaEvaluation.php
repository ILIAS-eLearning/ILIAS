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

/**
 * Interface ilTermsOfServiceDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentCriteriaEvaluation
{
    /**
     * Evaluates a document for the context given by the concrete implementation
     */
    public function evaluate(ilTermsOfServiceSignableDocument $document): bool;

    /**
     * Returns a criteria evaluator like this with the passed context user
     */
    public function withContextUser(ilObjUser $user): ilTermsOfServiceDocumentCriteriaEvaluation;
}
