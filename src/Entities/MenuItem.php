<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities;

use Dashifen\Exception\Exception;
use Timber\MenuItem as TimberMenuItem;

/**
 * The MenuItem entity receives information from Timber menu items and keeps
 * only the information we need after cramming it into our properties.
 */
class MenuItem extends AbstractEntity
{
  /**
   * @var string the URL to which this menu item links
   */
  protected(set) string $url {
    set {
      if (!filter_var($value, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid url: ' . $value);
      }
      
      $this->url = $value;
    }
  }
  
  /**
   * @var string the on-screen label for this menu item.
   */
  protected(set) string $label;
  
  /**
   * @var array the class list for this menu item.
   */
  protected(set) array $classes = [] {
    set {
      
      // typically, setting a property would obliterate prior values.  but,
      // this time we don't want to do that.  instead, we just merge the new
      // values into the existing ones, make sure the list remains unique, and
      // that there are no empty indices in our list.
      
      $this->classes = array_filter(array_unique(array_merge($this->classes, $value)));
    }
  }
  
  /**
   * @var array the children for this menu item, i.e. its submenu
   */
  protected(set) array $children = [] {
    set {
      $mapper = fn(TimberMenuItem $child) => new MenuItem($child);
      $this->children = array_map($mapper, $value);
    }
  }
  
  /**
   * @var bool true when this menu item should appear current
   */
  protected(set) bool $current {
    set {
      $this->current = $value;
      
      // in addition to remembering our Boolean in case it's handy on the
      // server side, for the client side, we want to add a class to each menu
      // item so that they can get different styles as needed.  notice that the
      // set hook for the classes property below merges new values into the old
      // ones, so this doesn't obliterate other pre-existing classes even
      // though an assignment would typically do so.
      
      $currentClass = $value ? 'is-current' : 'is-not-current';
      $this->classes = [$currentClass];
    }
  }
  
  /**
   * Given a TimberMenuItem, extracts what we need and "discards" the rest of
   * the information.
   *
   * @param TimberMenuItem $item
   */
  public function __construct(TimberMenuItem $item)
  {
    $this->url = $item->url;
    $this->label = $item->name();
    $this->classes = $item->classes;
    $this->children = $item->children;
    $this->current = $item->current
      || $item->current_item_ancestor
      || $item->current_item_parent;
  }
  
  /**
   * Returns our properties as an array.
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'url'      => $this->url,
      'label'    => $this->label,
      'classes'  => $this->classes,
      'children' => $this->children,
      'current'  => $this->classes,
    ];
  }
}
