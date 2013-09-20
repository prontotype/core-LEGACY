<?php

namespace Prontotype\Twig\Tags;

class ExitTokenParser extends \Twig_TokenParser
{
    /**
     * Parses {% exit %} tags.
     *
     * @param \Twig_Token $token
     * @return Exit_Node
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        
		if ($stream->test(\Twig_Token::NUMBER_TYPE))
		{
			$status = $this->parser->getExpressionParser()->parseExpression();
		}
		else
		{
			$status = null;
		}

		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new \Prontotype\Twig\Tags\ExitNode(array('status' => $status), array(), $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'exit';
    }
}
