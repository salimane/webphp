<?php


// sample program

require 'init.php';

$urls = array(
              '#^$#'      => array('hello', 'home'),
              '#^named/(?P<namedparam>[a-zA-Z_]+)/?$#' => array('named', 'index'),
             );


class hello {

    function home()
    {
        echo 'Welcome to webphp';

        /*
        // gets a reference to an Inspekt _GET object, clean up your variables!
        $input = Web::get();
        */
        // test your get vars
        /*
        if ($email = $input->testEmail('email')) {
            // wow emai is valid!
            ... do something ...
        }
        */

        // or
        // $vars['message'] = 'requested via get';
        // echo Web::render("name-of-file.html", $vars);
    }
}



class named {
    function index($p)
    {
        var_dump($p);
        echo "this is a captured var from the URI: ".$p['namedparam'];
    }
}


try {
    Web::run($urls);
} catch (RequestErrorException $e) {
    // errorCode gives you the 404 or 500 code etc.
    // echo $e->errorCode();
    // viewError will print out a basic 404 page
    // catch the RequestErrorException and do whatever you want.
    // viewError will send a header() to the browser fyi.
    $e->ViewError();
}
