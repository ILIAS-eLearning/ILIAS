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

use ceLTIc\LTI\Content\Item;
use ceLTIc\LTI\Content\LtiLinkItem;
use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Platform;
use ceLTIc\LTI\Tool;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use Random\RandomException;

/**
 * LTI Advantage Deep Linking with ILIAS as tool: an instructor of a platform picks ILIAS objects released to
 * the platform, and each one goes back as a resource link to its target link, lti.php?ref_id=N.
 *
 * celtic/lti checks the Deep Linking request, which arrives at lti.php like a launch (ilLTILaunchReceiver).
 * Nobody is logged in to ILIAS for it: the page with the objects to pick sends them to lti.php again, with a
 * token ILIAS signed that names the platform and the return URL of the request, so no session is needed. The
 * platform usually shows the page in an iframe of its own site, where a session cookie of ILIAS is not sent.
 * Errors of the request go back to the platform, as the library does.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantageToolDeepLinking extends Tool
{
    /**
     * The parameter of lti.php with the token of the page with the objects to pick.
     */
    public const string TOKEN_PARAM = 'lti_deep_linking';

    private const string PURPOSE = 'lti_tool_deep_linking';
    private const int TOKEN_LIFETIME = 3600;
    private const string MESSAGE_RESPONSE = 'ContentItemSelection';
    private const string FIELD_OBJECTS = 'objects';

    public function __construct()
    {
        parent::__construct(new ilLTIDataConnector());
    }

    /**
     * The page with the objects to pick for a Deep Linking request celtic/lti has checked. A request ILIAS
     * cannot answer is refused, and the library tells the platform why.
     *
     * @param array $content_types the types of content the platform accepts, as the library read them from the
     *                             request
     *
     * @throws RandomException
     */
    public static function showSelection(Tool $receiver, array $content_types): void
    {
        global $DIC;

        $parameters = $receiver->getMessageParameters() ?? [];
        $multiple = ($parameters['accept_multiple'] ?? '') === 'true';
        $ref_ids = ilLTIRelease::lookupLaunchableRefIds((int) $receiver->platform?->getRecordId());

        $reason = match (true) {
            $receiver->ltiVersion !== LtiVersion::V1P3 => 'ILIAS only takes Deep Linking requests of LTI Advantage.',
            $receiver->userResult === null || !($receiver->userResult->isStaff() || $receiver->userResult->isAdmin())
                => 'Only instructors and administrators may pick ILIAS content.',
            !in_array(Item::TYPE_LTI_LINK, $content_types, true) => 'The platform does not accept LTI resource links, only: ' . implode(', ', $content_types) . '.',
            $ref_ids === [] => 'No ILIAS object is released to the platform.',
            default => null,
        };
        if ($reason !== null) {
            $receiver->setReason($reason);
            return;
        }

        try {
            $token = ilLTIAdvantageKeyPair::signFor(self::PURPOSE, self::TOKEN_LIFETIME, [
                'platform' => $receiver->platform->getRecordId(),
                'iss' => $receiver->platform->platformId,
                'client_id' => $receiver->platform->clientId,
                'deployment_id' => $receiver->platform->deploymentId,
                'return_url' => (string) ($parameters['content_item_return_url'] ?? ''),
                'data' => $parameters['data'] ?? null,
                'multiple' => $multiple,
            ]);
        } catch (ilException $e) {
            $DIC->logger()->forComponent('lti')->error($e->getMessage());
            $receiver->setReason('ILIAS cannot sign its response.');
            return;
        }

        $DIC->logger()->forComponent('lti')->info('LTI Deep Linking request: {count} objects offered ({request})', [
            'count' => count($ref_ids),
            'request' => $receiver instanceof ilLTILaunchReceiver ? $receiver->describeRequest() : '',
        ]);
        self::printSelection(self::buildForm($token, $ref_ids, $multiple));
        // ILIAS would go on with the login of a launch
        $DIC->http()->close();
    }

    /**
     * Sends the objects the instructor picked back to the platform, from the page showSelection() printed.
     *
     * @throws RandomException
     * @throws ResponseSendingException
     */
    public function respond(string $token): never
    {
        global $DIC;

        $log = $DIC->logger()->forComponent('lti');
        $payload = ilLTIAdvantageKeyPair::verifyFor(self::PURPOSE, $token);
        if (!is_int($payload['platform'] ?? null)) {
            $log->warning('LTI Deep Linking selection refused: the token is invalid or has expired.');
            $this->printError('lti_deep_linking_expired');
        }

        $this->platform = Platform::fromRecordId($payload['platform'], $this->dataConnector);
        $ref_ids = ilLTIRelease::lookupLaunchableRefIds($payload['platform']);
        $form = self::buildForm($token, $ref_ids, (bool) $payload['multiple'])->withRequest($DIC->http()->request());
        $data = $form->getData();
        if ($data === null) {
            self::printSelection($form);
            $DIC->http()->close();
        }

        $items = [];
        foreach (array_intersect(array_map('intval', (array) $data[self::FIELD_OBJECTS]), $ref_ids) as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $item = new LtiLinkItem();
            $item->setTitle(ilObject::_lookupTitle($obj_id));
            $description = ilObject::_lookupDescription($obj_id);
            if ($description !== '') {
                $item->setText($description);
            }
            $item->setUrl(ilLTIEndpoint::getUrl(ilLTIEndpoint::LAUNCH, '?ref_id=' . $ref_id));
            $items[] = $item;
        }

        // the response names ILIAS as the platform knows it, whichever registration of the platform it was sent to
        $this->platform->platformId = (string) $payload['iss'];
        $this->platform->clientId = (string) $payload['client_id'];
        $this->platform->deploymentId = (string) $payload['deployment_id'];
        $this->ltiVersion = LtiVersion::V1P3;
        ilLTIAdvantageKeyPair::applyTo($this);

        $parameters = ['content_items' => Item::toJson($items, LtiVersion::V1P3)];
        if (is_string($payload['data'] ?? null)) {
            $parameters['data'] = $payload['data'];
        }
        $page = $this->sendMessage((string) $payload['return_url'], self::MESSAGE_RESPONSE, $parameters);
        $log->info('LTI Deep Linking response with {count} objects sent to the platform: {request}', [
            'count' => count($items),
            'request' => ilLTILibraryLogger::describe([
                'registration' => $payload['platform'],
                'issuer' => $payload['iss'],
                'client_id' => $payload['client_id'],
                'deployment_id' => $payload['deployment_id'],
                'return_url' => $payload['return_url'],
                'objects' => implode(',', array_intersect(array_map('intval', (array) $data[self::FIELD_OBJECTS]), $ref_ids)),
            ]),
        ]);

        ilLTIAdvantageResponse::html($page);
    }

    /**
     * @param array $ref_ids
     */
    private static function buildForm(string $token, array $ref_ids, bool $multiple): Form
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule('lti');
        $field = $DIC->ui()->factory()->input()->field();
        $options = [];
        foreach ($ref_ids as $ref_id) {
            $options[$ref_id] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
        }

        if ($multiple) {
            $input = $field->multiSelect($lng->txt('objects'), $options)->withRequired(true);
        } else {
            $input = $field->radio($lng->txt('objects'))->withRequired(true);
            foreach ($options as $ref_id => $title) {
                $input = $input->withOption((string) $ref_id, $title);
            }
        }

        return $DIC->ui()->factory()->input()->container()->form()->standard(
            ilLTIEndpoint::getUrl(ilLTIEndpoint::LAUNCH, '?' . self::TOKEN_PARAM . '=' . rawurlencode($token)),
            [self::FIELD_OBJECTS => $input]
        )->withSubmitLabel($lng->txt('lti_deep_linking_submit'));
    }

    private static function printSelection(Form $form): void
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('lti');
        new ilLTIViewGUI()->printPage($DIC->language()->txt('lti_deep_linking_select'), [
            $DIC->ui()->factory()->messageBox()->info($DIC->language()->txt('lti_deep_linking_select_info')),
            $form,
        ]);
    }

    private function printError(string $lang_var): never
    {
        global $DIC;
        /** @var ILIAS\DI\Container $DIC */

        $DIC->language()->loadLanguageModule('lti');
        new ilLTIViewGUI()->printPage($DIC->language()->txt('lti_deep_linking_select'), [
            $DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt($lang_var)),
        ]);
        $DIC->http()->close();
    }
}
