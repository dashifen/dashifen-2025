<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework;

use Dashifen\WPDebugging\WPDebuggingTrait;

class TemplateFactory
{
  use WPDebuggingTrait;
  
  /**
   * Given the name of a template object, instantiates and returns it.
   *
   * @param string $template
   *
   * @return AbstractTemplate
   */
  public static function produceTemplate(string $template): AbstractTemplate
  {
    $namespace = 'Dashifen\\WordPress\\Themes\\Dashifen2025\\Templates\\';
    
    $template = !class_exists($namespace . $template)
      ? $namespace . 'DefaultTemplate'
      : $namespace . $template;
    
    // we'll assume that, if it's a class that exists, $template refers to a
    // child of our AbstractTemplate object.  if not, PHP will help us fix the
    // problem when the return type hint fails.
    
    return new $template;
  }
}
