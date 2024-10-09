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

use DateTime;
use DateTimeImmutable;
use Exception;
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
         * @var array{certificate_id: null|string, issue_date: null|DateTime, object: null|string, owner: null|string} $filter_data
         */
        $ui_filter_data = $this->ui_service->filter()->getData($this->filter);
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        if (isset($ui_filter_data['issue_date']) && $ui_filter_data['issue_date'] !== '') {
            try {
                $ui_filter_data['issue_date'] = new DateTime($ui_filter_data['issue_date']);
            } catch (Exception) {
                $ui_filter_data['issue_date'] = null;
            }
        } else {
            $ui_filter_data['issue_date'] = null;
        }

        $table_rows = $this->buildTableRows($this->repo->fetchCertificatesForOverview(
            $this->user->getLanguage(),
            $ui_filter_data,
            $range,
            $order_field,
            $order_direction
        ));

        foreach ($table_rows as $row) {
            $row['issue_date'] = DateTimeImmutable::createFromMutable((new DateTime())->setTimestamp($row['issue_date']));
            yield $row_builder->buildDataRow((string) $row['id'], $row);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        /**
         * @var array{certificate_id: null|string, issue_date: null|DateTime, object: null|string, owner: null|string} $filter_data
         */
        $ui_filter_data = $this->ui_service->filter()->getData($this->filter);

        if (isset($ui_filter_data['issue_date']) && $ui_filter_data['issue_date'] !== '') {
            try {
                $ui_filter_data['issue_date'] = new DateTime($ui_filter_data['issue_date']);
            } catch (Exception) {
                $ui_filter_data['issue_date'] = null;
            }
        } else {
            $ui_filter_data['issue_date'] = null;
        }

        return $this->repo->fetchCertificatesForOverviewCount($ui_filter_data);
    }


    private function buildFilter(): \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
        return $this->ui_service->filter()->standard(
            'certificates_overview_filter',
            $this->ctrl->getLinkTargetByClass(
                ilObjCertificateSettingsGUI::class,
                ilObjCertificateSettingsGUI::CMD_CERTIFICATES_OVERVIEW
            ),
            [
                'certificate_id' => $this->ui_factory->input()->field()->text($this->lng->txt('certificate_id')),
                'issue_date' => $this->ui_factory->input()->field()->text($this->lng->txt('certificate_issue_date')),
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
            $this->lng->txt('certificates'),
            [
                'certificate_id' => $ui_table->column()->text($this->lng->txt('certificate_id')),
                'issue_date' => $ui_table->column()->date($this->lng->txt('certificate_issue_date'), $date_format),
                'object' => $ui_table->column()->text($this->lng->txt('obj')),
                'obj_id' => $ui_table->column()->text($this->lng->txt('object_id')),
                'owner' => $ui_table->column()->text($this->lng->txt('owner'))
            ],
            $this
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

        foreach ($certificates as $certificate) {
            $refIds = ilObject::_getAllReferences($certificate->getObjId());
            $objectTitle = ilObject::_lookupTitle($certificate->getObjId());
            foreach ($refIds as $refId) {
                if ($this->access->checkAccess('read', '', $refId)) {
                    $objectTitle = $this->ui_renderer->render(
                        $this->ui_factory->link()->standard($objectTitle, ilLink::_getLink($refId))
                    );
                    break;
                }
            }

            $table_rows[] = [
                'id' => $certificate->getId(),
                'certificate_id' => $certificate->getCertificateId()->asString(),
                'issue_date' => $certificate->getAcquiredTimestamp(),
                'object' => $objectTitle,
                'obj_id' => (string) $certificate->getObjId(),
                'owner' => ilObjUser::_lookupLogin($certificate->getUserId()),
            ];
        }

        return $table_rows;
    }

    public function render(): string
    {
        return $this->ui_renderer->render([$this->filter, $this->table]);
    }
}
