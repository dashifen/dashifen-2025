<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\DTOs;

use DOMDocument;
use Dashifen\DTO\DTO;
use WP_HTML_Tag_Processor;
use Dashifen\WPDebugging\WPDebuggingTrait;

class Article extends DTO
{
  use WPDebuggingTrait;
  
  protected(set) string $ID;
  protected(set) string $post_title;
  protected(set) string $post_content;
  
  public string $excerpt {
    get {
      
      // in honor of the finest band east of all points west, we will show 97
      // words as our excerpt.  the other work we do here is to try and make
      // sure that it ends in a letter or number to avoid the rare cases where
      // the excerpt would end in a mark of punctuation.
      
      $excerpt = wp_trim_words($this->post_content, 97, '');
      while (!preg_match('~\w$~', $excerpt)) {
        $excerpt = substr($excerpt, 0, -1);
      }
      
      return $excerpt . '&hellip;';
    }
  }
  
  public string $permalink {
    get {
      return $this->excerpt !== $this->post_content
        ? get_permalink($this->ID)
        : '';
    }
  }
}
