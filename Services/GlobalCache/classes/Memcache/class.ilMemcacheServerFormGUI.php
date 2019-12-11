<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilMemcacheServer.php');

/**
 * Class ilMemcacheServerFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMemcacheServerFormGUI extends ilPropertyFormGUI
{
    const F_HOST = 'host';
    const F_PORT = 'port';
    const F_STATUS = 'status';
    const F_WEIGHT = 'weight';
    /**
     * @var  ilMemcacheServer
     */
    protected $object;
    /**
     * @var bool
     */
    protected $is_new = true;


    /**
     * @param ilMemcacheServer $object
     */
    public function __construct(ilMemcacheServer $object)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->object = $object;
        $this->lng = $lng;
        $this->is_new = ($this->object->getId() == 0);
        $this->initForm();
    }


    protected function initForm()
    {
        $this->setTarget('_top');
        $this->setFormAction('setup.php?cmd=gateway&mcsid=' . $_GET['mcsid']);
        $this->initButtons();

        $te = new ilCheckboxInputGUI($this->txt(self::F_STATUS), self::F_STATUS);
        $this->addItem($te);

        $te = new ilTextInputGUI($this->txt(self::F_HOST), self::F_HOST);
        $te->setRequired(true);
        $this->addItem($te);

        $te = new ilTextInputGUI($this->txt(self::F_PORT), self::F_PORT);
        $te->setRequired(true);
        $this->addItem($te);

        $se = new ilSelectInputGUI($this->txt(self::F_WEIGHT), self::F_WEIGHT);
        $se->setOptions(array( 10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100 ));
        $this->addItem($se);
    }


    public function fillForm()
    {
        $array = array(
            self::F_STATUS => $this->object->getStatus() == ilMemcacheServer::STATUS_ACTIVE,
            self::F_HOST   => $this->object->getHost(),
            self::F_PORT   => $this->object->getPort(),
            self::F_WEIGHT => $this->object->getWeight(),
        );

        $this->setValuesByArray($array);
    }


    /**
     * @return bool
     */
    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->object->setStatus(($this->getInput(self::F_STATUS) == 1 ? ilMemcacheServer::STATUS_ACTIVE : ilMemcacheServer::STATUS_INACTIVE));
        $this->object->setHost($this->getInput(self::F_HOST));
        $this->object->setPort($this->getInput(self::F_PORT));
        $this->object->setWeight($this->getInput(self::F_WEIGHT));

        return true;
    }


    /**
     * @param $key
     *
     * @return string
     */
    protected function txt($key)
    {
        return $this->lng->txt('memcache_' . $key);
    }


    /**
     * @return bool|string
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }

        if ($this->object->getId()) {
            $this->object->update();
        } else {
            $this->object->create();
        }

        return $this->object->getId();
    }


    protected function initButtons()
    {
        switch (true) {
            case  $this->is_new:
                $this->setTitle($this->txt('add'));
                $this->addCommandButton('createMemcacheServer', $this->txt('add'));
                $this->addCommandButton('cache', $this->txt('cancel'));
                break;
            case  !$this->is_new:
                $this->setTitle($this->txt('update'));
                $this->addCommandButton('updateMemcacheServer', $this->txt('update'));
                $this->addCommandButton('cache', $this->txt('cancel'));
                break;
        }
    }
}
