<?php declare(strict_types=1);

/**
 * Util around ilPRGMessageCollection
 * factors and output collections.
 */
class ilPRGMessagePrinter
{
    protected ilPRGMessageCollection $collection;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(
        ilPRGMessageCollection $collection,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl
    ) {
        $this->collection = $collection;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    public function getMessageCollection(string $topic) : ilPRGMessageCollection
    {
        return $this->collection->withNewTopic($topic);
    }

    public function showMessages(ilPRGMessageCollection $msg) : void
    {
        if ($msg->hasSuccess()) {
            $out = sprintf(
                $this->lng->txt($msg->getDescription()),
                count($msg->getSuccess())
            );
            $this->tpl->setOnScreenMessage("success", $out, true);
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

            $this->tpl->setOnScreenMessage("success", $out, true);
        }
    }
}
