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

namespace ILIAS\LearningModule;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    public function presentation(): Presentation\GUIService
    {
        return new Presentation\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function editing(): Editing\GUIService
    {
        return new Editing\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function blockedUsersTableBuilder(
        int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ): Question\BlockedUsers\TableBuilder {
        return new Question\BlockedUsers\TableBuilder(
            $this->domain_service,
            $ref_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function questionStatisticsTableBuilder(
        int $lm_id,
        object $parent_gui,
        string $parent_cmd
    ): Question\Statistics\TableBuilder {
        return new Question\Statistics\TableBuilder(
            $this->domain_service,
            $lm_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function helpTooltipTableBuilder(
        string $component,
        object $parent_gui,
        string $parent_cmd
    ): HelpTooltip\TableBuilder {
        return new HelpTooltip\TableBuilder(
            $this->domain_service,
            $component,
            $parent_gui,
            $parent_cmd
        );
    }

    public function linksTableBuilder(
        int $lm_id,
        string $lm_type,
        object $parent_gui,
        string $parent_cmd
    ): Links\TableBuilder {
        return new Links\TableBuilder(
            $this->domain_service,
            $this,
            $lm_id,
            $lm_type,
            $parent_gui,
            $parent_cmd
        );
    }
}
