<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use ILIAS\DI\Container;

/**
 * Class ilLTIDeepLinkPickerGUI
 *
 * @ilCtrl_isCalledBy ilLTIDeepLinkPickerGUI: ilLTIViewGUI
 *
 * @ingroup ServicesLTI
 */
class ilLTIDeepLinkPickerGUI implements ilCtrlBaseClassInterface
{
    /** @var Container */
    protected Container $dic;

    /** @var ilCtrl */
    protected ilCtrl $ctrl;

    /** @var ilLanguage */
    protected ilLanguage $lng;

    /** @var ilGlobalTemplateInterface */
    protected \ilGlobalTemplateInterface $tpl;

    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('lti');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->logger = $DIC->logger()->root();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('show');

        if (!in_array($cmd, ['show'])) {
            $cmd = 'show';
        }
        $this->logger->info("Redirecting to cmd: " . $cmd);
        switch ($cmd) {
            case 'show':
                $this->show();
                break;
        }
    }

    private function checkToShowInDeepLink(int $ref_id, array $proccesedIds): bool
    {
        $access = $this->dic->access();
        return !in_array($ref_id, $proccesedIds) && !ilObject::_isInTrash($ref_id) && $access->checkAccess('read', '', $ref_id);
    }

    public function getAvailableResourcesForDL(int $ref_id, ?array &$proccesedIds = array()): ?array
    {
        $context = array();
        $tree = $this->dic->repositoryTree();
        $path = $tree->getChilds($ref_id);

        foreach ($path as $row) {
            if ($this->checkToShowInDeepLink($row['child'], $proccesedIds)) {
                $context[(string)$row['child']] = $row['title'];
                $proccesedIds[] = $row['child'];
                $children = $tree->getChilds($row['child']);

                if (sizeof($children) > 0) {
                    foreach ($children as $child) {
                        if ($this->checkToShowInDeepLink($child['child'], $proccesedIds)) {
                            $contextRes = $this->getAvailableResourcesForDL($child['child'], $proccesedIds);
                            $context += $contextRes;
                        }
                    }
                }
            }
        }
        if (!in_array($ref_id, $proccesedIds)) {
            $obj = ilObjectFactory::getInstanceByRefId($ref_id);
            $context[(string)$ref_id] = $obj->getTitle();
        }

        return $context;
    }

    public function show(): void
    {
        $main_tpl = $this->dic->ui()->mainTemplate();
        $ui = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();
        $request = $this->dic->http()->request();
        $dl = ilSession::get('lti_dl_ctx') ?? [];
        if (empty($dl['deep_link_return_url']) || empty($dl['deployment_id']) || empty($dl['iss'])) {
            $this->tpl->setOnScreenMessage('failure', 'Deep linking context missing', true);
            return;
        }

        $choices = $this->getSelectableItems();
        $selectOptions = $ui->input()->field()->multiSelect($this->lng->txt("select"), $choices)->withRequired(true);
        $section = $ui->input()->field()->section([$selectOptions], $this->lng->txt("content_selection"));
        $form = $ui->input()->container()->form()->standard('#', [$section]);
        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $result = $form->getData();
            if (isset($result)) {
                $this->managePostDpRequest($result[0][0]);
                $main_tpl->setOnScreenMessage('success', 'Items successfully selected!', true);
            }
        }
        $logo = $ui->image()->responsive("./assets/images/logo/HeaderIcon.svg", CLIENT_ID);
        $tpl = new ilTemplate('tpl.lti_dl_resources.html', true, true, 'components/ILIAS/LTIProvider');
        $v = DEVMODE ? '?vers=' . time() : '?vers=' . ILIAS_VERSION_NUMERIC;
        $delos_href = 'assets/css/delos.css' . $v;
        $jquery_href = 'assets/js/jquery.js' . $v;
        $tpl->setVariable("DELOS_HREF", $delos_href);
        $tpl->setVariable("CLIENT_ID", CLIENT_ID);
        $tpl->setVariable("JQUERY_HREF", $jquery_href);
        $tpl->setVariable("BODY", $renderer->render($form));
        $tpl->setVariable("LOGO", $renderer->render($logo));
        echo $tpl->get();
    }

    public function managePostDpRequest(array $ref_ids): void
    {
        $dl = ilSession::get('lti_dl_ctx');

        $launchUrl = ILIAS_HTTP_PATH . '/lti.php';

        $content_items = [];

        foreach ($ref_ids as $ref_id) {
            $obj = ilObjectFactory::getInstanceByRefId((int)$ref_id);
            $title = trim($_POST['title'] ?? '') ?: $obj->getTitle();
            $description = trim($_POST['description'] ?? '') ?: $obj->getDescription();
            $content_items[] = [
                "type" => "ltiResourceLink",
                "title" => $title,
                "description" => $description,
                "url" => $launchUrl,
                "custom" => [
                    "ilias_ref_id" => (string)$ref_id,
                    "id" => (string)$ref_id
                ],
            ];
        }
        $clientId = $dl["consumer_key"];
        $now = time();
        $payload = [
            "iss" => $clientId,
            "aud" => [$dl['platform_id']],
            "iat" => $now,
            "exp" => $now + 600,
            "nonce" => $dl['nonce'] ?? bin2hex(random_bytes(8)),

            "https://purl.imsglobal.org/spec/lti/claim/message_type" => "LtiDeepLinkingResponse",
            "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
            "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => (string)$dl['deployment_id'],
            "https://purl.imsglobal.org/spec/lti-dl/claim/content_items" => $content_items,
        ];


        if (!empty($dl['data'])) {
            $payload["https://purl.imsglobal.org/spec/lti-dl/claim/data"] = $dl['data'];
        }
        $privateKey = ilObjLTIConsumer::getPrivateKey();
        $jwt = JWT::encode($payload, $privateKey['key'], 'RS256', $privateKey['kid']);
        $return = htmlspecialchars($dl['deep_link_return_url'], ENT_QUOTES);
        echo <<<HTML
<!doctype html>
<html><body onload="document.forms[0].submit()">
  <form action="{$return}" method="POST">
    <input type="hidden" name="JWT" value="{$jwt}">
    <noscript><button type="submit">Return to LMS</button></noscript>
  </form>
</body></html>
HTML;

    }

    /**
     * Return [ref_id => title] options the current user may share.
     * Replace this with your real repository logic (context-aware).
     */
    protected function getSelectableItems(): array
    {
        $res = [];
        if (ilSession::has("lti_context_ids") && sizeof(ilSession::get("lti_context_ids")) > 0) {
            $res = self::getAvailableResourcesForDL(ilSession::get("lti_context_ids")[0]);
        }
        return $res;
    }
}
