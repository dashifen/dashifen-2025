<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates;

use Dashifen\WordPress\Themes\Dashifen2025\DTOs\Article;
use Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework\AbstractTemplate;

class BlogTemplate extends AbstractTemplate
{
  /**
   * Returns an array containing data for this template's context that is
   * merged with the default data to form the context for the entire request.
   *
   * @param array $siteContext
   *
   * @return array
   */
  protected function getPageContext(array $siteContext): array
  {
    // in the AbstractTemplate constructor, we make sure that on the blog page
    // the post ID property has the value of the page that's showing the blog
    // and not the first post in the loop.  that way, we can get the page's
    // title for the start of things and then switch to post headings
    // thereafter.
    
    $post = get_post($this->postId);
    
    return [
      'title'       => $post->post_title,
      'entry_title' => $post->post_title,
      'content'     => apply_filters('the_content', $post->post_content),
      'articles'    => array_map(fn($p) => new Article($p), get_posts()),
    ];
  }
}
