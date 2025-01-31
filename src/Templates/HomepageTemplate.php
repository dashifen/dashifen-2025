<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates;

use Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework\AbstractTemplate;

class HomepageTemplate extends AbstractTemplate
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
    return [
      'title'           => 'Home',
      'entry_title'     => 'A Bit About Dash',
    ];
  }
}
