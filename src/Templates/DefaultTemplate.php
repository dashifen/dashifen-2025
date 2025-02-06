<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates;

use Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework\AbstractTemplate;

class DefaultTemplate extends AbstractTemplate
{
  /**
   * getPageContext
   *
   * Returns an array of information necessary for the compilation of a
   * specific twig template.
   *
   * @param array $siteContext
   *
   * @return array
   */
  protected function getPageContext(array $siteContext): array
  {
    $title = get_the_title();
    
    return [
      'title'       => $title,
      'entry_title' => $title,
      'content'     => $this->getContent(),
    ];
  }
}
