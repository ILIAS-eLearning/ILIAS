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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ilNonEditableValueGUI;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\LegalDocuments\DefaultMappings;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\ILIASSettingStore;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\UserPreferenceStore;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\ReadOnlyStore;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\SessionStore;
use ilInitialisation;
use Closure;
use ilObjUser;
use ilDateTime;
use ilDatePresentation;
use DateTimeImmutable;
use ILIAS\Data\Factory as DataFactory;
use ilStartUpGUI;

class Blocks
{
    /** @var Closure(DateTimeImmutable): string */
    private readonly Closure $format_date;

    /**
     * @param null|Closure(DateTimeImmutable): string $format_date
     */
    public function __construct(
        private readonly string $id,
        private readonly Container $container,
        private readonly Provide $provide,
        ?Closure $format_date = null
    ) {
        $this->format_date = $format_date ?? fn(DateTimeImmutable $date): string => ilDatePresentation::formatDate(
            new ilDateTime($date->getTimestamp(), IL_CAL_UNIX)
        );
    }

    public function slot(): Slot
    {
        return new Slot(
            $this->id,
            $this,
            $this->provide,
            $this->container
        );
    }

    public function defaultMappings(): DefaultMappings
    {
        return new DefaultMappings($this->id, $this->container);
    }

    public function marshal(): Marshal
    {
        return new Marshal($this->container->refinery());
    }

    public function selectSettingsFrom(KeyValueStore $store): SelectSetting
    {
        return new SelectSetting($store, $this->marshal());
    }

    public function readOnlyStore(KeyValueStore $store): KeyValueStore
    {
        return new ReadOnlyStore($store);
    }

    public function globalStore(): KeyValueStore
    {
        return new ILIASSettingStore($this->container->settings());
    }

    public function userStore(ilObjUser $user): KeyValueStore
    {
        return new UserPreferenceStore($user);
    }

    public function sessionStore(): KeyValueStore
    {
        return new SessionStore();
    }

    public function ui(): UI
    {
        return new UI($this->id, $this->container->ui()->factory(), $this->container->ui()->mainTemplate(), $this->container->language());
    }

    public function user(Settings $global_settings, UserSettings $user_settings, ilObjUser $user): User
    {
        return new User($user, $global_settings, $user_settings, $this->provide, (new DataFactory())->clock()->system());
    }

    public function routing(): Routing
    {
        return new Routing(
            $this->container->ctrl(),
            $this->selectSettingsFrom($this->sessionStore()),
            ilInitialisation::redirectToStartingPage(...),
            ilStartUpGUI::logoutUrl(...)
        );
    }

    /**
     * @param Closure(Refinery): Transformation $select
     */
    public function retrieveQueryParameter(string $key, Closure $select)
    {
        return $this->container->http()->wrapper()->query()->retrieve($key, $select($this->container->refinery()));
    }

    public function userManagementAgreeDateField(callable $build_user, string $lang_key, ?string $module = null): Closure
    {
        return function (ilObjUser $user) use ($build_user, $lang_key, $module) {
            if ($module) {
                $this->container->language()->loadLanguageModule($module);
            }
            $this->container->language()->loadLanguageModule('ldoc');
            $user = $build_user($user);

            $value = $user->acceptedVersion()->map(function (DocumentContent $content) use ($lang_key, $user): ilNonEditableValueGUI {
                $input = new ilNonEditableValueGUI($this->ui()->txt($lang_key), $lang_key);
                $input->setValue($this->formatDate($user->agreeDate()->value()));
                $modal = $this->ui()->create()->modal()->lightbox([
                    $this->ui()->create()->modal()->lightboxTextPage($content->value(), $content->title())
                ]);

                $titleLink = $this->ui()->create()->button()->shy($content->title(), '#')->withOnClick($modal->getShowSignal());
                $sub = new ilNonEditableValueGUI($this->ui()->txt('agreement_document'), '', true);
                $sub->setValue($this->container->ui()->renderer()->render([$titleLink, $modal]));
                $input->addSubItem($sub);

                return $input;
            })->except(fn() => new Ok($this->ui()->txt('never')))->value();

            return [$lang_key => $value];
        };
    }

    /**
     * @param Closure(array): void $then
     */
    public function withRequest(Form $form, Closure $then): Form
    {
        if ($this->container->http()->request()->getMethod() !== 'POST') {
            return $form;
        }

        $form = $form->withRequest($this->container->http()->request());
        $data = $form->getData();

        if ($data) {
            $then($data);
        }

        return $form;
    }

    private function formatDate(?DateTimeImmutable $date): string
    {
        return $date ? ($this->format_date)($date) : '';
    }
}
