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

namespace ILIAS\WOPI\Embed;

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
        $tpl->setVariable('TOKEN', $this->embedded_application->getToken());
        $tpl->setVariable('TTL', (string) (time() + $this->embedded_application->getTTL()) * 1000); // in milliseconds

        return $this->ui_factory->legacy()->content($tpl->get());
    }

    /**
     * Markup for a preview of the file, e.g. inside a modal. It is turned into a viewer by
     * the component of getPreviewInitialisation(), which must be rendered along with it.
     */
    public function getPreviewMarkup(string $id, string $title): string
    {
        $tpl = new \ilTemplate('tpl.wopi_preview.html', true, true, 'components/ILIAS/WOPI');
        $tpl->setVariable('ID', htmlspecialchars($id, ENT_QUOTES));
        $tpl->setVariable(
            'EDITOR_URL',
            htmlspecialchars((string) $this->embedded_application->getActionLauncherURL(), ENT_QUOTES)
        );
        $tpl->setVariable('TOKEN', htmlspecialchars($this->embedded_application->getToken(), ENT_QUOTES));
        $tpl->setVariable('TTL', (string) ((time() + $this->embedded_application->getTTL()) * 1000)); // in milliseconds
        $tpl->setVariable('TITLE', htmlspecialchars($title, ENT_QUOTES));

        return $tpl->get();
    }

    public function getPreviewInitialisation(string $preview_id): Component
    {
        $code = 'il.WOPI.preview(' . json_encode($preview_id, JSON_THROW_ON_ERROR) . ');';

        // the id of the bound component is not used, the preview has an id of its own
        return $this->ui_factory->legacy()->content('')->withOnLoadCode(
            static fn($id): string => $code
        );
    }
}
