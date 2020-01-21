<?php

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
                    function () use ($DIC, $ref_id) {
                        return (bool) $DIC->access()->checkAccess('visible', '', $ref_id);
                    }
                );
            // ->withAvailableCallable(
            // 	function () use ($ref_id) {
            // 		$b = (bool)(ilObject2::_exists($ref_id, true) && !ilObject2::_isInTrash($ref_id));
            //
            // 		return $b;
            // 	}
            // )->withNonAvailableReason($DIC->ui()->factory()->legacy($DIC->language()->txt('deleted')))
        }

        return $item;
    }


    /**
     * @inheritdoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array
    {
        global $DIC;
        $url = $DIC->ui()->factory()->input()->field()->numeric($this->getFieldTranslation());
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
