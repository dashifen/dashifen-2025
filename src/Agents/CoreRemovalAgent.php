<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Agents;

use Dashifen\WPHandler\Agents\AbstractThemeAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;

/**
 * CoreRemovalAgent
 *
 * This agent removes Core theme features that we don't want to use (for now).
 *
 * @property Theme $handler
 */
class CoreRemovalAgent extends AbstractThemeAgent
{
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to attach protected methods of this object
   * to the ecosystem of WordPress action and filter hooks.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addAction('init', 'removeVariousCoreActions', PHP_INT_MAX);
      $this->addAction('wp_enqueue_scripts', 'disableDefaultAssets', PHP_INT_MAX);
    }
  }
  
  /**
   * removeVariousCoreActions
   *
   * Removes various core actions from different parts of the WordPress
   * ecosystem to cut down on what inline cruft is added to the DOM.
   *
   * @return void
   */
  protected function removeVariousCoreActions(): void
  {
    // this removes the global styles that this theme's not using for the
    // moment.  then we remove some SVGs that are added to the DOM.
    
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    
    // the following get rid of the emoticon to emoji transformations.  now
    // that we can add emojis via the keyboard on almost every possible
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
   * disableDefaultAssets
   *
   * Turns off the block-library generated CSS that appears as inline styles
   * in the DOM.
   *
   * @return void
   */
  protected function disableDefaultAssets(): void
  {
    // this is an old jQuery plugin that was used way back in the days of WP 3
    // to add hover styles to the admin menu when some browsers didn't always
    // respond well to :hover pseudo-class.  those days are long gone, so we
    // can just remove it entirely at this point.
    
    wp_deregister_script('hoverintent-js');
    
    // these are all added by Core to try and homogenize the editor and the
    // front-end display.  since this theme is not a block theme, it's more
    // or less cruft that we can replace by enqueuing our own editor styles
    // which we can do elsewhere as needed.
    
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    
    if (!is_user_logged_in()) {
      
      // the dashicons styles are needed for the WP admin bar.  but, if the
      // visitor isn't logged in, then they don't see that bar.  because this
      // theme doesn't use dashicons except in the admin bar, we therefore
      // deregister them for anonymous visitors to help speed up the site.
      
      wp_deregister_style('dashicons');
    }
  }
}
