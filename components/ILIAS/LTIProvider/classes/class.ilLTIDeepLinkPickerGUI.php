<?php
declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Log\LoggerInterface;

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
        global $DIC;
        $access = $DIC->access();
        return !in_array($ref_id, $proccesedIds) && !ilObject::_isInTrash($ref_id) && $access->checkAccess('read', '', $ref_id);
    }

    public function getAvailableResourcesForDL(int $ref_id, ?array &$proccesedIds = array()): ?array
    {
        $context = array();

        global $DIC;
        $tree = $DIC->repositoryTree();
        $path = array_reverse($tree->getPathFull($ref_id));

        foreach ($path as $row) {
            if (in_array($row['type'], array('cat', 'root')) && !empty($context)) {
                break;
            }
            if ($this->checkToShowInDeepLink($row['child'], $proccesedIds)) {
                $context[(string)$row['child']] = $row['title'];
                $proccesedIds[] = $row['child'];
                $children = $tree->getChilds($row['child']);
                if (sizeof($children) > 0 && sizeof($proccesedIds) < 8) {
                    foreach ($children as $child) {
                        if ($this->checkToShowInDeepLink($child['child'], $proccesedIds)) {
                            $contextRes = $this->getAvailableResourcesForDL($child['child'], $proccesedIds);
                            $context += $contextRes;
                        }

                    }
                }

            }
        }

        return $context;
    }

    public function show(): void
    {
        $dl = ilSession::get('lti_dl_ctx') ?? [];
        if (empty($dl['deep_link_return_url']) || empty($dl['deployment_id']) || empty($dl['iss'])) {
            $this->tpl->setOnScreenMessage('failure', 'Deep linking context missing', true);
            return;
        }

        $choices = $this->getSelectableItems();

        $form = new ilPropertyFormGUI();
        $form->setFormAction('ltidlresponse.php');
        $form->setTitle($this->lng->txt("tab_content"));

        $si = new ilCheckboxGroupInputGUI($this->lng->txt("tab_content"), 'ref_ids');
        $si->setRequired(true);
        foreach ($choices as $id => $label) {
            $si->addOption(new ilCheckboxOption($label, (string)$id));
        }
        $form->addItem($si);

        $form->addCommandButton('submit', $this->lng->txt('select'));
        $tpl = new ilTemplate('tpl.lti_dl_resources.html', true, true, 'components/ILIAS/LTIProvider');
        $v = DEVMODE ? '?vers=' . time() : '?vers=' . ILIAS_VERSION_NUMERIC;
        $delos_href = 'assets/css/delos.css' . $v;
        $jquery_href = 'assets/js/jquery.js' . $v;
        $tpl->setVariable("DELOS_HREF", $delos_href);
        $tpl->setVariable("JQUERY_HREF", $jquery_href);
        $tpl->setVariable("BODY", $form->getHTML());
        echo $tpl->get();
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
