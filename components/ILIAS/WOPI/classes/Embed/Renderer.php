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

namespace ILIAS\components\WOPI\Embed;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Renderer
{
    private Factory $ui_factory;

    public function __construct(
        private EmbeddedApplication $embedded_application
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }

    public function getComponent(): Component
    {
        $tpl = new \ilTemplate('tpl.wopi_container.html', true, true, 'components/ILIAS/WOPI');
        $tpl->setVariable('EDITOR_URL', (string) $this->embedded_application->getActionLauncherURL());
        $tpl->setVariable('INLINE', (string) (int) $this->embedded_application->isInline());
        $tpl->setVariable('TOKEN', (string) $this->embedded_application->getToken());
        $tpl->setVariable('TTL', (string) (time() + $this->embedded_application->getTTL()) * 1000); // in milliseconds

        return $this->ui_factory->legacy()->content($tpl->get());
    }
}
