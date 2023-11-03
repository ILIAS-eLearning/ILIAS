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

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SimpleViewManager implements ViewManager
{
    protected InternalDomainService $domain;
    protected DataService $data;
    protected \ilContainer $container;

    public function __construct(
        DataService $data_service,
        InternalDomainService $domain_service,
        \ilContainer $container
    ) {
        $this->data = $data_service;
        $this->domain = $domain_service;
        $this->container = $container;
    }

    public function getBlockSequence(): BlockSequence
    {
        $blocks = [];
        $blocks[] = $this->data->itemGroupBlocks();
        $blocks[] = $this->data->otherBlock();
        return $this->data->blockSequence($blocks);
    }
}
