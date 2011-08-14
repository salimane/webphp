<?php

/**
 * core classes
 */

global $core_classes;
$core_classes = array(
              'Web' => 'Web.php',
              'Base' => 'Base.php',
              'Template' => 'Template.php',
              'View' => 'View.php',
              'ViewVars' => 'ViewVars.php',
              'RequestErrorException' => 'RequestErrorException.php'
             );

/**
 * this will attempt to load a class from the modules directory
 * if using modules, each action class will need to be in the same
 * file as this parent class, and the index action will be the
 * name of the class
 *
 * @return void
 * @author Salimane Adjao Moustapha
 */

function __autoload($class_name) {
  global $model_classes, $core_classes;
  if (isset($core_classes[$class_name])) {
    include realpath(__DIR__ . DIRECTORY_SEPARATOR . 'lib' ) . DIRECTORY_SEPARATOR . $class_name.'.php';
  }
  elseif (isset($model_classes[$class_name])) {
    include MODELS_DIR . DIRECTORY_SEPARATOR . $class_name.'.php';
  }
  else {
    include CONTROLLERS_DIR . DIRECTORY_SEPARATOR . $class_name . '.php';
    !defined("TEMPLATES_DIR") && define("TEMPLATES_DIR", VIEWS_DIR . DIRECTORY_SEPARATOR . $class_name);
  }
}