<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities\Library;

use Dashifen\Exception\Exception;
use Dashifen\WordPress\Themes\Dashifen2025\Entities\AbstractEntity;

class Book extends AbstractEntity
{
  /**
   * @var string the title of the book.
   */
  protected(set) string $title;
  
  /**
   * @var Contributor[] people involved in the production of the book.
   */
  protected(set) array $people = [];
  
  /**
   * @var int number of pages in the book.
   */
  protected(set) int $pages = 0;
  
  /**
   * @var int number of pages Dash has read in the book.
   */
  protected(set) int $progress = 0;
  
  /**
   * @var string percent complete based on the prior two properties
   */
  public string $percent {
    get {
      return $this->progress && $this->pages
        ? round($this->progress / $this->pages * 100) . '%'
        : '0%';
    }
  }
  
  /**
   * @var string cover art for the book.
   */
  protected(set) string $image;
  
  
  public function __construct(object $book)
  {
    // the $book parameter is data directly from the Hardcover.app API.  if
    // they change things on their end, it'll mess with our code here, but
    // hopefully that won't happen too often.
    
    $this->title = $book->book->title ?? '';
    $this->pages = $book->book->pages ?? 0;
    $this->progress = $book->user_book_reads[0]->progress_pages ?? 0;
    $this->image = $book->book->cached_image->url
      ?? 'https://assets.hardcover.app/static/covers/cover' . mt_rand(1,9) . '.png';
    
    $contributors = [];
    foreach ($book->book->cached_contributors as $contributor) {
      $contributors[] = new Contributor($contributor);
    }
    
    $this->people = $this->organizeContributors($contributors);
  }
  
  /**
   * Arranges contributors by their role in sets.
   *
   * @param array $contributors
   *
   * @return Contributor[]
   */
  private function organizeContributors(array $contributors): array
  {
    $organized = [];
    foreach ($contributors as $contributor) {
      
      // this first loop takes each of our contributors and adds them to arrays
      // based on their role.  afterward, all of the authors will be in an
      // array at the "author" index, translators at the "translator" index,
      // and so on.
      
      $organized[strtolower($contributor->role)][] = $contributor->name;
    }
    
    // now, for the screen, we want to take arrays of people and turn them into
    // easily printable strings based on the number of people in each array.
    // notice the use of the Oxford comma as is right and proper.
    
    foreach ($organized as $role => $people) {
      $count = sizeof($people);
      $organized[$role] = match ($count) {
        1       => $people[0],
        2       => $people[0] . ' and ' . $people[1],
        default => join(', ', array_slice($people, $count - 1)) . ', and ' . array_pop($people),
      };
    }
    
    return $organized;
  }
  
  /**
   * Returns an array of our object properties.
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'title'    => $this->title,
      'pages'    => $this->pages,
      'progress' => $this->progress,
      'percent'  => $this->percent,
      'image'    => $this->image,
      'people'   => $this->people,
    ];
  }
}
