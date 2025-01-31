<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities;

use Dashifen\WPDebugging\WPDebuggingTrait;

abstract class AbstractEntity
{
  use WPDebuggingTrait;
  
  abstract public function toArray(): array;
}
