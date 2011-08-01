<?php 


// sample program

require 'init.php';

$urls = array(
              '#^$#'      => 'hello',
              '#^named/(?P<namedparam>[a-zA-Z_]+)/?$#' => 'named',
             );


class hello {        
    
    function GET($p)
    {
        echo 'Welcome to web-php';
        
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
    
    function POST($p)
    {        
        // like you just posted a form
        /*
        $input = Web::post();
        if ($email = $input->testEmail('email')) {
            // wow email is valid!
             save to db...
             Web::redirect('/gohere');            
        }
        */        
        
        echo 'request via POST';
    }  
    
    function AJAX($p)
    {
        echo "requested via AJAX";
    }                            
}                          

                                                             

class named {
    function GET($p)
    {
        var_dump($p);
        echo "this is a captured var from the URI: ".$p['namedparam'];
    }
}


try {
    /* debug?
    $i = Web::instance();
    $i->debug(true);
    */
    Web::run($urls); 
} catch (RequestErrorException $e) {
    // errorCode gives you the 404 or 500 code etc.
    // echo $e->errorCode();                       
    // viewError will print out a basic 404 page
    // catch the RequestErrorException and do whatever you want.
    // viewError will send a header() to the browser fyi.
    $e->ViewError();
}
