<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Interface for repository objects to use adv md with subitems
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesAdvancedMetaData
*/
interface ilAdvancedMetaDataSubItems
{
    public static function getAdvMDSubItemTitle($a_obj_id, $a_sub_type, $a_sub_id);
}
