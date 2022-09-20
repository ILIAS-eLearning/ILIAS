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

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeRenderer
{
    protected ilLanguage $lng;
    protected \ILIAS\UI\Factory $factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected ?ilBadgeAssignment $assignment = null;
    protected ?ilBadge $badge = null;

    public function __construct(
        ilBadgeAssignment $a_assignment = null,
        ilBadge $a_badge = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        if ($a_assignment) {
            $this->assignment = $a_assignment;
            $this->badge = new ilBadge($this->assignment->getBadgeId());
        } else {
            $this->badge = $a_badge;
        }
    }

    public function getHTML(): string
    {
        $components = array();

        $modal = $this->factory->modal()->roundtrip(
            $this->badge->getTitle(),
            $this->factory->legacy($this->renderModalContent())
        )->withCancelButtonLabel("ok");
        $components[] = $modal;

        $image_path = ilWACSignedPath::signFile($this->badge->getImagePath());
        $image = $this->factory->image()->responsive($image_path, $this->badge->getTitle())
            ->withAction($modal->getShowSignal());
        $components[] = $image;

        return $this->renderer->render($components);
    }

    public function renderModalContent(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("badge");

        $modal_content = array();

        $image = $this->factory->image()->responsive($this->badge->getImagePath(), $this->badge->getImage());
        $modal_content[] = $image;

        $badge_information = [
            $lng->txt("description") => $this->badge->getDescription(),
            $lng->txt("badge_criteria") => $this->badge->getCriteria(),
        ];

        if ($this->assignment) {
            $badge_information[$lng->txt("badge_issued_on")] = ilDatePresentation::formatDate(
                new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)
            );
        }

        if ($this->badge->getParentId()) {
            $parent = $this->badge->getParentMeta();
            if ($parent["type"] !== "bdga") {
                $parent_icon = $this->factory->symbol()->icon()->custom(
                    ilObject::_getIcon((int) $parent["id"], "big", $parent["type"]),
                    $lng->txt("obj_" . $parent["type"])
                )->withSize("medium");

                $parent_icon_with_text = $this->factory->legacy($this->renderer->render($parent_icon) . $parent["title"]);
                $badge_information[$lng->txt("object")] = $parent_icon_with_text;
            }
        }

        if ($this->badge->getValid()) {
            $badge_information[$lng->txt("badge_valid")] = $this->badge->getValid();
        }

        $list = $this->factory->listing()->descriptive($badge_information);
        $modal_content[] = $list;

        return $this->renderer->render($modal_content);
    }
}
