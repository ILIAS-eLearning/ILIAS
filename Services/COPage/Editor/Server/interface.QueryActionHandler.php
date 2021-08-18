<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

/**
 * Query action handler interface
 * @author Alexander Killing <killing@leifos.de>
 */
interface QueryActionHandler
{
    /**
     * @param $query
     * @return Response
     */
    public function handle($query) : Response;
}
