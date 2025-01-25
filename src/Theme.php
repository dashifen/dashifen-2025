<?php

namespace Dashifen\WordPress\Themes\Dashifen2025;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Traits\OptionsManagementTrait;
use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;

class Theme extends AbstractThemeHandler
{
  use OptionsManagementTrait;
  
  /**
   * Uses the addAction and/or addFilter methods to attach protected methods of
   * this object to the WordPress ecosystem of action and filter hooks.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addAction('wp_enqueue_scripts', 'addAssets');
    }
  }
  
  /**
   * Adds script and style assets to our theme.
   *
   * @return void
   */
  protected function addAssets(): void
  {
    $this->enqueue('assets/scripts/copyright-year.js');
  }
  
  protected function getOptionNames(): array
  {
    return ['php-version'];
  }
}
