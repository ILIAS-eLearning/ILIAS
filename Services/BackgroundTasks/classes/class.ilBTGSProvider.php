<?php

use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

/**
 * Class ilBTGSProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTGSProvider extends AbstractStaticMetaBarProvider
{

    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        /**
         * @var $gui ilBTPopOverGUI
         */
        $uid = $this->dic->user()->getId();
        $f = $this->dic->ui()->factory();
        $bg = $this->dic->backgroundTasks();
        $gui = $bg->injector()->createInstance(ilBTPopOverGUI::class);

        $bucket_ids_of_user = $bg->persistence()->getBucketIdsOfUser($uid);
        $amount_of_buckets = is_array($bucket_ids_of_user) ? count($bucket_ids_of_user) : 0;

        $top_legacy_item = $this->globalScreen()->metaBar()->topLegacyItem($this->if->identifier('bgt'))
            ->withLegacyContent($gui->getPopOverContent($uid, "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"))
            ->withGlyph($f->symbol()->glyph()->briefcase()->withCounter($f->counter()->novelty($amount_of_buckets)))
            ->withVisibilityCallable(
                function () use ($amount_of_buckets): bool {
                    return ($amount_of_buckets > 0);
                }
            );

        return [$top_legacy_item];
    }
}
