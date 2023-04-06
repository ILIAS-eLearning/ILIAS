<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeRenderer
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeRenderer
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $renderer;

    protected $assignment; // [ilBadgeAssignment]
    protected $badge; // [ilBadge]
    
    public function __construct(ilBadgeAssignment $a_assignment = null, ilBadge $a_badge = null)
    {
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
    
    public function getHTML()
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
    
    public function renderModalContent()
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
            if ($parent["type"] != "bdga") {
                $parent_icon = $this->factory->symbol()->icon()->custom(
                    ilObject::_getIcon($parent["id"], "big", $parent["type"]),
                    $lng->txt("obj_" . $parent["type"])
                )->withSize("medium");

                $label = $parent['title'];
                $ref = current(ilObject::_getAllReferences($parent['id']));
                if ($ref) {
                    $label = $this->factory->link()->standard($label, ilLink::_getLink($ref, $parent['type']));
                    $label = $this->renderer->render($label);
                }
                $badge_information[$lng->txt("object")] = $this->renderer->render($parent_icon) . $label;
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
