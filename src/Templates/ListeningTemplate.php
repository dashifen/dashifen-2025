<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates;

use Dashifen\WordPress\Themes\Dashifen2025\Entities\Playlist\Playlist;

class ListeningTemplate extends DefaultTemplate
{
  /**
   * Returns an array of information necessary for the compilation of a
   * specific request.
   *
   * @param array $siteContext
   *
   * @return array
   */
  protected function getPageContext(array $siteContext): array
  {
    return array_merge(parent::getPageContext($siteContext), [
      'playlist' => new Playlist()->toArray()
    ]);
  }
  
}
