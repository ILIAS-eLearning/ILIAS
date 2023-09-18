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

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Toast\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer\ToastRendererFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ToastFactory
{
    private ToastRendererFactory $renderer_factory;

    public function __construct(ToastRendererFactory $renderer_factory)
    {
        $this->renderer_factory = $renderer_factory;
    }

    /**
     * Your Toasts shiuld provide Callables which are executed on some events: onShow, onClose, onVanish.
     * Users, which see a Toast must have an option to close it, otherwise it would pop up every time.
     * Therefore you should provide a callable for onClose ehich persists the close action.
     * onVanish could be used to track if this toast has been presented to the user.
     * All those events are handles asynchronously.
     *
     * Every other Action can be provided as a ToastAction, which is rendered as a link in the Toast.
     * These actions are handled synchronously.
     *
     * @see isStandardItem::withShownCallable()
     * @see isStandardItem::withClosedCallable()
     * @see isStandardItem::withVanishedCallable()
     * @see isStandardItem::withAdditionToastAction()
     */
    public function standard(
        IdentificationInterface $identification,
        string $title,
        ?Icon $icon = null
    ): StandardToastItem {
        return new StandardToastItem(
            $identification,
            $this->renderer_factory->getRenderer(StandardToastItem::class),
            $title,
            $icon
        );
    }

    /**
     * A ToastAction leads into the rendered toast to a link that can be clicked by the user. The user is forwarded to
     * an endpoint of the GlobalScreen, where the corresponding callable is executed. Since this is currently only
     * possible synchronously and not asynchronously, the callable must then forward to a
     * URL with $DIC->ctrl()->redirectToURL('...');
     */
    public function action(string $identifier, string $title, \Closure $action): ToastAction
    {
        return new ToastAction($identifier, $title, $action);
    }
}
