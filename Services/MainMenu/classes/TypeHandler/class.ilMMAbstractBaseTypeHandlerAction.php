<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ilMMAbstractBaseTypeHandlerAction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractBaseTypeHandlerAction implements TypeHandler
{

    /**
     * @var array
     */
    protected $links = [];
    /**
     * @inheritDoc
     */
    const F_ACTION = 'action';
    /**
     * ilMMAbstractBaseTypeHandlerAction constructor.
     */
    const F_EXTERNAL = 'external';


    public function __construct()
    {
        $this->links = ilMMTypeActionStorage::getArray('identification', [self::F_ACTION, self::F_EXTERNAL]);
    }


    abstract public function matchesForType() : string;


    /**
     * @inheritdoc
     */
    abstract public function enrichItem(isItem $item) : isItem;


    /**
     * @inheritdoc
     */
    public function saveFormFields(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $data) : bool
    {
        ilMMTypeActionStorage::find($identification->serialize())->setAction((string) $data[self::F_ACTION])->setExternal((bool) $data[self::F_EXTERNAL])->update();

        return true;
    }


    /**
     * @inheritdoc
     */
    public function getAdditionalFieldsForSubForm(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification) : array
    {
        global $DIC;
        $url = $DIC->ui()->factory()->input()->field()->text($this->getFieldTranslation())->withRequired(true)->withByline($this->getFieldInfoTranslation());
        if (isset($this->links[$identification->serialize()][self::F_ACTION])) {
            $url = $url->withValue($this->links[$identification->serialize()][self::F_ACTION]);
        }
        $external = $DIC->ui()->factory()->input()->field()->checkbox($DIC->language()->txt('field_external'), $DIC->language()->txt('field_external_info'));
        if (isset($this->links[$identification->serialize()][self::F_EXTERNAL])) {
            $external = $external->withValue((bool) $this->links[$identification->serialize()][self::F_EXTERNAL]);
        }

        return [self::F_ACTION => $url, self::F_EXTERNAL => $external];
    }


    /**
     * @return string
     */
    abstract protected function getFieldTranslation() : string;


    abstract protected function getFieldInfoTranslation() : string;
}
