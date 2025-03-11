<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\DTOs\Playlist;

use Dashifen\DTO\DTO;

class Song extends DTO
{
  protected(set) string $name = '';       // the most recent song Dash played
  protected(set) string $album = '';      // the album on which that song resides
  protected(set) string $artist = '';     // the artist that produced that album
  protected(set) bool $current = false;   // is this song currently being played?
  protected(set) string $image = '' {     // the album art, when available
    set {
      $this->image = empty($value)
        ? get_stylesheet_directory_uri() . '/assets/images/compact-disc-solid.svg'
        : $value;
    }
  }
}
