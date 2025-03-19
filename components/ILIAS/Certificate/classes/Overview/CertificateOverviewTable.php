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

namespace ILIAS\Certificate\Overview;

use DateInterval;
use DateTimeImmutable;
use Generator;
use ilAccessHandler;
use ilCalendarSettings;
use ilCtrl;
use ilCtrlInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilLink;
use ilObjCertificateSettingsGUI;
use ilObject;
use ilObjUser;
use ilUIService;
use ilUserCertificate;
use ilUserCertificateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Action\Action;
use Throwable;

class CertificateOverviewTable implements DataRetrieval
{
    private readonly ilUserCertificateRepository $repo;
    private readonly ilUIService $ui_service;
    private readonly Factory $ui_factory;
    private readonly ilLanguage $lng;
    private readonly ServerRequestInterface $request;
    private readonly \ILIAS\Data\Factory $data_factory;
    private readonly ilCtrl|ilCtrlInterface $ctrl;
    private readonly \ILIAS\UI\Component\Input\Container\Filter\Standard $filter;
    private readonly Data $table;
    private readonly Renderer $ui_renderer;
    private readonly ilAccessHandler $access;
    private readonly ilObjUser $user;
    private readonly \DateTimeZone $user_timezone;

    public function __construct(
        ?Factory $ui_factory = null,
        ?ilUserCertificateRepository $repo = null,
        ?ilUIService $ui_service = null,
        ?ilLanguage $lng = null,
        ServerRequestInterface|RequestInterface|null $request = null,
        ?\ILIAS\Data\Factory $data_factory = null,
        ?ilCtrl $ctrl = null,
        ?Renderer $ui_renderer = null,
        ?ilAccessHandler $access = null,
        ?ilObjUser $user = null
    ) {
        global $DIC;
        $this->ui_factory = $ui_factory ?: $DIC->ui()->factory();
        $this->repo = $repo ?: new ilUserCertificateRepository();
        $this->ui_service = $ui_service ?: $DIC->uiService();
        $this->lng = $lng ?: $DIC->language();
        $this->request = $request ?: $DIC->http()->request();
        $this->data_factory = $data_factory ?: new \ILIAS\Data\Factory();
        $this->ctrl = $ctrl ?: $DIC->ctrl();
        $this->ui_renderer = $ui_renderer ?: $DIC->ui()->renderer();
        $this->access = $access ?: $DIC->access();
        $this->user = $user ?: $DIC->user();
        $this->user_timezone = new \DateTimeZone($this->user->getTimeZone());

        $this->filter = $this->buildFilter();
        $this->table = $this->buildTable();
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        /**
         * @var array{certificate_id: null|string, issue_date: null|DateTimeImmutable, object: null|string, owner: null|string} $filter_data
         */
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        $ui_filter_data = $this->mapUiFilterData($this->ui_service->filter()->getData($this->filter));

        $table_rows = $this->buildTableRows($this->repo->fetchCertificatesForOverview(
            $this->user->getLanguage(),
            $ui_filter_data,
            $range,
            $order_field,
            $order_direction
        ));

        foreach ($table_rows as $row) {
            $row['issue_date'] = (new DateTimeImmutable())
                ->setTimestamp($row['issue_date'])
                ->setTimezone($this->user_timezone);
            yield $row_builder->buildDataRow((string) $row['id'], $row);
        }
    }

    /**
     * @param array{certificate_id: null|string, issue_date: string[], object: null|string, owner: null|string} $filter_data
     * @return array{certificate_id: null|string, issue_date: array{from: null|DateTimeImmutable, to: null|DateTimeImmutable}, object: null|string, owner: null|string} $filter_data
     */
    private function mapUiFilterData(array $filter_data): array
    {
        if (isset($filter_data['issue_date']) && $filter_data['issue_date'] !== '') {
            try {
                $from = new DateTimeImmutable($filter_data['issue_date'][0], $this->user_timezone);
            } catch (Throwable) {
                $from = null;
            }

            try {
                $to = new DateTimeImmutable($filter_data['issue_date'][1], $this->user_timezone);
                $seconds_to_add = 59 - (int) $to->format('s');
                $to = $to->modify("+$seconds_to_add seconds");
            } catch (Throwable) {
                $to = null;
            }

            $filter_data['issue_date'] = [
                'from' => $from,
                'to' => $to
            ];
        } else {
            $filter_data['issue_date'] = [
                'from' => null,
                'to' => null
            ];
        }

        return $filter_data;
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $ui_filter_data = $this->mapUiFilterData($this->ui_service->filter()->getData($this->filter));

        return $this->repo->fetchCertificatesForOverviewCount($ui_filter_data);
    }


    private function buildFilter(): \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
        if ((int) $this->user->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
        }

        return $this->ui_service->filter()->standard(
            'certificates_overview_filter',
            $this->ctrl->getLinkTargetByClass(
                ilObjCertificateSettingsGUI::class,
                ilObjCertificateSettingsGUI::CMD_CERTIFICATES_OVERVIEW
            ),
            [
                'certificate_id' => $this->ui_factory->input()->field()->text($this->lng->txt('certificate_id')),
                'issue_date' => $this->ui_factory->input()->field()
                    ->duration($this->lng->txt('certificate_issue_date'))
                    ->withFormat($date_format)
                    ->withUseTime(true),
                'object' => $this->ui_factory->input()->field()->text($this->lng->txt('obj')),
                'obj_id' => $this->ui_factory->input()->field()->text($this->lng->txt('object_id')),
                'owner' => $this->ui_factory->input()->field()->text($this->lng->txt('owner')),
            ],
            [true, true, true, true, true],
            true,
            true
        );
    }

    private function buildTable(): Data
    {
        $ui_table = $this->ui_factory->table();

        if ((int) $this->user->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
        }
        return $ui_table->data(
            $this,
            $this->lng->txt('certificates'),
            [
                'certificate_id' => $ui_table->column()->text($this->lng->txt('certificate_id')),
                'issue_date' => $ui_table->column()->date($this->lng->txt('certificate_issue_date'), $date_format),
                'object' => $ui_table->column()->text($this->lng->txt('obj')),
                'obj_id' => $ui_table->column()->text($this->lng->txt('object_id')),
                'owner' => $ui_table->column()->text($this->lng->txt('owner'))
            ],
        )
            ->withOrder(new Order('issue_date', Order::DESC))
            ->withId('certificateOverviewTable')
            ->withRequest($this->request)
            ->withActions($this->buildTableActions());
    }

    /**
     * @return array<string, Action>
     */
    private function buildTableActions(): array
    {
        $uri_download = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilObjCertificateSettingsGUI::class,
                ilObjCertificateSettingsGUI::CMD_DOWNLOAD_CERTIFICATE
            )
        );

        /**
         * @var URLBuilder $url_builder_download
         * @var URLBuilderToken $action_parameter_token_download ,
         * @var URLBuilderToken $row_id_token_download
         */
        [
            $url_builder_download,
            $action_parameter_token_download,
            $row_id_token_download
        ] =
            (new URLBuilder($uri_download))->acquireParameters(
                ['cert_overview'],
                'action',
                'id'
            );

        return [
            'download' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('download'),
                $url_builder_download->withParameter($action_parameter_token_download, 'download'),
                $row_id_token_download
            )
        ];
    }

    /**
     * @param ilUserCertificate[] $certificates
     * @return list<array{"id": int, "certificate": string, "issue_date": int, "object": string, "obj_id": string, "owner": string}>
     */
    private function buildTableRows(array $certificates): array
    {
        $table_rows = [];

        $ref_id_cache = [];
        $owner_cache = [];
        $object_title_cache = [];

        foreach ($certificates as $certificate) {
            if (!isset($ref_id_cache[$certificate->getObjId()])) {
                $ref_id_cache[$certificate->getObjId()] = ilObject::_getAllReferences($certificate->getObjId());
            }
            $ref_ids = $ref_id_cache[$certificate->getObjId()];

            if (!isset($object_title_cache[$certificate->getObjId()])) {
                $object_title = ilObject::_lookupTitle($certificate->getObjId());
                foreach ($ref_ids as $refId) {
                    if ($this->access->checkAccess('read', '', $refId)) {
                        $object_title = $this->ui_renderer->render(
                            $this->ui_factory->link()->standard($object_title, ilLink::_getLink($refId))
                        );
                        break;
                    }
                }

                $object_title_cache[$certificate->getObjId()] = $object_title;
            }



            if (!isset($owner_cache[$certificate->getUserId()])) {
                $owner_cache[$certificate->getUserId()] = ilObjUser::_lookupLogin($certificate->getUserId());
            }

            $table_rows[] = [
                'id' => $certificate->getId(),
                'certificate_id' => $certificate->getCertificateId()->asString(),
                'issue_date' => $certificate->getAcquiredTimestamp(),
                'object' => $object_title_cache[$certificate->getObjId()],
                'obj_id' => (string) $certificate->getObjId(),
                'owner' => $owner_cache[$certificate->getUserId()],
            ];
        }

        return $table_rows;
    }

    public function render(): string
    {
        return $this->ui_renderer->render([$this->filter, $this->table]);
    }
}
