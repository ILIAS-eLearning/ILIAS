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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestTemplateDeleteAction implements ilCertificateDeleteAction
{
    private ilCertificateDeleteAction $deleteAction;
    private ilCertificateObjectHelper $objectHelper;

    public function __construct(
        ilCertificateDeleteAction $deleteAction,
        ilCertificateObjectHelper $objectHelper
    ) {
        $this->deleteAction = $deleteAction;
        $this->objectHelper = $objectHelper;
    }

    public function delete(int $templateId, int $objectId): void
    {
        $this->deleteAction->delete($templateId, $objectId);
    }
}
