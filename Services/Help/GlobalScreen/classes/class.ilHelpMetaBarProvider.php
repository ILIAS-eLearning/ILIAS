<?php


use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Help meta bar provider
 *
 * @author <killing@leifos.de>
 */
class ilHelpMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @return IdentificationInterface
     */
    private function getId() : IdentificationInterface
    {
        return $this->if->identifier('help');
    }


    /**
     * @inheritDoc
     */
    public function getAllIdentifications() : array
    {
        return [$this->getId()];
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $icon = $f->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/info.svg"), "");

        if ($this->showHelpItem()) {
            $item = $mb->topLinkItem($this->getId())
                ->withAction($ctrl->getLinkTargetByClass("ilpersonaldesktopgui", "toggleHelp"))
                ->withSymbol($icon)
                ->withTitle("Help")
                ->withPosition(2)
                ->withAvailableCallable(
                    function () use ($DIC) {
                        return true;
                    }
                );

            return [$item];
        }

        return [];
    }

    /**
     * Show help tool?
     *
     * @param
     * @return
     */
    protected function showHelpItem(): bool
    {
        global $DIC;

        $user = $DIC->user();
        $settings = $DIC->settings();

        if ($user->getLanguage() != "de")
        {
            return false;
        }

        if ($settings->get("help_mode") == "2")
        {
            return false;
        }

        if ((defined("OH_REF_ID") && OH_REF_ID > 0))
        {
            true;
        }
        else
        {
            $module = (int) $settings->get("help_module");
            if ($module == 0)
            {
                return false;
            }
        }
        return true;
    }

}
