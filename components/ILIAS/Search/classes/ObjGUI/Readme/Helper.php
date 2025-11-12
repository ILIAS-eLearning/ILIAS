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

namespace ILIAS\Search\ObjGUI\Readme;

use ILIAS\UI\Factory as UIFactory;
use ilCtrlInterface;
use ilLanguage;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ilObjSearchSettingsReadmeGUI;
use ILIAS\UI\Component\Link\Link;

class Helper
{
    public function __construct(
        protected ilCtrlInterface $ctrl,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
    }

    public function getServerErrorMessageBox(string $error): MessageBox
    {
        return $this->ui_factory->messageBox()->failure(
            $error . '<br/>' . $this->lng->txt('search_server_further_information')
        )->withLinks([$this->buildDeliverFileLink()]);
    }

    public function getServerInfoMessageBox(): MessageBox
    {
        return $this->ui_factory->messageBox()->info(
            $this->lng->txt('search_server_further_information')
        )->withLinks([$this->buildDeliverFileLink()]);
    }

    protected function buildDeliverFileLink(): Link
    {
        $view_url = $this->ctrl->getLinkTargetByClass(
            ilObjSearchSettingsReadmeGUI::class,
            'deliverFile'
        );
        return $this->ui_factory->link()->standard(
            $this->lng->txt('search_readme_file'),
            $view_url
        )->withOpenInNewViewport(true);
    }
}
