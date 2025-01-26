<?php

namespace Dashifen\WordPress\Themes\Dashifen2025;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;

class Theme extends AbstractThemeHandler
{
  public const string SLUG = 'dashifen-2025';
  
  /**
   * Uses addAction and/or addFilter to attach protected methods of this object
   * to the ecosystem of WordPress action and filter hooks.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      
      // we initialize our agents at priority level one so that they can, in
      // turn, use the default priority level of ten and know that it will not
      // have happened yet.  theoretically, if any of them try to use priority
      // level one it could be an issue, so let's not do that.
      
      $this->addAction('init', 'initializeAgents', 1);
      $this->addAction('init', 'removeVariousCoreActions', PHP_INT_MAX);
      $this->addAction('wp_enqueue_scripts', 'removeAssets', PHP_INT_MAX);
      $this->addAction('wp_enqueue_scripts', 'addAssets');
    }
  }
  
  /**
   * Removes core actions related to emojis and the WordPress skip link code.
   *
   * @return void
   */
  protected function removeVariousCoreActions(): void
  {
    // the following get rid of the emoticon to emoji transformations.  now
    // that we can add emojis via the keyboard on likely every possible
    // platform, if someone types :) we'll leave it that way rather than
    // wasting cycles converting it to 🙂.
    
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    // finally, we remove the core version of the skip link behaviors.  we can
    // make this happen all on our own some other way and, like other styles
    // we've removed, they're inline, so they can't get cached.
    
    remove_action('wp_footer', 'the_block_template_skip_link');
  }
  
  /**
   * Removes core assets that we don't need for this theme.
   *
   * @return void
   */
  protected function removeAssets(): void
  {
    if (!is_user_logged_in()) {
      
      // the WP dashicons are unnecessary for this theme unless a person is
      // logged in when the admin bar is on-screen.  so to save a bit of time,
      // we can simply deregister this style when no one is logged in.
      
      wp_deregister_style('dashicons');
    }
  }
  
  /**
   * Adds script and style assets to our theme.
   *
   * @return void
   */
  protected function addAssets(): void
  {
    $this->enqueue('assets/scripts/dashifen-2025.js');
    $this->enqueue('style.css');
  }
}
