<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities\Playlist;

use WP_Error;
use JsonException;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Entities\AbstractEntity;

class Playlist extends AbstractEntity
{
  /**
   * @var Song[]
   */
  protected(set) array $tracks;
  
  /**
   * Constructs a playlist with a number of tracks given as the argument.
   * We default to 60 because our "shelf" CSS shows rows of six at full
   * size, so 60 is 10 full rows.
   */
  public function __construct(int $tracks = 60)
  {
    $transient = Theme::SLUG . '-playlist';
    
    if (!($playlist = get_transient($transient))) {
      if (($playlist = $this->getPlaylist($tracks)) !== null) {
        
        // the first if-condition checks the database for cached results from
        // the last.fm API.  if we couldn't get those data, we'll hit the
        // API and try to get new data.  if we were able to do so, we store
        // them in the database here.
        
        set_transient($transient, $playlist, 3 * MINUTE_IN_SECONDS);
      }
    }
    
    $this->tracks = is_array($playlist) ? $playlist : [];
  }
  
  /**
   * Hits the last.fm API to receive recent track information for Dash's
   * account.
   *
   * @param int $tracks
   *
   * @return array|null
   */
  protected function getPlaylist(int $tracks): ?array
  {
    // when we decode the JSON returned to us from the last.fm API, we pass the
    // JSON_THROW_ON_ERROR flag so it'll throw a JsonException object instead
    // of relying on the various JSON error message functions.  that way we can
    // also throw exceptions if we run into other errors.
    
    try {
      $response = $this->getApiResponse($tracks);
      if (wp_remote_retrieve_response_code($response) !== 200) {
        throw new JsonException('Could not retrieve data from the last.fm API.');
      }
      
      $response = wp_remote_retrieve_body($response);
      $json = json_decode(json: $response, flags: JSON_THROW_ON_ERROR);
      if (sizeof($json->recenttracks->track ?? []) === 0) {
        throw new JsonException('No data retrieved from the last.fm API.');
      }
      
      $playlist = [];
      foreach ($json->recenttracks->track as $track) {
        
        // notice that the null coalescing operator will produce the same
        // value for these arguments as the Song constructor uses as the
        // default values for its properties.  changes here (or there) should
        // be reflected in both places.
        
        $playlist[] = new Song(
          $track->name ?? '',
          $track->album->{'#text'} ?? '',
          $track->artist->{'#text'} ?? '',
         ($track->{'@attr'}->nowplaying ?? 'false') === 'true' ?? false,
          $track->image[3]->{'#text'} ?? '',
        );
      }
    } catch (JsonException) {
      $playlist = null;
    }
    
    return $playlist;
  }
  
  /**
   * Hits the Last.fm API and gets my most recent track listing there.
   *
   * @param int $tracks
   *
   * @return array|null
   */
  private function getApiResponse(int $tracks): ?array
  {
    $query = [
      'api_key' => LAST_FM_API_KEY,
      'method'  => 'user.getrecenttracks',
      'user'    => 'ddkees',
      'limit'   => $tracks,
      'format'  => 'json',
    ];
    
    $response = wp_remote_get(
      'http://ws.audioscrobbler.com/2.0/?' . http_build_query($query)
    );
    
    return !is_a($response, WP_Error::class)
      ? $response
      : null;
  }
  
  /**
   * Returns our playlist as an array.
   *
   * @return array
   */
  public function toArray(): array
  {
    return array_map(fn($track) => $track->toArray(), $this->tracks);
  }
}
