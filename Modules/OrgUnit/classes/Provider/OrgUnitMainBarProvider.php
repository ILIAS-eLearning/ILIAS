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
 ********************************************************************
 */ namespace ILIAS\OrgUnit\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilObjOrgUnit;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\DI\Container;
use ilObjTalkTemplateAdministration;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class OrgUnitMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitMainBarProvider extends AbstractStaticMainMenuProvider
{
    private IdentificationInterface $organisationIdentifier;
    private IdentificationInterface $orgUnitIdentifier;
    private IdentificationInterface $employeeTalkTemplateIdentifier;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->organisationIdentifier = $this->if->identifier('mm_adm_org');
        $this->orgUnitIdentifier = $this->if->identifier('mm_adm_org_orgu');
        $this->employeeTalkTemplateIdentifier = $this->if->identifier('mm_adm_org_etal');
    }

    public function getStaticTopItems() : array
    {
        return [];
    }

    /**
     * @return isItem[]
     */
    public function getStaticSubItems() : array
    {
        $this->dic->language()->loadLanguageModule('mst');
        $this->dic->language()->loadLanguageModule('etal');

        $items = [];
        $access_helper = BasicAccessCheckClosuresSingleton::getInstance();
        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title = $this->dic->language()->txt("objs_orgu");
        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . ilObjOrgUnit::getRootOrgRefId() . "&cmd=jump";
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('orgu', $title);

        $linkOrgUnit = $this->mainmenu->link($this->orgUnitIdentifier)
                                      ->withAlwaysAvailable(true)
                                      ->withAction($action)
                                      ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
                                      ->withParent($this->organisationIdentifier)
                                      ->withTitle($title)
                                      ->withSymbol($icon)
                                      ->withPosition(10)
                                      ->withVisibilityCallable(
                                          $access_helper->hasAdministrationAccess(function () : bool {
                                              return $this->dic->access()->checkAccess(
                                                  'read',
                                                  '',
                                                  ilObjOrgUnit::getRootOrgRefId()
                                              );
                                          })
                                      );

        $title = $this->dic->language()->txt("mm_talk_template", "");
        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . ilObjTalkTemplateAdministration::getRootRefId() . "&cmd=jump";
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('etal', $title);
        $linkEmployeeTalkTemplates = $this->mainmenu->link($this->employeeTalkTemplateIdentifier)
                                                    ->withAlwaysAvailable(true)
                                                    ->withAction($action)
                                                    ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
                                                    ->withParent($this->organisationIdentifier)
                                                    ->withTitle($title)
                                                    ->withSymbol($icon)
                                                    ->withPosition(20)
                                                    ->withVisibilityCallable(
                                                        $access_helper->hasAdministrationAccess(function () : bool {
                                                            return $this->dic->access()->checkAccess(
                                                                'read',
                                                                '',
                                                                ilObjOrgUnit::getRootOrgRefId()
                                                            );
                                                        })
                                                    );

        $title = $this->dic->language()->txt("mm_organisation");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('org', $title);
        $items[] = $this->mainmenu->linkList($this->organisationIdentifier)
                                  ->withAlwaysAvailable(true)
                                  ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
                                  ->withParent($top)
                                  ->withTitle($title)
                                  ->withSymbol($icon)
                                  ->withPosition(70)
                                  ->withLinks([$linkOrgUnit, $linkEmployeeTalkTemplates])
                                  ->withVisibilityCallable(
                                      $access_helper->hasAdministrationAccess(function () : bool {
                                          return $this->dic->access()->checkAccess(
                                              'read',
                                              '',
                                              ilObjOrgUnit::getRootOrgRefId()
                                          );
                                      })
                                  );

        return $items;
    }
}
