<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\DTOs\Time;

use stdClass;
use JsonException;
use Dashifen\DTO\DTO;

class SolarTime extends DTO
{
  protected(set) int $sunrise;
  protected(set) int $sunset;
  protected(set) int $tomorrow;
  private array $decoded = [];
  
  /**
   * SolarTime constructor.
   *
   * @param array|null $today
   * @param array|null $tomorrow
   */
  public function __construct(?array $today = null, ?array $tomorrow = null)
  {
    // this information in our extractions are strings, so when we have null
    // parameters, we also use a string.  then, for each of our properties we
    // pass either that string, '0', or what we extract from our parameters
    // through strtotime to store timestamps in this object.
    
    parent::__construct([
      'sunrise'  => strtotime($today !== null ? $this->extractSunrise($today) : '0'),
      'sunset'   => strtotime($today !== null ? $this->extractSunset($today) : '0'),
      'tomorrow' => strtotime($tomorrow !== null ? $this->extractSunrise($tomorrow) : '0'),
    ]);
  }
  
  /**
   * Decodes the specified HTTP response and returns the sunrise property of
   * it.
   *
   * @param array $day
   *
   * @return string
   */
  private function extractSunrise(array $day): string
  {
    return $this->getProperty($day, 'sunrise');
  }
  
  /**
   * Gets a property of our decoded HTTP response.
   *
   * @param array  $response
   * @param string $property
   *
   * @return string
   */
  private function getProperty(array $response, string $property): string
  {
    // md5 does collide, but the likelihood that it does so for us seems so
    // astronomically small that we're not going to worry about it.
    
    $hash = md5(serialize($response));
    $json = !isset($this->decoded[$hash])
      ? $this->decodeHttpResponse($response, $hash)
      : $this->decoded[$hash];
    
    return $json->results->{$property} ?? '0';
  }
  
  /**
   * Given an HTTP response, decodes its JSON body into a stdClass, stores that
   * object in the decode cache property, and then returns it.
   *
   * @param array  $response
   * @param string $hash
   *
   * @return stdClass
   */
  private function decodeHttpResponse(array $response, string $hash): stdClass
  {
    try {
      $json = wp_remote_retrieve_body($response);
      $json = json_decode(json: $json, flags: JSON_THROW_ON_ERROR);
      
      // so we only have to decode information once, we store the json object
      // in our decode cache.  likely the work necessary isn't too great, but
      // there's no reason to do it if we don't have to.  then, since the
      // assignment operator returns the data assigned by side effect, we can
      // return the results of that caching assignment.
      
      return $this->decoded[$hash] = $json;
    } catch (JsonException) {
      
      // if we run into a JSON exception, we catch it here and return an empty
      // class.  this satisfies our type hint and allows our calling scope to
      // use null coalescing operators to set reasonable defaults for missing
      // properties.
      
      return new stdClass();
    }
  }
  
  /**
   * Decodes the specified HTTP response and returns the sunset property of it.
   *
   * @param array $day
   *
   * @return string
   */
  private function extractSunset(array $day): string
  {
    return $this->getProperty($day, 'sunset');
  }
}
