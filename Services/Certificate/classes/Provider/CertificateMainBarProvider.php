<?php declare(strict_types=1);

namespace ILIAS\Certificate\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class CertificateMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CertificateMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems() : array
    {
        return [];
    }

    public function getStaticSubItems() : array
    {
        global $DIC;

        $title = $this->dic->language()->txt("mm_certificates");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("cert", $title)->withIsOutlined(true);

        $ctrl = $DIC->ctrl();
        return [
            $this->mainmenu
                ->link($this->if->identifier('mm_cert'))
                ->withTitle($title)
                ->withAction(
                    $ctrl->getLinkTargetByClass(
                        [
                            \ilDashboardGUI::class,
                            \ilAchievementsGUI::class,
                            \ilUserCertificateGUI::class
                        ]
                    )
                )
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withSymbol($icon)
                ->withPosition(50),
        ];
    }
}
