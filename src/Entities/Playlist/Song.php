<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities\Playlist;

use Dashifen\WordPress\Themes\Dashifen2025\Entities\AbstractEntity;

class Song extends AbstractEntity
{
  /**
   * Song constructor.
   *
   * The default values for our properties included herein should match
   * the values produced by the null coalescing statements in the
   * getSongData method of the Playlist object.  changes here (or there)
   * should be reflected in both places.
   */
  public function __construct(
    protected(set) string $name = '',       // the most recent song Dash played
    protected(set) string $album = '',      // the album on which that song resides
    protected(set) string $artist = '',     // the artist that produced that album
    protected(set) bool $current = false,   // is this song currently being played?
    protected(set) string $image = '' {    // the album art, when available
      set {
        $this->image = empty($value)
          ? get_stylesheet_directory_uri() . '/assets/images/compact-disc-solid.svg'
          : $value;
      }
    }
  ) {
  }
  
  /**
   * Returns our song data as an array.
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'name'    => $this->name,
      'album'   => $this->album,
      'artist'  => $this->artist,
      'image'   => $this->image,
      'current' => $this->current,
    ];
  }
}
