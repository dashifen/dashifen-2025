<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework;

use RegexIterator;
use Timber\Timber;
use FilesystemIterator;
use WP_HTML_Tag_Processor;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Dashifen\Transformer\TransformerException;
use Dashifen\WPHandler\Traits\CaseChangingTrait;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WPHandler\Traits\OptionsManagementTrait;
use Dashifen\WordPress\Themes\Dashifen2025\DTOs\MenuItem;
use Dashifen\WordPress\Themes\Dashifen2025\DTOs\Playlist\Song;
use Dashifen\WordPress\Themes\Dashifen2025\DTOs\Time\TimeOfDay;
use Dashifen\WPTemplates\AbstractTemplate as AbstractTimberTemplate;
use Dashifen\WordPress\Themes\Dashifen2025\DTOs\Playlist\Playlist;
use Dashifen\WPTemplates\TemplateException as BaselineTemplateException;
use Dashifen\WordPress\Themes\Dashifen2025\DTOs\Library\CurrentlyReading;

abstract class AbstractTemplate extends AbstractTimberTemplate
{
  use CaseChangingTrait;
  use OptionsManagementTrait;
  
  /**
   * AbstractTemplate constructor.
   *
   * @throws HandlerException
   * @throws TemplateException
   * @throws TransformerException
   */
  public function __construct(
    protected string $template,
    protected int $postId = 0,
  ) {
    $this->postId = is_home()
      ? get_option('page_for_posts')
      : get_the_ID();
    
    try {
      parent::__construct($this->getTwig(), $this->getContext());
    } catch (BaselineTemplateException $e) {
      throw new TemplateException($e->getMessage(), $e->getCode(), $e);
    }
  }
  
  /**
   * Returns the twig file for this template after confirming that it exists
   * within this theme.
   *
   * @return string
   * @throws HandlerException
   * @throws TemplateException
   * @throws TransformerException
   */
  private function getTwig(): string
  {
    $twig = $this->getTemplateTwig();
    if (!isset($this->findTwigs()[$twig])) {
      throw new TemplateException('Unknown template: ' . $twig,
        TemplateException::UNKNOWN_TWIG);
    }
    
    return $twig;
  }
  
  /**
   * Returns the name of the twig file for this template.
   *
   * @return string
   */
  protected function getTemplateTwig(): string
  {
    // the template object's name has the word Template at the end of it.  we
    // don't want that because it's not a part of our twig filenames.  we'll
    // remove that, convert the otherwise PascalCase object names to
    // kebab-case, and add the twig file extension to that result before
    // returning it as a part of our Timber @templates namespace.
    
    $twig = str_replace('Template', '', $this->template);
    $twig = $this->pascalToCamelCase($twig) . '.twig';
    return '@templates/' . $twig;
  }
  
  /**
   * Returns an array of twig filenames located within this theme.
   *
   * @return array
   * @throws HandlerException
   * @throws TransformerException
   */
  private function findTwigs(): array
  {
    // in a production environment, we want to avoid a filesystem search as
    // much as possible.  so, if we're not debugging, and it's not a new
    // version of this theme, then we'll assume that the list of twigs is the
    // same as last time we searched for them.
    
    if (!self::isDebug() && !$this->isNewThemeVersion()) {
      return $this->getOption('twigs', []);
    }
    
    $directory = new RecursiveDirectoryIterator(     // get all files
      get_stylesheet_directory() . '/assets/twigs/', // in or under this folder
      FilesystemIterator::SKIP_DOTS                  // skipping . and ..
    );
    
    $files = new RegexIterator(                      // limit results
      new RecursiveIteratorIterator($directory),     // within this iterator
      '/.twig$/',                                    // to .twig files
      RegexIterator::MATCH,                          // keeping only matches
      RegexIterator::USE_KEY                         // based on iterator keys
    );
    
    // now, we convert our iterator to an array and get its keys; these are
    // the paths to each twig file. (the values are the SplFileInfo objects; we
    // don't need those.)  and, if we're on Windows, we do a quick change to
    // the directory separator
    
    $twigs = array_keys(iterator_to_array($files));
    
    if (str_starts_with(PHP_OS, 'WIN')) {
      array_walk($twigs, fn(&$twig) => $twig = str_replace('\\', '/', $twig));
    }
    
    // our map here splits the full path names based on the folder in this
    // theme that contains our twigs.  then, everything after that folder with
    // the Timber namespace prefix will match the twig files that our templates
    // want to use.  finally, we flip the array to do an O(1) lookup for files
    // instead of O(N) searches.
    
    $twigs = array_flip(
      array_map(
        fn($twig) => '@' . explode('assets/twigs/', $twig)[1],
        $twigs
      )
    );
    
    $this->updateOption('twigs', $twigs);
    return $twigs;
  }
  
  /**
   * Returns true if the theme version in the database isn't the same as the
   * one in the style.css file.
   *
   * @return bool
   * @throws TransformerException
   * @throws HandlerException
   */
  protected function isNewThemeVersion(): bool
  {
    $knownVersion = $this->getOption('version');
    $currentVersion = wp_get_theme()->get('Version');
    $isNewVersion = $knownVersion !== $currentVersion;
    
    // if this is a new version, we want to update the known version of the
    // theme in the database.  we do this quickly here so that the next time
    // we get back to this method we don't re-do the work to check for new
    // template files.
    
    if ($isNewVersion) {
      $this->updateOption('version', $currentVersion);
    }
    
    return $isNewVersion;
  }
  
  /**
   * Returns an array of information that we pass to Timber so that it can use
   * it while compiling our templates into valid HTML.
   *
   * @return array
   * @throws HandlerException
   * @throws TemplateException
   * @throws TransformerException
   */
  private function getContext(): array
  {
    // the site context is the same for all pages throughout on the site.  it
    // includes things like the main menu or the copyright year.  the page
    // context is specific to the current request, so it'll be filled with page
    // content and other information that changes between one request and the
    // next.
    
    $siteContext = $this->getSiteContext();
    $pageContext = $this->getPageContext($siteContext);
    return array_merge($siteContext, ['page' => $pageContext]);
  }
  
  /**
   * Returns information that's global, i.e. it's the same throughout the site.
   *
   * @return array
   * @throws HandlerException
   * @throws TemplateException
   * @throws TransformerException
   */
  private function getSiteContext(): array
  {
    return [
      'year'     => date('Y'),
      'home'     => is_front_page(),
      'twig'     => basename($this->getTwig(), '.twig'),
      'template' => $this->template,
      'debug'    => self::isDebug(),
      'time'     => new TimeOfDay()->toArray(),
      'song'     => (new Playlist()->tracks[0] ?? new Song([]))->toArray(),
      'books'    => new CurrentlyReading()->toArray(),
      'site'     => [
        'url'    => home_url(),
        'title'  => 'David Dashifen Kees',
        'images' => get_stylesheet_directory_uri() . '/assets/images/',
        'logo'   => [
          'alt' => 'a witch\'s hat with a purple band and a gold buckle',
          'src' => 'witch-hat.png',
        ],
      ],
      'menus'    => [
        'main'   => $this->getMenu('main'),
        'footer' => $this->getMenu('footer'),
        'admin'  => is_admin_bar_showing(),
      ],
    ];
  }
  
  /**
   * Returns an array of MenuItem objects that define our menu.
   *
   * @param string $menuLocation
   *
   * @return array
   */
  private function getMenu(string $menuLocation): array
  {
    if (!has_nav_menu($menuLocation)) {
      return [];
    }
    
    // if we're here, then we have a menu for the specified location.  we
    // start by getting the Timber version of that menu.  but, this includes a
    // massive amount of additional information that we don't care about at
    // this time.  therefore, we convert these Timber menu items into our own
    // MenuItem repositories which filters these data keeping only what we
    // need.
    
    $menuItems = Timber::get_menu($menuLocation)->get_items();
    return array_map(fn($item) => new MenuItem($item), $menuItems);
  }
  
  /**
   * Returns an array of information necessary for the compilation of a
   * specific request.
   *
   * @param array $siteContext
   *
   * @return array
   */
  abstract protected function getPageContext(array $siteContext): array;
  
  /**
   * Compiles either a previously set template file and context or can use
   * the optional parameters here to specify the file and context at the time
   * of the call and returns it to the calling scope.
   *
   * @param bool        $debug
   * @param string|null $file
   * @param array|null  $context
   *
   * @return string
   * @throws TemplateException
   */
  public function compile(bool $debug = false, ?string $file = null, ?array $context = null): string
  {
    if (($file ??= $this->file) === null) {
      throw new TemplateException(
        'Cannot compile without a twig file.',
        TemplateException::UNKNOWN_TWIG
      );
    }
    
    if (($context ??= $this->context) === null) {
      throw new TemplateException(
        "Cannot compile without a template's context.",
        TemplateException::UNKNOWN_CONTEXT
      );
    }
    
    if ($debug || self::isDebug()) {
      $siteContext = array_filter($context, fn($key) => $key !== 'page', ARRAY_FILTER_USE_KEY);
      $context['page']['context'] = print_r($siteContext, true);
    }
    
    return Timber::compile($file, $context);
  }
  
  /**
   * Returns the prefix that is used to differentiate the options for this
   * handler's sphere of influence from others.
   *
   * @return string
   */
  public function getOptionNamePrefix(): string
  {
    return Theme::getPrefix();
  }
  
  /**
   * getOptionNames
   *
   * Returns an array of valid option names for use within the isOptionValid
   * method.
   *
   * @return array
   */
  protected function getOptionNames(): array
  {
    return ['twigs', 'version'];
  }
  
  /**
   * Used by our children to get a post's content.
   *
   * @return string
   */
  protected function getContent(): string
  {
    $content = new WP_HTML_Tag_Processor(
      apply_filters('the_content', get_the_content())
    );
    
    // for reasons Dash hasn't figured out yet, the content filters applied
    // above are cramming width and height attributes onto images making them
    // all stretchy and weird.  so, we'll look for images and remove them.
    // there's probably a better way to do this, and maybe they'll even look
    // for it someday!
    
    while($content->next_tag()) {
      if ($content->get_tag() === 'IMG') {
        $content->remove_attribute('width');
        $content->remove_attribute('height');
      }
    }
    
    return (string) $content;
  }
}
