<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities;

class Person extends AbstractEntity
{
  protected(set) string $name;
  protected(set) string $role;
  
  public function __construct(object $person)
  {
    $this->name = $person->author->name ?? '';
    $this->role = !empty($person->contribution ?? '')
      ? $person->contribution
      : 'Author';
  }
  
  /**
   * Returns an array containing our object properties.
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'name' => $this->name,
      'role' => $this->role,
    ];
  }
  
  
}
