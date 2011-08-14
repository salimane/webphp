<?php
/**
 * put anything here you want loaded before web.php
 * any config stuff should be in this file
 *
 * @author Salimane Adjao Moustapha
 */
define("SITE_BASE", __DIR__);
define("MODELS_DIR", SITE_BASE.DIRECTORY_SEPARATOR ."modules");
define("CONTROLLERS_DIR", SITE_BASE.DIRECTORY_SEPARATOR."controllers");

// layouts
define('VIEWS_DIR', SITE_BASE.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'templates');
define('LAYOUT_DIR', SITE_BASE.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts');

define("WEBPHP_DIR", '/home/salimane/htdocs/webphp/webphp');

global $model_classes;
$model_classes = array(
);
