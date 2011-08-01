<?php

/**
* WebFlash 
* you can use this class to save messages between
* requests, you must have session_start called before using this
*/

class Flash
{
    private $session = array();
    private $old = array();
    
    function __construct()
    {
        if (array_key_exists('flash', $_SESSION)) {
            foreach ($_SESSION['flash'] as $key => $value) 
            {
                $this->session[$key] = $value;
                $this->old[$key] = $value;
            }
            unset($_SESSION['flash']);
        }
    }
    
    function __set($key, $value)
    {
        $this->session[$key] = $value;
    }
    
    function __get($key)
    {
        if (isset($this->session[$key])) {
            return $this->session[$key];
        }
    }
    
    function __isset($key)
    {
        return array_key_exists($key, $this->session);
    }
    
    function __unset($key)
    {
        unset($this->session[$key]);
    }
    
    function &instance()
    {
        static $instance;
        if (!is_object($instance)) {
            $instance = new Flash();
        }
        return $instance;
    }
    
    public static function get($key)
    {
        $i = self::instance();
        return $i->$key;
    }
    
    public static function add($key, $value=null)
    {
        $i = self::instance();
    	if ( !is_null($value) ) {
    	    $i->$key = $value;
    	} else {
    	    if (isset($i->$key)) {
    	       return $i->key;
    	    }
    	}
    } 
    
    // if you don't like add blargh....
    public static function set($key, $value=null)
    {
        $i = self::instance();
    	if ( !is_null($value) ) {
    	    $i->$key = $value;
    	} else {
    	    if (isset($i->$key)) {
    	       return $i->key;
    	    }
    	}
    }

    // trash flash keys
    public static function discard($keys)
    {
        $i = self::instance();
    	if ( is_array($keys) ){
    		foreach( $keys as $key )
    		{
    		    unset($i->$key);
    		}
    	} else {
    	    unset($i->$key);
    	}
    }

    // trash all flash keys except those requested by flash_keep
    function __destruct()
    {
    	$set_new = array_diff_key($this->session, $this->old);
    	foreach ($set_new as $key => $value) 
    	{
    	   $_SESSION['flash'][$key] = $this->session[$key];
    	}
    }
}