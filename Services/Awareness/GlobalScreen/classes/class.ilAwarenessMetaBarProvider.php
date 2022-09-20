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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Implementation\Component\Link\Bulky as BulkyLink;

/**
 * Who-Is-Online meta bar provider
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAwarenessMetaBarProvider extends AbstractStaticMetaBarProvider
{
    private function getId(): IdentificationInterface
    {
        return $this->if->identifier('awareness');
    }

    public function getAllIdentifications(): array
    {
        return [$this->getId()];
    }

    public function getMetaBarItems(): array
    {
        $ilUser = $this->dic->user();
        $ref_id = $this->dic->awareness()
            ->internal()
            ->gui()
            ->standardRequest()
            ->getRefId();
        $gui = $this->dic->awareness()
            ->internal()
            ->gui()
            ->widget();
        $manager = $this->dic->awareness()
            ->internal()
            ->domain()
            ->widget(
                $ilUser->getId(),
                $ref_id
            );

        $is_widget_visible = $manager->isWidgetVisible();

        if (!$is_widget_visible) {
            return [];
        }

        $counter = $manager->processMetaBar();

        $content = function () use ($gui) {
            $result = $gui->getAwarenessList(true);
            return $this->dic->ui()->factory()->legacy($result["html"]);
        };

        $mb = $this->globalScreen()->metaBar();

        $f = $this->dic->ui()->factory();

        $item = $mb
            ->topLegacyItem($this->getId())
            ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c): ILIAS\UI\Component\Component {
                if ($c instanceof BulkyButton || $c instanceof BulkyLink) {
                    return $c->withAdditionalOnLoadCode(static function (string $id): string {
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
                ->withCounter($f->counter()->status($counter->getCount()))
                ->withCounter($f->counter()->novelty($counter->getHighlightCount()))
            )
            ->withTitle($this->dic->language()->txt("awra"))
            ->withPosition(2)
            ->withAvailableCallable(
                function () use ($is_widget_visible) {
                    return $is_widget_visible;
                }
            );

        return [$item];
    }
}
