<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Templates;

use JsonException;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Entities\Library\Book;
use Dashifen\WordPress\Themes\Dashifen2025\Entities\Library\CurrentlyReading;

class ReadingTemplate extends DefaultTemplate
{
  /**
   * Returns an array of information necessary for the compilation of a
   * specific twig template.
   *
   * @param array $siteContext
   *
   * @return array
   */
  protected function getPageContext(array $siteContext): array
  {
    
    return array_merge(parent::getPageContext($siteContext), [
      'library' => [
        'current' => [
          'heading' => "Books I'm Currently Reading",
          'books'   => $siteContext['books'],
        ],
        'annual'  => [
          'heading' => "Books I've Read in " . date('Y'),
          'books'   => $this->getAnnualLibrary(),
        ],
      ]
    ]);
  }
  
  /**
   * Uses the Hardcover.app API to get my library for this year.
   *
   * @return array
   */
  private function getAnnualLibrary(): array
  {
    $transient = Theme::getPrefix() . 'library';
    $databaseData = get_transient($transient);
    return true || $databaseData === false
      ? $this->fetchApiData($transient)
      : $databaseData;
    
  }
  
  /**
   * If we need to get new data from the Hardcover.app API, this method
   * does so.
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
    
    $response = CurrentlyReading::fetch($this->getQuery());
    if (wp_remote_retrieve_response_code($response) !== 200) {
      return [];
    }
    
    try {
      $books = [];
      $rawBooks = json_decode(wp_remote_retrieve_body($response), flags: JSON_THROW_ON_ERROR);
      foreach ($rawBooks->data->me[0]->user_books as $book) {
        $book = new Book($book)->toArray();
        unset($book['pages'], $book['progress'], $book['percent']);
        $books[] = $book;
      }
    } catch (JsonException) {
      return [];
    }
    
    set_transient($transient, $books, WEEK_IN_SECONDS);
    return $books;
  }
  
  /**
   * Returns the graphQL query that we bounce off of the Hardcover.app
   * API.
   *
   * @return string
   */
  private function getQuery(): string
  {
    $date = ($year = date('Y')) !== '2025'
      ? $year . '-01-01'    // start at the first of the year, unless...
      : '2025-02-08';       // it's 2025; then, Feb 7 is when I ran my import
    
    return <<< QUERY
      {
        me {
          user_books(
            where: {status_id: {_eq: 3}, updated_at: {_gte: "$date"}}
            order_by: {last_read_date: desc}
          ) {
            book {
              cached_image
              cached_contributors
              title
            }
          }
        }
      }
    QUERY;
  }
}
