<?php

namespace Dashifen\WordPress\Themes;

use Dashifen\Exception\Exception;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Agents\ShushingAgent;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory;

defined('ABSPATH') || die;

if (!class_exists(Theme::class)) {
  
  // if we don't already know about or Theme object, then we'll include the
  // local autoloader here.  in a fully composer integrated WordPress build,
  // likely we won't need to do this, but if this theme is just dropped into
  // the wp-content/themes folder, we probably will.
  
  require __DIR__ . '/vendor/autoload.php';
}

// before we do any more work, let's make sure that the host's PHP version is
// high enough.  since we do some filesystem and regular expression work while
// we do so, we only do it if we notice that the host's PHP version is new.

if (get_option('dashifen2025-php-version', '0.0') !== PHP_VERSION) {
  
  // first, we read and decode the composer.json file.  this produces an object
  // that includes our php version.  we dig down to it and make sure it's just
  // the numbers (i.e. we remove the >= sign that is likely included with it.
  // then, if the host's PHP_VERSION is less than the one listed the composer
  // config, we die with a message.
  
  $composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'));
  $phpVersion = preg_replace('/[^.\d]/', '', $composerJson->require->php);
  if (version_compare(PHP_VERSION, $phpVersion, '<')) {
    $message = "This theme requires at least PHP $phpVersion; you're using %s.";
    wp_die(sprintf($message, PHP_VERSION), args: ['response' => 503]);
  }
  
  // if we made it this far, then the current PHP version on the server is
  // new enough to support our theme.  therefore, we update our option in the
  // database to record that version.  this way, until the version number
  // changes, we won't re-do this work again.
  
  update_option('dashifen2025-php-version', PHP_VERSION, autoload: true);
}


(function () {
  
  // by initializing our Theme in this anonymous function, we prevent anything
  // from being loaded into the global WordPress scope which ensures that said
  // ecosystem won't be able to access its public methods.
  
  try {
    $theme = new Theme();
    $acf = new AgentCollectionFactory();
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
