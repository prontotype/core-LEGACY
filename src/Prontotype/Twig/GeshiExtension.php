<?php

namespace Prontotype\Twig;
 
class GeshiExtension extends \Twig_Extension
{
    protected $geshi;
    
    public function getName()
    {
        return 'geshi_highlight'; 
    }
    
    public function getFilters()
    {
        return array(
            'geshiPre' => new \Twig_Filter_Method($this, 'replaceGeshiPre'), 
            'geshi' => new \Twig_Filter_Method($this, 'geshiHighlight'), 
        ); 
    }
 
    public function geshiHighlight($source, $language)
    {
        $geshi = new \GeSHi($source, $language);
        $geshi->enable_classes();
        return $geshi->parse_code();
    }
 
    public function replaceGeshiPre($string)
    {
        $pattern = '#<pre class="brush: (.+?)">(.+?)</pre>#si'; 
        $output = preg_replace_callback($pattern, function($matches) {
            $geshi = new \GeSHi($matches[2], $matches[1]);
            $geshi->enable_classes();
            $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
            return $geshi->parse_code();
        }, $string);
        return $output; 
    }
 
}