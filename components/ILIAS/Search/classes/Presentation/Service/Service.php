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

namespace ILIAS\Search\Presentation\Service;

use ILIAS\DI\Container;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ILIAS\Search\Presentation\Result\ResultPresenterImpl;
use ILIAS\Search\Presentation\Result\UI\ComponentFactoryImpl;
use ILIAS\Search\Presentation\Result\Object\PropertiesAggregatorImpl as ObjectPropertiesAggregatorImpl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesAggregatorImpl as SubitemPropertiesAggregatorImpl;
use ILIAS\Search\Presentation\Result\UI\SanitizerImpl;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactoryImpl as SubitemPropertiesFactoryImpl;
use ILIAS\Search\Presentation\Result\Object\AccessCheckerImpl;
use ILIAS\Search\Presentation\Result\Copyright\HelperImpl as CopyrightHelperImpl;

class Service
{
    protected ResultPresenter $result_presenter;

    public function __construct(
        protected Container $dic
    ) {
    }

    public function result(): ResultPresenter
    {
        $lng = $this->dic->language();
        $lng->loadLanguageModule('search');
        $sanitizer = new SanitizerImpl($this->dic->refinery());
        $access_checker = new AccessCheckerImpl($this->dic->access());
        $subitem_properties_factory = new SubitemPropertiesFactoryImpl();
        return $this->result_presenter ??= new ResultPresenterImpl(
            new ComponentFactoryImpl(
                $this->dic->ui()->factory(),
                $lng,
                $sanitizer
            ),
            new ObjectPropertiesAggregatorImpl(
                $access_checker,
                $this->dic['objDefinition'],
                $lng,
                $this->dic['static_url'],
                new DataFactory()
            ),
            new SubitemPropertiesAggregatorImpl(
                $this->dic,
                $subitem_properties_factory
            ),
            $subitem_properties_factory,
            new CopyrightHelperImpl($this->dic->learningObjectMetadata()),
            $access_checker,
            $sanitizer
        );
    }
}
