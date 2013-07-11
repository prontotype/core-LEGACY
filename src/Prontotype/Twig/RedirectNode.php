<?php

namespace Prontotype\Twig;

class RedirectNode extends \Twig_Node
{
	/**
	 * Compiles a Redirect_Node into PHP.
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
		    ->addDebugInfo($this)
    		    ->write('header(\'Location: \'.')
    		    ->subcompile($this->getNode('path'))
    		    ->raw(".''); exit();\n");
		    
	}
}
