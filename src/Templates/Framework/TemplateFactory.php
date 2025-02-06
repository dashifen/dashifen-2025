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
    
    if (!class_exists($namespace . $template)) {
      $template = 'DefaultTemplate';
    }
    
    $namespacedTemplate = $namespace . $template;
    return new $namespacedTemplate($template);
  }
}
