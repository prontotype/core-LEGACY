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
            return $this->geshiHighlight($matches[2], $mathes[1]); 
        }, $string); 
 
        return $output; 
    }
 
}