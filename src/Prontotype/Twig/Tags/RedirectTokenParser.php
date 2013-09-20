<?php

namespace Prontotype\Twig\Tags;

class RedirectTokenParser extends \Twig_TokenParser
{
    /**
     * Parses {% redirect %} tags.
     *
     * @param \Twig_Token $token
     * @return Redirect_Node
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $path = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new RedirectNode(array('path' => $path), array(), $lineno, $this->getTag());
    }
    
    public function getTag()
    {
        return 'redirect';
    }
}
