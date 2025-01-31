<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities;

class Book extends AbstractEntity
{
  /**
   * @var string the title of the book.
   */
  protected(set) string $title;
  
  /**
   * @var Person[] people involved in the production of the book.
   */
  protected(set) array $people = [];
  
  /**
   * @var int number of pages in the book.
   */
  protected(set) int $pages;
  
  /**
   * @var int number of pages Dash has read in the book.
   */
  protected(set) int $progress;
  
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
    $this->image = $book->book->cached_image->url;
    
    foreach ($book->book->cached_contributors as $contributor) {
      $this->people[] = new Person($contributor);
    }
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
      'image'    => $this->image,
      'people'   => array_map(fn($person) => $person->toArray(), $this->people),
    ];
  }
}
