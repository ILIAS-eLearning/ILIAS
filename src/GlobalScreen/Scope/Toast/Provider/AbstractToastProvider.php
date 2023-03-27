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

namespace ILIAS\GlobalScreen\Scope\Toast\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Toast\Toast;
use ILIAS\UI\Implementation\Component\Toast\Toast as IToast;
use ILIAS\GlobalScreen\Scope\Toast\Factory\ToastFactory;

abstract class AbstractToastProvider extends AbstractProvider implements ToastProvider
{
    protected Container $dic;
    protected \ILIAS\UI\Factory $ui_factory;
    protected IdentificationProviderInterface $if;
    protected ToastFactory $toast_factory;

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->toast_factory = $this->globalScreen()->toasts()->factory();
        $this->ui_factory = $this->dic->ui()->factory();
        $this->if = $this->globalScreen()->identification()->core($this);
    }
}
