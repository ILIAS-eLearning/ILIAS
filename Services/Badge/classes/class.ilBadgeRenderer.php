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

use ILIAS\Badge\Tile;
use ILIAS\UI\Renderer;

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeRenderer
{
    private readonly ilLanguage $lng;
    private readonly Renderer $renderer;
    private readonly Tile $tile;
    private readonly ?ilBadgeAssignment $assignment;
    private readonly ilBadge $badge;

    public function __construct(
        ilBadgeAssignment $assignment = null,
        ilBadge $badge = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->renderer = $DIC->ui()->renderer();
        $this->tile = new Tile($DIC);

        if ($assignment) {
            $this->assignment = $assignment;
            $this->badge = new ilBadge($this->assignment->getBadgeId());
        } else {
            $this->assignment = null;
            $this->badge = $badge;
        }
    }

    public function getHTML(): string
    {
        $this->lng->loadLanguageModule('badge');
        $content = $this->tile->modalContent($this->badge);
        if ($this->assignment) {
            $content = $this->tile->addAssignment($content, $this->assignment);
        }

        return $this->renderer->render($this->tile->asImage($content));
    }
}
