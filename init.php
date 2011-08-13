<?php

/**
 * put anything here you want loaded before web.php
 * any config stuff should be in this file
 *
 * @author Salimane Adjao Moustapha
 */
define("SITE_BASE", dirname(__FILE__));
define("MODULES_DIR", SITE_BASE."/modules");

// include path
set_include_path( SITE_BASE . '/webphp/lib/' . PATH_SEPARATOR . get_include_path());

// layouts
define('LAYOUT_DIR', SITE_BASE . '/layouts');

/**
 * this will attempt to load a class from the modules directory
 * if using modules, each action class will need to be in the same
 * file as this parent class, and the index action will be the
 * name of the class
 *
 * @return void
 * @author Kenrick Buchanan
 */

function __autoload($class_name)
{
    $file = MODULES_DIR.'/'.$class_name.'/'.$class_name.'.php';
    if (file_exists($file)) {
        include_once $file;
        define('TEMPLATES_DIR', MODULES_DIR.'/'.$class_name.'/templates');
    }
}


include 'webphp/web.php';

