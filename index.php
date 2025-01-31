<?php

namespace Dashifen\WordPress\Themes;

use Exception;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;
use Dashifen\WordPress\Themes\Dashifen2025\Router;
use Dashifen\WordPress\Themes\Dashifen2025\Templates\Framework\TemplateFactory;

defined('ABSPATH') || die;

(function () {
  
  // by defining these variables in this anonymous function, we avoid adding
  // them to the global WordPress scope which prevents naming collisions and
  // access to any of an object's public methods.
  
  try {
    $templateName = new Router()->getTemplateObjectName();
    $templateObject = TemplateFactory::produceTemplate($templateName);
    $templateObject->render();
  } catch (Exception $e) {
    Theme::catcher($e);
  }
})();
