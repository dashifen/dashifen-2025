<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\DTOs\Library;

use Dashifen\DTO\DTO;

class Contributor extends DTO
{
  protected(set) string $name;
  protected(set) string $role;
  
  public function __construct(object $person)
  {
    parent::__construct([
      'name' => $person->author->name ?? '',
      'role' => !empty($person->contribution ?? '')
        ? $person->contribution
        : 'Author',
    ]);
  }
}
