<?php

namespace Dashifen\WordPress\Themes\Dashifen2025;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;

class Theme extends AbstractThemeHandler
{
  public const string SLUG = 'dashifen-2025';
  
  private bool $coreTemplateLoaderPrevention;
  
  /**
   * Returns our slug ready-to-go as a prefix for options, etc.
   *
   * @return string
   */
  public static function getPrefix(): string
  {
    return self::SLUG . '-';
  }
  
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
      $this->addFilter('timber/loader/loader', 'addTimberNamespaces');
      $this->addAction('after_setup_theme', 'prepareTheme');
      $this->addAction('wp_head', 'addPreConnections');
      $this->addAction('wp_enqueue_scripts', 'addAssets');
      $this->addFilter('pre_get_document_title', 'customizeTitle');
      
      // these two disengage this theme from the core WordPress template
      // loader.  instead, this one uses the Router object to load template
      // objects instead of using more typical class WordPress PHP based
      // template files.  we use PHP_INT_MAX for our template_redirect action
      // to allow other filters attached to that hook to happen before we do
      // our work here.
      
      $this->addFilter('wp_using_themes', 'preventCoreTemplateLoader');
      $this->addAction('template_redirect', 'almostAlwaysIncludeIndex', PHP_INT_MAX);
    }
  }
  
  /**
   * Creates a Timber namespace for each of the folders within assets/twigs.
   *
   * @param FilesystemLoader $loader
   *
   * @return FilesystemLoader
   * @throws LoaderError
   */
  protected function addTimberNamespaces(FilesystemLoader $loader): FilesystemLoader
  {
    $dir = $this->getStylesheetDir() . '/assets/twigs';
    $folders = array_filter(glob($dir . '/*'), 'is_dir');
    foreach ($folders as $folder) {
      
      // each folder within our $folders runs from the root of the filesystem
      // all thw way to the one of the folders within assets/twigs.  all we
      // want are those folder names, so we can explode them all, pop off the
      // last bit, and then use that as a namespace.
      
      $namespaces = explode('/', $folder);
      $namespace = array_pop($namespaces);
      $loader->addPath($folder, $namespace);
    }
    
    return $loader;
  }
  
  /**
   * Specifies additional WordPress features that our theme supports as well
   * as registers menus.
   *
   * @return void
   */
  protected function prepareTheme(): void
  {
    register_nav_menus(['main' => 'Main Menu', 'footer' => 'Footer Menu']);
    add_theme_support('post-thumbnails', get_post_types(['public' => true]));
  }
  
  /**
   * Adds preconnect <link> elements to the <head> of the document.
   *
   * @return void
   */
  protected function addPreConnections(): void
  {
    $connections = [
      'https://fonts.googleapis.com' => '',
      'https://fonts.gstatic.com'    => 'crossorigin',
    ];
    
    $link = /** @lang text */
      '<link rel="preconnect" href="%s" %s>';
    
    foreach ($connections as $connection => $modification) {
      echo sprintf($link, $connection, $modification) . PHP_EOL;
    }
  }
  
  /**
   * Adds script and style assets to our theme.
   *
   * @return void
   */
  protected function addAssets(): void
  {
    $fonts[] = $this->enqueue('//fonts.googleapis.com/css2?family=Nunito&display=swap');
    $this->enqueue('assets/styles/dashifen.css', $fonts);
    $this->enqueue('assets/scripts/dashifen.js');
  }
  
  /**
   * Makes sure the <title> tag is exactly the way we want it to be.
   *
   * @return string
   */
  protected function customizeTitle(): string
  {
    $titleParts[] = !is_front_page() ? get_the_title() : 'Welcome';
    $titleParts[] = get_bloginfo('name');
    return vsprintf('%s | %s', $titleParts);
  }
  
  /**
   * Returns true the first time and false thereafter.  First time, this allows
   * the template_redirect actions to occur.  Subsequently, it'll prevent the
   * WP Core template loader from using up some server-side time executing the
   * Core router when we have our own as a part of our theme.
   *
   * @return bool
   */
  protected function preventCoreTemplateLoader(): bool
  {
    // this method needs to return true the first time and false thereafter.
    // we do this by leaving this property unset above.  the first time we get
    // here, because it's not set, the latter half of the AND operation will
    // be run setting it to true and returning that true value by side effect.
    // subsequently, it will have already been set, and so the !isset test will
    // short-circuit the AND operation and we'll return false.
    
    return !isset($this->coreTemplateLoaderPrevention)
      && ($this->coreTemplateLoaderPrevention = true);
  }
  
  /**
   * Almost always includes the index file for this theme which handles the
   * routing operations.
   *
   * @return void
   */
  protected function almostAlwaysIncludeIndex(): void
  {
    if (is_trackback() || is_feed() || is_favicon() || is_robots()) {
      
      // the template-loader.php file checks for each of these four options
      // after it runs the template_redirect actions.  in each of these cases,
      // it halts the operation of that file after doing some additional work.
      // we don't want to impact that work, so we just return here.
      
      return;
    }
    
    // otherwise, because we've blocked the default WordPress template loader,
    // we want to include the index.php template file for our theme now because
    // we won't be relying on WP Core to do so for us.
    
    include locate_template('index.php');
  }
}
