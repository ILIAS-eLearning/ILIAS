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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * Filter administration for containers
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterAdminGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\DI\UIServices $ui;
    protected ilContainerFilterService $container_filter_service;
    protected int $ref_id;
    protected ilContainerGUI $container_gui;
    protected ServerRequestInterface $request;

    public function __construct(ilContainerGUI $container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->container_gui = $container_gui;
        $this->ref_id = $this->container_gui->getObject()->getRefId();
        $this->toolbar = $DIC["ilToolbar"];
        $this->ui = $DIC->ui();
        $this->request = $DIC->http()->request();
        // not sure if this should go to dic someday, currently this is not an internal API
        $this->container_filter_service = new ilContainerFilterService();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["show", "selectFields", "saveFields"])) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Show table
     */
    protected function show() : void
    {
        $main_tpl = $this->main_tpl;
        $ui = $this->ui;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $f = $ui->factory();

        $button = $f->button()->standard(
            $lng->txt("cont_select_fields"),
            $ctrl->getLinkTarget($this, "selectFields")
        );

        $this->toolbar->addComponent($button);

        /** @var $container ilObjCategory */
        $container = $this->container_gui->getObject();
        $table = new ilContainerFilterTableGUI(
            $this,
            "show",
            $this->container_filter_service,
            $container
        );
        $main_tpl->setContent($table->getHTML());
    }

    protected function selectFields() : void
    {
        $main_tpl = $this->main_tpl;
        $ui = $this->ui;
        $r = $ui->renderer();
        $form = $this->getFieldSelectionForm();
        $main_tpl->setContent($r->render($form));
    }

    protected function getFieldSelectionForm() : Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $adv = $this->container_filter_service->advancedMetadata();
        $service = $this->container_filter_service;


        $fields[] = [];


        // current filter set
        $current_filters = $service->data()->getFilterSetForRefId($this->ref_id);

        // standar set
        $selected = [];
        $options = [];
        foreach ($service->standardSet()->getFields() as $field) {
            $options[$field->getFieldId()] = $service->util()->getContainerFieldTitle($field->getRecordSetId(), $field->getFieldId());
            if ($current_filters->has(0, $field->getFieldId())) {
                $selected[] = $field->getFieldId();
            }
        }

        $fields[0] = $f->input()->field()->multiSelect($lng->txt("cont_std_record_title"), $options)
            ->withRequired(false)
            ->withValue($selected);

        // ADV MD record sets
        foreach ($adv->getAvailableRecordSets() as $rs) {
            $options = [];
            $selected = [];
            foreach ($adv->getFields($rs->getRecordId()) as $fl) {
                $options[$fl->getFieldId()] = $fl->getTitle();
                if ($current_filters->has($rs->getRecordId(), $fl->getFieldId())) {
                    $selected[] = $fl->getFieldId();
                }
            }
            $fields[$rs->getRecordId()] = $f->input()->field()->multiSelect($rs->getTitle(), $options, $rs->getDescription())
                ->withRequired(false)
                ->withValue($selected);
        }

        // Standard filter fields
        $section1 = $f->input()->field()->section($fields, $lng->txt("cont_filter_fields"), "");

        $form_action = $ctrl->getLinkTarget($this, "saveFields", "", false, false);
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    protected function saveFields() : void
    {
        $request = $this->request;
        $service = $this->container_filter_service;
        $form = $this->getFieldSelectionForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $fields = [];
        if ($request->getMethod() === "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();

            // ADV MD record sets
            if (is_array($data["sec"])) {
                foreach ($data["sec"] as $rec_id => $ids) {
                    if (is_array($ids)) {
                        foreach ($ids as $field_id) {
                            $fields[] = $service->field($rec_id, $field_id);
                        }
                    }
                }
            }
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            $service->data()->saveFilterSetForRefId($this->ref_id, $service->set($fields));
        }
        $ctrl->redirect($this, "");
    }
}
