<?php


use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Implementation\Component\Link\Bulky as BulkyLink;

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

        $ilUser = $DIC->user();

        $awrn_set = new ilSetting("awrn");
        if (!$awrn_set->get("awrn_enabled", false) || ANONYMOUS_USER_ID == $ilUser->getId() || $ilUser->getId() == 0) {
            return [];
        }

        $cache_period = (int) $awrn_set->get("caching_period");
        $last_update = ilSession::get("awrn_last_update");
        $now = time();

        $act = ilAwarenessAct::getInstance($ilUser->getId());
        $act->setRefId((int) $_GET["ref_id"]);
        if ($last_update == "" || ($now - $last_update) >= $cache_period) {
            $cnt = explode(":", $act->getAwarenessUserCounter());
            $hcnt = $cnt[1];
            $cnt = $cnt[0];
            $act->notifyOnNewOnlineContacts();
            ilSession::set("awrn_last_update", $now);
            ilSession::set("awrn_nr_users", $cnt);
            ilSession::set("awrn_nr_husers", $hcnt);
        } else {
            $cnt = (int) ilSession::get("awrn_nr_users");
            $hcnt = (int) ilSession::get("awrn_nr_husers");
        }


        $gui = new ilAwarenessGUI();
        $result = $gui->getAwarenessList(true);

        $content = function () use ($result) {
            //return $this->dic->ui()->factory()->legacy("<div id='awareness-content'></div>");
            return $this->dic->ui()->factory()->legacy($result["html"]);
        };

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $online = explode(":", $result["cnt"]);
        $online = $online[0];

        $item = $mb
            ->topLegacyItem($this->getId())
            ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) : ILIAS\UI\Component\Component {
                if ($c instanceof BulkyButton || $c instanceof BulkyLink) {
                    return $c->withAdditionalOnLoadCode(static function (string $id) : string {
                        return "$('#$id').on('click', function() {
                                    console.log('trigger awareness slate');
                                })";
                    });
                }
                return $c;
            })
            ->withLegacyContent($content())
            ->withSymbol(
                $this->dic->ui()->factory()
                ->symbol()
                ->glyph()
                ->user()
                ->withCounter($f->counter()->status((int) $cnt))
                ->withCounter($f->counter()->novelty((int) $hcnt))
            )
            ->withTitle($this->dic->language()->txt("awra"))
            ->withPosition(2)
            ->withAvailableCallable(
                function () use ($DIC, $online) {
                    $ilUser = $DIC->user();

                    $awrn_set = new ilSetting("awrn");
                    if ($online <= 0 || !$awrn_set->get("awrn_enabled", false) || ANONYMOUS_USER_ID == $ilUser->getId()) {
                        return false;
                    }
                    return true;
                }
            );

        return [$item];
    }
}
