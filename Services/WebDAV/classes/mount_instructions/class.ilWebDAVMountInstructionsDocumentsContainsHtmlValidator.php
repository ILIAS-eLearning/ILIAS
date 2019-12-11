<?php


class ilWebDAVMountInstructionsDocumentsContainsHtmlValidator
{
    /** @var string */
    private $text;

    /**
     * ilWebDAVMountInstructionsDocumentsContainsHtmlValidator constructor.
     * @param $purified_html_content
     */
    public function __construct(string $purified_html_content)
    {
        $this->text = $purified_html_content;
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        if (!preg_match('/<[^>]+?>/', $this->text))
        {
            return false;
        }

        try
        {
            $dom = new DOMDocument();
            if (!$dom->loadHTML($this->text))
            {
                return false;
            }

            $iter = new RecursiveIteratorIterator(
                new ilHtmlDomNodeIterator($dom),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iter as $element)
            {
                /** @var $element DOMNode */
                if (in_array(strtolower($element->nodeName), ['body']))
                {
                    continue;
                }

                if ($element->nodeType === XML_ELEMENT_NODE)
                {
                    return true;
                }
            }
        } catch (Exception $e)
        {
            return false;
        } catch (Throwable $e)
        {
            return false;
        }

    }
}