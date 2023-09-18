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

namespace ILIAS\MetaData\Editor\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Modal\RoundTrip as RoundtripModal;

class RequestForForm implements RequestForFormInterface
{
    protected Request $request;
    protected ?PathInterface $path;

    public function __construct(
        Request $request,
        ?PathInterface $path = null
    ) {
        $this->request = $request;
        $this->path = $path;
    }

    public function path(): ?PathInterface
    {
        return $this->path;
    }

    public function applyRequestToForm(StandardForm $form): StandardForm
    {
        return $form->withRequest($this->request);
    }

    public function applyRequestToModal(RoundtripModal $modal): RoundtripModal
    {
        return $modal->withRequest($this->request);
    }
}
