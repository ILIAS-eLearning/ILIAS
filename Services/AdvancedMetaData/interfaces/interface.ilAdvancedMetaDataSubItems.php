<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for repository objects to use adv md with subitems
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
interface ilAdvancedMetaDataSubItems
{
    public static function getAdvMDSubItemTitle(int $a_obj_id, string $a_sub_type, int $a_sub_id): string;
}
