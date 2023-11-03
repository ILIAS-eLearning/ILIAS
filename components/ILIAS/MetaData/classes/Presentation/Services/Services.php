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

namespace ILIAS\MetaData\Presentation\Services;

use ILIAS\MetaData\Presentation\UtilitiesInterface;
use ILIAS\MetaData\Presentation\DataInterface;
use ILIAS\MetaData\Presentation\ElementsInterface;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Presentation\Utilities;
use ILIAS\MetaData\Presentation\Data;
use ILIAS\MetaData\Presentation\Elements;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;

class Services
{
    protected UtilitiesInterface $utilities;
    protected DataInterface $data;
    protected ElementsInterface $elements;

    protected GlobalContainer $dic;
    protected DataHelperServices $data_helper_services;

    public function __construct(
        GlobalContainer $dic,
        DataHelperServices $data_helper_services
    ) {
        $this->dic = $dic;
        $this->data_helper_services = $data_helper_services;
    }

    public function utilities(): UtilitiesInterface
    {
        if (isset($this->utilities)) {
            return $this->utilities;
        }
        return $this->utilities = new Utilities(
            $this->dic->language(),
            $this->dic->user()
        );
    }

    public function data(): DataInterface
    {
        if (isset($this->data)) {
            return $this->data;
        }
        return $this->data = new Data(
            $this->utilities(),
            $this->data_helper_services->dataHelper()
        );
    }

    public function elements(): ElementsInterface
    {
        if (isset($this->elements)) {
            return $this->elements;
        }
        return $this->elements = new Elements(
            $this->utilities()
        );
    }
}
