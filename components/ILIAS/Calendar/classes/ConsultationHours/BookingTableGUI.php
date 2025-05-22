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

namespace ILIAS\Calendar\ConsultationHours;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use Generator;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table\Data as Data;
use Psr\Http\Message\ServerRequestInterface as ServerRequestInterface;
use ILIAS\UI\Renderer as Renderer;
use ilLanguage;
use ilObjUser;
use ilCtrl;
use ilConsultationHoursGUI;
use ILIAS\UI\URLBuilder;

class BookingTableGUI implements DataRetrieval
{
    public const ACTION_TOKEN = 'action';
    public const ID_TOKEN = 'id';
    public const TABLE_NS = 'ch_booking_table';

    public const ACTION_TOKEN_NS = self::TABLE_NS . '_' . self::ACTION_TOKEN;

    public const ID_TOKEN_NS = self::TABLE_NS . '_' . self::ID_TOKEN;


    protected BookingDataProvider $provider;

    protected readonly ilLanguage $lng;
    protected readonly ilObjUser $user;
    protected readonly ilCtrl $ctrl;
    protected readonly UIFactory $ui_factory;
    protected readonly DataFactory $data_factory;
    protected readonly ServerRequestInterface $http_request;
    protected Renderer $ui_renderer;


    public function __construct(BookingDataProvider $provider)
    {
        global $DIC;

        $this->provider = $provider;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();

        $this->http_request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->data_factory = new DataFactory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $records = $this->provider->limitData($range, $order);
        foreach ($records as $row) {
            $id = $row['id'];
            $records_row = $row_builder->buildDataRow($id, $row);
            if (count($row['booking_participant']->getItems()) === 0) {
                $records_row = $records_row
                    ->withDisabledAction('confirmCancelBooking')
                    ->withDisabledAction('confirmDeleteBooking')
                    ->withDisabledAction('sendMail');
            }
            yield $records_row;
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->provider->getData());
    }

    public function get(): Data
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this,
                $this->lng->txt('cal_ch_ch'),
                $this->getColumns(),
            )
            ->withId(self::class)
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }


    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(): array
    {
        $uri_command_handler = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilConsultationHoursGUI::class,
                'handleBookingTableActions'
            )
        );

        [
            $url_builder,
            $action_parameter_token,
            $row_id_token
        ] =
            (new URLBuilder($uri_command_handler))->acquireParameters(
                [self::TABLE_NS],
                self::ACTION_TOKEN,
                self::ID_TOKEN
            );

        return [
            'edit' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token, 'edit'),
                $row_id_token
            ),
            'searchUsersForAppointments' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('cal_ch_assign_participants'),
                $url_builder->withParameter($action_parameter_token, 'searchUsersForAppointments'),
                $row_id_token
            ),
            'confirmCancelBooking' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('cal_ch_cancel_booking'),
                $url_builder->withParameter($action_parameter_token, 'confirmCancelBooking'),
                $row_id_token
            )->withAsync(true),
            'confirmDeleteBooking' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('cal_ch_delete_booking'),
                $url_builder->withParameter($action_parameter_token, 'confirmDeleteBooking'),
                $row_id_token
            )->withAsync(true),
            'confirmDeleteAppointments' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'confirmDeleteAppointments'),
                $row_id_token
            )->withAsync(true),
            'sendMail' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('cal_ch_send_mail'),
                $url_builder->withParameter($action_parameter_token, 'sendMail'),
                $row_id_token
            )
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    protected function getColumns(): array
    {
        if ($this->user->getTimeFormat() === \ilCalendarSettings::TIME_FORMAT_12) {
            $format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
        }

        return [
            'booking_start' => $this->ui_factory
                ->table()
                ->column()
                ->date($this->lng->txt('cal_ch_booking_start'), $format)
                ->withIsSortable(true),
            'booking_duration' => $this->ui_factory
                ->table()
                ->column()
                ->number($this->lng->txt('cal_ch_minutes'))
                ->withIsSortable(true),
            'booking_title' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('title'))
                ->withIsSortable(true),
            'booking_participant' => $this->ui_factory
                    ->table()
                    ->column()
                    ->linkListing($this->lng->txt('cal_ch_booking_participants')),
            'booking_comment' => $this->ui_factory
                ->table()
                ->column()
                ->linkListing($this->lng->txt('cal_ch_booking_col_comments')),
            'booking_location' => $this->ui_factory
                ->table()
                ->column()
                ->linkListing($this->lng->txt('cal_ch_target_object'))
        ];

    }

    public function render(): string
    {
        return $this->ui_renderer->render(
            [
                $this->get()
            ]
        );
    }

}
