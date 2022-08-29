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

namespace ILIAS\MetaData\Repository\Dictionary;

enum ExpectedParameter: string
{
    /**
     * Entries in the expected params with this value should
     * be ignored when reading.
     */
    case MD_ID = 'md_id';
    case PARENT_MD_ID = 'parent_md_id';
    case SECOND_PARENT_MD_ID ='second_parent_md_id';
    case SUPER_MD_ID = 'super_md_id';
    case RESSOURCE_IDS = 'ressource_ids';

    /**
     * Entries in the expected params with this value should
     * be ignored when reading or deleting.
     */
    case DATA = 'md_data';
}
