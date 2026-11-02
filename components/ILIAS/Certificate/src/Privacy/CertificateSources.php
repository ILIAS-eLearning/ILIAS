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

namespace ILIAS\Certificate\Privacy;

use ILIAS\Data\Privacy\Source\DbTableColumns;
use ILIAS\Data\Privacy\Source\SourceCatalogue;

/**
 * Catalogue of the personal data columns of the Certificate component.
 */
readonly class CertificateSources implements SourceCatalogue
{
    private const string USER_CERTIFICATE_TABLE = 'il_cert_user_cert';

    /**
     * An issued certificate: the rendered content and the placeholder
     * values it was rendered with. Issued certificates do not change when
     * the personal data they were rendered with changes.
     */
    public function issuedCertificate(): DbTableColumns
    {
        return new DbTableColumns(self::USER_CERTIFICATE_TABLE, 'certificate_content', 'template_values');
    }
}
