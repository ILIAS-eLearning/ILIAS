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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Responses;

enum Error: string
{
    case BAD_ARGUMENT = 'badArgument';
    case BAD_RESUMTPION_TOKEN = 'badResumptionToken';
    case BAD_VERB = 'badVerb';
    case CANNOT_DISSEMINATE_FORMAT = 'cannotDisseminateFormat';
    case ID_DOES_NOT_EXIST = 'idDoesNotExist';
    case NO_RECORDS_MATCH = 'noRecordsMatch';
    case NO_MD_FORMATS = 'noMetadataFormats';
    case NO_SET_HIERARCHY = 'noSetHierarchy';
}
