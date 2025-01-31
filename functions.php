<?php

namespace Dashifen\WordPress\Themes;

use Dashifen\Exception\Exception;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Agents\ShushingAgent;
use Dashifen\WordPress\Themes\Dashifen2025\Agents\CoreRemovalAgent;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory;

defined('ABSPATH') || die;

if (version_compare(PHP_VERSION, '8.4', '<')) {
  $message = "This theme requires at least PHP 8.4; you're using %s.";
  wp_die(sprintf($message, PHP_VERSION), 'Upgrade PHP', ['response' => 503]);
}

if (!class_exists(Theme::class)) {
  
  // if we don't already know about or Theme object, then we'll include the
  // local autoloader here.  in a fully composer integrated WordPress build,
  // likely we won't need to do this, but if this theme is just dropped into
  // the wp-content/themes folder, we probably will.
  
  require __DIR__ . '/vendor/autoload.php';
}

(function () {
  
  // by initializing our Theme in this anonymous function, we prevent anything
  // from being loaded into the global WordPress scope which ensures that said
  // ecosystem won't be able to access its public methods.
  
  try {
    $theme = new Theme();
    $acf = new AgentCollectionFactory();
    $acf->registerAgent(CoreRemovalAgent::class);
    $acf->registerAgent(ShushingAgent::class);
    $theme->setAgentCollection($acf);
    $theme->initialize();
  } catch (Exception $e) {
    
    //if an Exception is thrown during its initialization, then we'll catch it
    // with the Theme's catcher.  by default, in environments when WP_DEBUG is
    // on, it'll emit the error on-screen.  otherwise, it'll try to write it to
    // the WordPress debug.log file.
    
    Theme::catcher($e);
  }
})();
