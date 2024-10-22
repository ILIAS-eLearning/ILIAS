<?php

namespace ILIAS\Badge;

use ILIAS\UI\Implementation\Component\Image\Image;
use ILIAS\UI\Component\Modal\Modal;
use ilBadgeAssignment;
use ilLanguage;
use ilDateTime;
use ilDatePresentation;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

class ModalBuilder
{

    private ?Factory $ui_factory;
    private ?Renderer $ui_renderer;
    protected ?ilBadgeAssignment $assignment = null;
    protected ilLanguage $lng;

    public function __construct(ilBadgeAssignment $assignment = null)
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("badge");

        if ($assignment) {
            $this->assignment = $assignment;
        }
    }

    public function constructModal(
        Image $badge_image,
        string $badge_title,
        string $badge_description = null,
        array $badge_properties = []
    ) : Modal
    {
        $modal_content[] = $badge_image;

        if ($this->assignment) {
            $badge_properties['badge_issued_on'] = ilDatePresentation::formatDate(
                new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)
            );
        }

        $badge_properties = $this->translateKeysWithValidDataAttribute($badge_properties);

        $modal_content[] = $this->ui_factory->listing()->descriptive($badge_properties);

        return $this->ui_factory->modal()->roundtrip($badge_title, $modal_content);
    }

    public function renderModal(Modal $modal) : string
    {
        return $this->ui_renderer->render($modal);
    }

    public function renderShyButton(string $label, Modal $modal) : string
    {
        $button = $this->ui_factory->button()->shy($label, $modal->getShowSignal());
        return $this->ui_renderer->render($button);
    }

    private function translateKeysWithValidDataAttribute(array $properties) : array
    {
        $translations = [];

        if (sizeof($properties) > 0) {
            foreach ($properties as $lang_var => $data) {
                if (strlen($data) > 0) {
                    $translations[$this->lng->txt($lang_var)] = $data;
                }
            }
        }
        return $translations;
    }
}