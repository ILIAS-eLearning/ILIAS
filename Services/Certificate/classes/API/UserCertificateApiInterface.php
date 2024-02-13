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

declare(strict_types=1);

namespace ILIAS\Certificate\API;

use ilCouldNotFindCertificateTemplate;
use ilCertificateTemplate;
use ILIAS\Certificate\API\Filter\UserDataFilter;
use ilCertificateConsumerNotSupported;
use ILIAS\Certificate\API\Data\UserCertificateDto;
use ilCertificateIssuingObjectNotFound;
use ilCertificateOwnerNotFound;
use ilInvalidCertificateException;

interface UserCertificateApiInterface
{
    /**
     * @param list<class-string> $ilCtrlStack An array of ilCtrl-enabled GUI class names that are used to create the link,
     *                                        if this is an empty array (default) no link will be generated
     * @return array<int, UserCertificateDto>
     */
    public function getUserCertificateData(UserDataFilter $filter, array $ilCtrlStack = []): array;

    public function getUserCertificateDataMaxCount(UserDataFilter $filter): int;

    /**
     * @throws ilCertificateIssuingObjectNotFound
     * @throws ilCertificateOwnerNotFound
     * @throws ilInvalidCertificateException
     */
    public function certificateCriteriaMetForGivenTemplate(int $usr_id, ilCertificateTemplate $template): void;

    /**
     * @throws ilCertificateConsumerNotSupported
     * @throws ilCertificateIssuingObjectNotFound
     * @throws ilCertificateOwnerNotFound
     * @throws ilCouldNotFindCertificateTemplate
     * @throws ilInvalidCertificateException
     */
    public function certificateCriteriaMet(int $usr_id, int $obj_id): void;

    public function isActiveCertificateTemplateAvailableFor(int $obj_id): bool;
}
