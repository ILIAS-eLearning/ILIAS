<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace LTI;

include_once("./Services/UICore/lib/html-it/IT.php");
include_once("./Services/UICore/lib/html-it/ITX.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");

/**
 * special template class to simplify handling of ITX/PEAR
 * @author     Stefan Schneider <schneider@hrz.uni-marburg.de>
 * @version    $Id$
 */
class ilGlobalTemplate extends \ilGlobalTemplate
{
    public function __construct(
        $file,
        $flag1,
        $flag2,
        $in_module = false,
        $vars = "DEFAULT",
        $plugin = false,
        $a_use_cache = false
    ) {
        parent::__construct(
            $file,
            $flag1,
            $flag2,
            $in_module,
            $vars,
            $plugin,
            $a_use_cache
        );
    }
}
