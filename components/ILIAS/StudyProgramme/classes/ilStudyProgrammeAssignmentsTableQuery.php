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

use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Uri as GuzzlURI;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as RequestWrapper;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;

class ilStudyProgrammeAssignmentsTableQuery
{
    protected ServerRequestInterface $request;
    protected RequestWrapper $request_wrapper;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;

    public function __construct(
        HTTPService $http,
        protected Refinery $refinery,
        protected DataFactory $data_factory,
        array $namespace
    ) {
        $this->request = $http->request();
        $this->request_wrapper = $http->wrapper()->query();

        $url_builder = $this->buildUrlBuilder();
        list($url_builder, $action_token, $row_id_token) = $url_builder->acquireParameters(
            $namespace,
            "action",
            "ids"
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
    }


    private function buildUrlBuilder(): URLBuilder
    {
        $endpoint_url = (new \ILIAS\Data\Factory())->uri(
            GuzzlURI::withQueryValue(
                $this->request->getUri(),
                'cmd',
                ilObjStudyProgrammeMembersGUI::TABLE_COMMAND
            )
            ->__toString()
        );
        return new \ILIAS\UI\URLBuilder($endpoint_url);
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }
    public function getActionToken(): URLBuilderToken
    {
        return $this->action_token;
    }
    public function getRowIdToken(): URLBuilderToken
    {
        return $this->row_id_token;
    }
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }


    //getPostPrgrsIdsFromModal

}
