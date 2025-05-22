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

namespace ILIAS\Calendar\URL;

use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\Handler as Handler;
use ILIAS\StaticURL\Handler\BaseHandler as BaseHandler;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Services;

class CalendarStaticURLHandler extends BaseHandler implements Handler
{
    private const CALENDAR_NAMESPACE = 'calendar';

    private const BOOKINGS = 'bookings';


    public function buildConsultationHoursURI(): URI
    {
        global $DIC;
        /** @var Services $static_url */
        $static_url = $DIC['static_url'];
        return $static_url->builder()->build(
            self::CALENDAR_NAMESPACE,
            null,
            [
                self::BOOKINGS
            ]
        );
    }

    public function getNamespace(): string
    {
        return self::CALENDAR_NAMESPACE;
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $additional_params = $request->getAdditionalParameters()[0] ?? '';
        $uri = match ($additional_params) {
            self::BOOKINGS => $context->ctrl()->getLinkTargetByClass(
                [
                    \ilDashboardGUI::class,
                    \ilCalendarPresentationGUI::class,
                    \ilConsultationHoursGUI::class
                ],
                'appointments'
            ),
            default => $context->ctrl()->getLinkTargetByClass(
                [
                     \ilDashboardGUI::class
                 ],
                ''
            )
        };
        return $response_factory->can($uri);
    }
}
