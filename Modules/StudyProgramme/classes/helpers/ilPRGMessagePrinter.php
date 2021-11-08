<?php declare(strict_types = 1);

/**
 * Util around ilPRGMessageCollection
 * factors and output collections.
 */
class ilPRGMessagePrinter
{
    /**
     * @var ilPRGMessageCollection
     */
    protected $collection;
    
    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct(
        ilPRGMessageCollection $collection,
        ilLanguage $lng
    ) {
        $this->collection = $collection;
        $this->lng = $lng;
    }

    public function getMessageCollection(string $topic) : ilPRGMessageCollection
    {
        return $this->collection->withNewTopic($topic);
    }

    public function showMessages(ilPRGMessageCollection $msg)
    {
        if ($msg->hasSuccess()) {
            $out = sprintf(
                $this->lng->txt($msg->getDescription()),
                count($msg->getSuccess())
            );
            \ilUtil::sendSuccess($out, true);
        }

        if ($msg->hasErrors()) {
            $errmsg = [];
            foreach ($msg->getErrors() as $err) {
                list($message, $rec_indentifier) = $err;
                $errmsg[] = sprintf('<li>%s (%s)</li>', $rec_indentifier, $this->lng->txt($message));
            }

            $out = sprintf(
                $this->lng->txt($msg->getDescription() . '_failed'),
                count($errmsg)
            )
            . '<ul>' . implode('', $errmsg) . '</ul>';
            
            ilUtil::sendInfo($out, true);
        }
    }
}
