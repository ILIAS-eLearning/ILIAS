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
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ProfileGUIRequest
{
    private WrapperFactory $wrapper;
    /**
     * @var array<string>
     */
    private array $post;

    public function __construct(
        RequestServices $request,
        private Refinery $refinery
    ) {
        $this->wrapper = $request->wrapper();
        $this->post = $request->request()->getParsedBody();
    }

    public function getUserId(): int
    {
        $user_id = $this->int('user_id');
        if ($user_id !== 0) {
            return $this->int('user_id');
        }
        return $this->int('user');
    }

    public function getBackUrl(): string
    {
        return $this->str('back_url');
    }

    public function getBaseClass(): string
    {
        return $this->str('baseClass');
    }

    public function getPrompted(): int
    {
        return $this->int('prompted');
    }

    public function getOsdId(): int
    {
        return $this->int('osd_id');
    }

    public function getFieldId(): string
    {
        return $this->str('f');
    }

    public function getTerm(): string
    {
        return $this->str('term');
    }

    public function getToken(): string
    {
        return $this->str('token');
    }

    public function getUserFileCapture(): string
    {
        $capture = $this->str('userfile_capture');

        if ($capture !== '') {
            return $capture;
        }

        return $this->str('user_picture_carry');
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

    private function str(string $key): string
    {
        $source = $this->existsInPostOrQuery($key);
        if ($source === '') {
            return '';
        }

        $transformation = $this->refinery->kindlyTo()->string();
        return $this->getFromQueryOrPost($key, $transformation, $source);
    }

    /**
     * @todo 2023-06-05 sk: This is not what we want, but in order to avoid
     * having a RequestInterface and a ProfileGUIRequest as class attributes
     */
    public function getParsedBody(): array
    {
        return $this->post;
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

    private function getFromQueryOrPost(string $key, Transformation $transformation, string $source): string|int
    {
        if ($source === 'query') {
            return $this->wrapper->query()->retrieve($key, $transformation);
        }

        return $this->wrapper->post()->retrieve($key, $transformation);
    }
}
