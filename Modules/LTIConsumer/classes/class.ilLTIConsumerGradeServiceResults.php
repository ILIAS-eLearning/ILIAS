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
 * Class ilLTIConsumerGradeServiceResults
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeServiceResults extends ilLTIConsumerResourceBase
{
    public function __construct(ilLTIConsumerServiceBase $service)
    {
        parent::__construct($service);
        $this->id = 'Result.collection';
        $this->template = '/{context_id}/lineitems/{item_id}/lineitem/results';
        $this->variables[] = 'Results.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.resultcontainer+json';
        $this->methods[] = 'GET';
    }
}
