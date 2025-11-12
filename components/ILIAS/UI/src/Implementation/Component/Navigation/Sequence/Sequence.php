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

namespace ILIAS\UI\Implementation\Component\Navigation\Sequence;

use ILIAS\UI\Component\Navigation\Sequence as ISequence;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl as ViewControlContainer;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use ILIAS\UI\Storage;

class Sequence implements ISequence\Sequence
{
    use ComponentHelper;

    private const PARAM_POSITION = 'p';
    public const STORAGE_ID_PREFIX = self::class . '_';

    protected ?ServerRequestInterface $request = null;
    protected ?URLBuilder $url_builder = null;
    protected ?URLBuilderToken $token_position = null;
    protected ?ViewControlContainer $viewcontrols = null;
    protected ?array $actions = null;
    protected int $position = 0;
    protected ?string $id = null;

    public function __construct(
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected Storage $storage,
        protected ISequence\SegmentRetrieval $segment_retrieval,
        protected ?string $title
    ) {
    }

    public function getSegmentRetrieval(): ISequence\SegmentRetrieval
    {
        return $this->segment_retrieval;
    }

    public function getCurrentPosition(): int
    {
        return $this->position;
    }

    public function withCurrentPosition(int $position): static
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function withViewControls(ViewControlContainer $viewcontrols): static
    {
        $clone = clone $this;
        $clone->viewcontrols = $viewcontrols;
        return $clone;
    }

    public function getViewControls(): ?ViewControlContainer
    {
        return $this->viewcontrols;
    }

    public function withActions(...$actions): static
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    public function getActions(): ?array
    {
        return $this->actions;
    }

    protected function checkRequest(): void
    {
        if (! $this->request) {
            throw new \LogicException('no request was set on the sequence');
        }
    }

    public function withRequest(ServerRequestInterface $request): static
    {
        $clone = clone $this;
        $clone->request = $request;
        $clone->initFromRequest();
        return $clone;
    }

    public function getRequest(): ServerRequestInterface
    {
        $this->checkRequest();
        return $this->request;
    }

    protected function initFromRequest(): void
    {
        $base_uri = $this->data_factory->uri($this->request->getUri()->__toString());
        $namespace = ['sequence_' . $this->getId() ?? ''];
        $url_builder = new URLBuilder($base_uri);
        list(
            $this->url_builder,
            $this->token_position
        ) = $url_builder->acquireParameters(
            $namespace,
            self::PARAM_POSITION
        );

        $query = new \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper($this->request->getQueryParams());
        $this->position = $query->retrieve(
            $this->token_position->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(0)
            ])
        );

        if ($this->viewcontrols) {
            $this->viewcontrols = $this->applyValuesToViewcontrols(
                $this->viewcontrols,
                $this->request
            );
        }
    }

    public function getNext(int $direction): URI
    {
        $this->checkRequest();
        return $this->url_builder
            ->withParameter($this->token_position, (string) ($this->position + $direction))
            ->buildURI();
    }

    protected function getStorageData(): ?array
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            return $this->storage[$storage_id] ?? null;
        }
        return null;
    }

    protected function setStorageData(array $data): void
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            $this->storage[$storage_id] = $data;
        }
    }

    protected function getStorageId(): ?string
    {
        if (null !== ($id = $this->getId())) {
            return static::STORAGE_ID_PREFIX . $id;
        }
        return null;
    }

    public function withId(string $id): static
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    protected function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    protected function applyValuesToViewcontrols(
        ViewControlContainer $view_controls,
        ServerRequestInterface $request
    ): ViewControlContainer {
        $stored_values = new ArrayInputData($this->getStorageData() ?? []);
        $view_controls = $view_controls
            ->withStoredInput($stored_values)
            ->withRequest($request);
        $this->setStorageData($view_controls->getComponentInternalValues());
        return $view_controls;
    }

}
