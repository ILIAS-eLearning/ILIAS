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

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage\Collections\DataProvider\TableDataProvider;
use ILIAS\HTTP\Services;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface RequestToComponents
{
    public function __construct(
        Request $request,
        Factory $ui_factory,
        \ilLanguage $language,
        Services $http,
        TableDataProvider $data_provider,
        ActionBuilder $action_builder,
        ViewControlBuilder $view_control_builder,
        UploadBuilder $upload_builder
    );

    public function getComponents(): \Generator;
}
