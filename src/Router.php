<?php

namespace Dashifen\WordPress\Themes\Dashifen2025;

use Dashifen\WPDebugging\WPDebuggingTrait;
use Dashifen\WPHandler\Traits\CaseChangingTrait;

class Router
{
  use CaseChangingTrait;
  use WPDebuggingTrait;
  
  /**
   * Uses WordPress core functions to identify what template to use based on
   * core's understanding of what content we're loading.  This is in lieu of
   * the series of if-statements found in core's template-loader.php file.
   *
   * @return string
   */
  public function getTemplateObjectName(): string
  {
    return match (true) {
      is_page()       => $this->getPageTemplate(),
      is_singular()   => $this->getPostTypeTemplate(),
      is_front_page() => 'HomepageTemplate',
      is_404()        => 'FourOhFourTemplate',
      default         => 'DefaultTemplate',
    };
  }
  
  /**
   * Returns the template object name for this page defaulting to the
   * DefaultTemplate, naturally.
   *
   * @return string
   */
  private function getPageTemplate(): string
  {
    return match (get_page_template_slug()) {
      "reading"   => "ReadingTemplate",
      "listening" => "ListeningTemplate",
      default     => "DefaultTemplate",
    };
  }
  
  /**
   * Returns the name of the template to use for the current post type.
   *
   * @return string
   */
  private function getPostTypeTemplate(): string
  {
    $postType = get_post_type();
    
    // we want to take post types like calendar_event or member-profile and
    // make them CalendarEventTemplate or MemberProfileTemplate respectively.
    // this ternary statement converts the post type name to PascalCase, and
    // then we just add the word "Template" to that before we return it.
    
    $template = str_contains($postType, '_')
      ? $this->snakeToPascalCase($postType)
      : $this->kebabToPascalCase($postType);
    
    return $template . 'Template';
  }
}
