<?php
/**
 * A class implementing a token parser for translation nodes.
 *
 * @author Jaime Pérez Crespo
 */
namespace JaimePerez\TwigConfigurableI18n\Twig\Extensions\TokenParser;

use JaimePerez\TwigConfigurableI18n\Twig\Extensions\Node\Trans as NodeTrans;
use Twig\Token;

class Trans extends \Twig\Extensions\TokenParser\TransTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig\Token $token A \Twig\Token instance
     *
     * @return \Twig\Node\Node A \Twig\Node\Node instance
     */
    public function parse(Token $token)
    {
        $parsed = parent::parse($token);
        $body = ($parsed->hasNode('body')) ? $parsed->getNode('body') : null;
        $plural = ($parsed->hasNode('plural')) ? $parsed->getNode('plural') : null;
        $count = ($parsed->hasNode('count')) ? $parsed->getNode('count') : null;
        $notes = ($parsed->hasNode('notes')) ? $parsed->getNode('notes') : null;

        /** @var \Twig\Node\Node $retval */
        $retval = new NodeTrans($body, $plural, $count, $notes, $parsed->getTemplateLine(), $parsed->getNodeTag());
        return $retval;
    }
}
