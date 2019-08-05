<?php namespace ILIAS\Certificate\Provider;

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
        return [
            $this->mainmenu->link($this->if->identifier('mm_cert'))
                ->withTitle($this->dic->language()->txt("mm_certificates"))
                ->withAction("#")
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
	            ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("cert", "")->withIsOutlined(true))
                ->withPosition(50),
        ];
    }
}
