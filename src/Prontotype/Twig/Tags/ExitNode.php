<?php

namespace Prontotype\Twig\Tags;

class ExitNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        if ($status = $this->getNode('status'))
        {
            $compiler
                ->write('throw new \Symfony\Component\HttpKernel\Exception\HttpException(')
                ->subcompile($status)
                ->raw(");\n");
        }
        else
        {
            $compiler->write("throw new \Symfony\Component\HttpKernel\Exception\HttpException(500);\n");
        }
    }
}