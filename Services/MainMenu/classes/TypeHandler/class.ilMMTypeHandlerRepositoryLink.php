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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;

/**
 * Class ilMMTypeHandlerRepositoryLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerRepositoryLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler
{

    /**
     * @inheritdoc
     */
    public function matchesForType() : string
    {
        return RepositoryLink::class;
    }


    /**
     * @inheritdoc
     */
    public function enrichItem(isItem $item) : isItem
    {
        global $DIC;
        if ($item instanceof RepositoryLink && isset($this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION])) {
            $ref_id = (int) $this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION];
            $item = $item->withRefId($ref_id)
                ->withVisibilityCallable(
                    function () use ($DIC, $ref_id, $item) {
                        $is_visible_parent = $item->isVisible();
                        $has_access = $DIC->access()->checkAccess('join', '', $ref_id)
                            || $DIC->access()->checkAccess('read', '', $ref_id);

                        return $is_visible_parent
                            && $has_access;
                    }
                );
        }

        return $item;
    }


    /**
     * @inheritdoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array
    {
        global $DIC;
        $url = $DIC->ui()->factory()->input()->field()->numeric($this->getFieldTranslation())
                   ->withAdditionalTransformation(
                       $DIC->refinery()->custom()->constraint(
                           function ($value) use ($DIC) : bool {
                               return !$DIC->repositoryTree()->isGrandChild(SYSTEM_FOLDER_ID, $value);
                           },
                           $DIC->language()->txt("msg_ref_id_not_callable")
                       )
                   );
        if (isset($this->links[$identification->serialize()][self::F_ACTION]) && is_numeric($this->links[$identification->serialize()][self::F_ACTION])) {
            $url = $url->withValue((int) $this->links[$identification->serialize()][self::F_ACTION]);
        }

        return [self::F_ACTION => $url];
    }


    /**
     * @inheritDoc
     */
    protected function getFieldTranslation() : string
    {
        global $DIC;

        return $DIC->language()->txt("field_ref_id");
    }


    /**
     * @inheritDoc
     */
    protected function getFieldInfoTranslation() : string
    {
        global $DIC;

        return $DIC->language()->txt("field_ref_id_info");
    }
}
