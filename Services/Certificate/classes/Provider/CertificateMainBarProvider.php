<?php namespace ILIAS\Certificate\Provider;

use ilCertificateActiveValidator;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class CertificateMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CertificateMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        global $DIC;

        $title = $this->dic->language()->txt("mm_certificates");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("cert", $title)->withIsOutlined(true);

        $ctrl = $DIC->ctrl();
        return [
            $this->mainmenu->link($this->if->identifier('mm_cert'))
                ->withTitle($title)
                ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI",
                    "ilAchievementsGUI","ilUserCertificateGUI"]))
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withVisibilityCallable(
                    static function () : bool {
                        return (
                            (BasicAccessCheckClosures::getInstance()->isUserLoggedIn())() &&
                            (new ilCertificateActiveValidator())->validate()
                        );
                    }
                )
                ->withSymbol($icon)
                ->withPosition(50),
        ];
    }
}
