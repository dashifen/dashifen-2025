<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\DTOs\Library;

use Dashifen\DTO\DTO;

class Book extends DTO
{
  protected(set) string $title;
  protected(set) string $image;
  protected(set) int $pages = 0;
  protected(set) int $progress = 0;
  public string $percent {
    get {
      return $this->progress && $this->pages
        ? round($this->progress / $this->pages * 100) . '%'
        : '0%';
    }
  }
  
  /**
   * @var Contributor[] typically authors, but also other contributors.
   */
  protected(set) array $people = [];
  
  /**
   * Given data from the Hardcover.app API, arranges it in our preferred
   * format.
   *
   * @param object $book
   */
  public function __construct(object $book)
  {
    parent::__construct([
      'title'    => $book->book->title ?? '',
      'pages'    => $book->book->pages ?? 0,
      'progress' => $book->user_book_reads[0]->progress_pages ?? 0,
      'image'    => $book->book->cached_image->url ?? 'https://assets.hardcover.app/static/covers/cover' . mt_rand(1, 9) . '.png',
      'people'   => $this->organizeContributors($book),
    ]);
  }
  
  /**
   * Arranges contributors by their role in sets.
   *
   * @param object $book
   *
   * @return Contributor[]
   */
  private function organizeContributors(object $book): array
  {
    $roles = [];
    foreach ($book->book->cached_contributors as $contributor) {
      $contributor = new Contributor($contributor);
      $roles[strtolower($contributor->role)][] = $contributor->name;
    }
    
    // now, for the screen, we want to take arrays of people and turn them into
    // easily printable strings based on the number of people in each array.
    // notice the use of the Oxford comma as is right and proper.
    
    foreach ($roles as $role => $people) {
      $count = sizeof($people);
      $roles[$role] = match ($count) {
        1       => $people[0],
        2       => $people[0] . ' and ' . $people[1],
        default => join(', ', array_slice($people, $count - 1)) . ', and ' . array_pop($people),
      };
    }
    
    return $roles;
  }
}
