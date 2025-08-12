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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Table\Action\Action;
use Psr\Http\Message\ServerRequestInterface;

interface TableAction
{
    public function getActionId(): string;

    public function buildTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action;

    /**
     * @param array<PersonalSettingsTemplate> $selected_templates
     */
    public function buildModal(URLBuilder $url_builder, array $selected_templates): ?Modal;

    /**
     * @param array<PersonalSettingsTemplate> $selected_templates
     */
    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_templates,
    ): ?Modal;
}
