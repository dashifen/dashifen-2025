<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities\Library;

use WP_Error;
use JsonException;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Entities\AbstractEntity;

/**
 * The CurrentlyReading service object reaches out to the Hardcover.app API
 * and grabs information about what Dash is currently reading and their
 * progress.
 */
class CurrentlyReading extends AbstractEntity
{
  /**
   * @var Book[] the books Dash is currently reading.
   */
  protected(set) array $books;
  
  public function __construct()
  {
    $transient = Theme::getPrefix() . 'currently-reading';
    $databaseData = get_transient($transient);
    $this->books = $databaseData === false
      ? $this->fetchApiData($transient)
      : $databaseData;
  }
  
  /**
   * Returns data from the Hardcover.app API.
   *
   * @param string $transient
   *
   * @return array
   */
  private function fetchApiData(string $transient): array
  {
    if (!defined('HARDCOVER_API_KEY')) {
      return [];
    }
    
    $response = self::fetch($this->getQuery());
    if (wp_remote_retrieve_response_code($response) !== 200) {
      return [];
    }
    
    try {
      $currentlyReading = json_decode(wp_remote_retrieve_body($response), flags: JSON_THROW_ON_ERROR);
    } catch (JsonException) {
      return [];
    }
    
    $books = [];
    $bookData = $currentlyReading->data->me[0]->user_books ?? [];
    foreach ($bookData as $book) {
      $books[] = new Book($book);
    }
    
    set_transient($transient, $books, DAY_IN_SECONDS);
    return $books;
  }
  
  /**
   * Given a GraphQL query, bounces it off the Hardcover.app API.
   *
   * @param string $query
   *
   * @return array|WP_Error
   */
  public static function fetch(string $query): array|WP_Error
  {
    return wp_remote_post('https://api.hardcover.app/v1/graphql', [
      'body'    => json_encode(['query' => $query]),
      'headers' => [
        'content-type'  => 'application/json',
        'authorization' => HARDCOVER_API_KEY,
      ],
    ]);
  }
  
  /**
   * Returns the GraphQL query that gets Dash's current books.
   *
   * @return string
   */
  private function getQuery(): string
  {
    return <<<QUERY
      {
        me {
          user_books(where: {status_id: {_eq: 2}}, order_by: {updated_at: desc}) {
            book {
              cached_image
              cached_contributors
              pages
              title
            }
            user_book_reads {
              progress
              progress_pages
            }
          }
        }
      }
    QUERY;
  }
  
  public function toArray(): array
  {
    return array_map(fn($book) => $book->toArray(), $this->books);
  }
}
