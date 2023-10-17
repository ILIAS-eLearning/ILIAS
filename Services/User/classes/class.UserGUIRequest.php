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

namespace ILIAS\User;

use ILIAS\HTTP\Services as RequestServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\RequestInterface;
use ILIAS\HTTP\Wrapper\WrapperFactory;

class UserGUIRequest
{
    private WrapperFactory $wrapper;
    private RequestInterface $request;

    public function __construct(
        RequestServices $request,
        private Refinery $refinery
    ) {
        $this->wrapper = $request->wrapper();
        $this->request = $request->request();
    }

    public function getRefId(): int
    {
        return $this->int('ref_id');
    }

    public function getLetter(): string
    {
        return $this->str('letter');
    }

    public function getBaseClass(): string
    {
        return $this->str('baseClass');
    }

    public function getSearch(): string
    {
        return $this->str('search');
    }

    public function getJumpToUser(): int
    {
        return $this->int('jmpToUser');
    }

    public function getFieldId(): int
    {
        return $this->int('field_id');
    }

    public function getFetchAll(): bool
    {
        return (bool) $this->int('fetchall');
    }

    public function getTerm(): string
    {
        return $this->str('term');
    }

    public function getStartingPointId(): ?int
    {
        if (!$this->wrapper->query()->has('spid') &&
            !$this->wrapper->query()->has('start_point_id')) {
            return null;
        }

        $id = $this->int('spid');
        if ($id !== 0) {
            return $id;
        }
        return $this->int('start_point_id');
    }

    public function getRoleId(): int
    {
        $role_id = $this->int('rolid');
        if ($role_id !== 0) {
            return $role_id;
        }
        return $this->int('role_id');
    }

    public function getActionActive(): array
    {
        return $this->intArray('active');
    }

    public function getIds(): array
    {
        return $this->intArray('id');
    }

    public function getChecked(): array
    {
        return $this->intArray('chb');
    }

    public function getFieldType(): int
    {
        return $this->int('field_type');
    }

    public function getFields(): array
    {
        return $this->intArray('fields');
    }

    public function getSelectedAction(): string
    {
        return $this->str('selectedAction');
    }

    public function getFrSearch(): bool
    {
        return $this->bool('frsrch');
    }

    public function getSelect(): array
    {
        return $this->strArray('select');
    }

    public function getFiles(): array
    {
        return $this->strArray('file');
    }

    public function getExportType(): string
    {
        return $this->str('export_type');
    }

    public function getMailSalutation(string $gender, string $lang): string
    {
        return $this->str('sal_' . $gender . '_' . $lang);
    }

    public function getMailSubject(string $lang): string
    {
        return $this->str('subject_' . $lang);
    }

    public function getMailBody(string $lang): string
    {
        return $this->str('body_' . $lang);
    }

    public function getMailAttDelete(string $lang): bool
    {
        return (bool) $this->int('att_' . $lang . '_delete');
    }

    public function getSelectAll(): bool
    {
        return (bool) $this->int('select_cmd_all');
    }

    public function getRoleIds(): array
    {
        return $this->intArray('role_id');
    }

    public function getPostedRoleIds(): array
    {
        return $this->intArray('role_id_ctrl');
    }

    public function getFilteredRoles(): int
    {
        return $this->int('filter');
    }

    public function getSendMail(): string
    {
        return $this->str('send_mail');
    }

    public function getPassword(): string
    {
        return $this->str('passwd');
    }

    public function getUDFs(): array
    {
        return $this->strArray('udf');
    }

    public function getPositions(): array
    {
        return $this->intArray('position');
    }

    public function getCurrentPassword(): string
    {
        return $this->str('current_password');
    }

    public function getNewPassword(): string
    {
        return $this->str('new_password');
    }

    private function int(string $key): int
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return 0;
        }

        $transformation = $this->refinery->kindlyTo()->int();
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }

    private function bool(string $key): bool
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return false;
        }

        $transformation = $this->refinery->kindlyTo()->bool();
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }

    private function str(string $key): string
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return '';
        }

        $transformation = $this->refinery->kindlyTo()->string();
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }

    protected function strArray(string $key): array
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return [];
        }

        $transformation = $this->refinery->custom()->transformation(
            function ($arr) {
                return array_column(
                    array_map(
                        static function ($k, $v): array {
                            if (is_array($v)) {
                                $v = '';
                            }
                            return [$k, \ilUtil::stripSlashes((string) $v)];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }

    protected function intArray(string $key): array
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return [];
        }

        $transformation = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to int
                return array_column(
                    array_map(
                        static function ($k, $v): array {
                            return [$k, (int) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }


    /**
     * @todo 2023-06-05 sk: This is not what we want, but in order to avoid
     * having a RequestInterface and a ProfileGUIRequest as class attributes
     */
    public function getParsedBody(): array
    {
        return $this->request->getParsedBody();
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function isPost(): bool
    {
        return $this->request->getMethod() === 'POST';
    }

    /**
     * @todo 2023-06-05 sk: This is ugly and has to go, but right now, I have
     * no idea, when I want to have information from $_POST or from $_GET.
     */
    private function existsInPostOrQuery(string $key): string
    {
        if ($this->wrapper->post()->has($key)) {
            return 'post';
        }

        if ($this->wrapper->query()->has($key)) {
            return 'query';
        }

        return '';
    }

    private function getFromQueryOrPost(string $key, Transformation $transformation, string $source): string|int|array
    {
        if ($source === 'query') {
            return $this->wrapper->query()->retrieve($key, $transformation);
        }

        return $this->wrapper->post()->retrieve($key, $transformation);
    }
}
