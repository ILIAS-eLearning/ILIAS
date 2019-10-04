<?php


use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Who-Is-Online meta bar provider
 *
 * @author <killing@leifos.de>
 */
class ilAwarenessMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @return IdentificationInterface
     */
    private function getId() : IdentificationInterface
    {
        return $this->if->identifier('awareness');
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

        $gui = new ilAwarenessGUI();
        $result = $gui->getAwarenessList(true);

        $content = function () use ($result) {
            return $this->dic->ui()->factory()->legacy($result["html"]);
        };

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $online = explode(":", $result["cnt"]);
        $online = $online[0];

        //$icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/people.svg"), "");

        $item = $mb
            ->topLegacyItem($this->getId())
            ->withLegacyContent($content())
            ->withSymbol($this->dic->ui()->factory()
                ->symbol()
                ->glyph()
                ->user()
                ->withCounter($f->counter()->status((int) $online))
            )
            ->withTitle("Who is online")
            ->withPosition(0)
            ->withAvailableCallable(
                function () use ($DIC, $online){
                    $ilUser = $DIC->user();

                    $awrn_set = new ilSetting("awrn");
                    if ($online <= 0 || !$awrn_set->get("awrn_enabled", false) || ANONYMOUS_USER_ID == $ilUser->getId())
                    {
                        return false;
                    }
                    return true;
                }
            );

        return [$item];
    }
}
