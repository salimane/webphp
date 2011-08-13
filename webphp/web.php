<?php

include_once 'exceptions/RequestErrorException.php';

/**
* web loader
*/

class Web
{
    protected $_baseURLPath     = '/';
    protected $_urls            = null;
    protected $_requestUrl      = null;
    public    $params           = null;
    protected $_debug           = false;
    protected $_debugMessages   = array();
    protected $_layoutFile      = null;
    protected $_layoutDir       = null;
    protected $_useLayout       = false;
    protected $_renderedText    = null;
    protected $_tpl             = null;
    static protected $_plugins  = array();
    static protected $_instance = NULL;


    /**
     * create instance of Web object. Only use this method
     * to get an instance of Web.
     *
     * @author Salimane Adjao Moustapha
     */

    public static function &instance()
    {
        if (self::$_instance == NULL) {
            self::$_instance = new Web();
        }
        return self::$_instance;
    }


    /**
     * requestUri
     *
     * inspects $_SERVER['REQUEST_URI'] and returns a sanitized
     * path without a leading/trailing slashes
     * @return void
     * @author Salimane Adjao Moustapha
     */

    private function requestUri()
    {
        // have seen apache set either or.
        $uri = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL']
                                               : $_SERVER['REQUEST_URI'];

        // sanitize it
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        // kill query string off REQUEST_URI
        if ( strpos($uri,'?') !== false ) {
               $uri = substr($uri,0,strpos($uri,'?'));
        }

        // strip off first slash
        if(substr($uri, 0, 1) == "/"){
            $uri = substr($uri, 1);
        }

        // knock off last slash
        if (substr($uri, -1) == "/") {
            $uri = substr($uri, 0, -1);
        }

        $this->request_uri = $uri;
    }

    /**
     * send 303 by default so browser won't cache the 200 or 301 headers
     *
     * @param string $location
     * @param string $status
     * @return void
     * @author Salimane Adjao Moustapha
     */

    public static function redirect($location, $status=303)
    {
        self::httpHeader($status);
        header("Location: $location");
    }

    /**
     * send header code to browser
     *
     * @param string $code
     * @return void
     * @author Salimane Adjao Moustapha
     */

    public static function httpHeader($code)
    {
        $http = array (
               100 => "HTTP/1.1 100 Continue",
               101 => "HTTP/1.1 101 Switching Protocols",
               200 => "HTTP/1.1 200 OK",
               201 => "HTTP/1.1 201 Created",
               202 => "HTTP/1.1 202 Accepted",
               203 => "HTTP/1.1 203 Non-Authoritative Information",
               204 => "HTTP/1.1 204 No Content",
               205 => "HTTP/1.1 205 Reset Content",
               206 => "HTTP/1.1 206 Partial Content",
               300 => "HTTP/1.1 300 Multiple Choices",
               301 => "HTTP/1.1 301 Moved Permanently",
               302 => "HTTP/1.1 302 Found",
               303 => "HTTP/1.1 303 See Other",
               304 => "HTTP/1.1 304 Not Modified",
               305 => "HTTP/1.1 305 Use Proxy",
               307 => "HTTP/1.1 307 Temporary Redirect",
               400 => "HTTP/1.1 400 Bad Request",
               401 => "HTTP/1.1 401 Unauthorized",
               402 => "HTTP/1.1 402 Payment Required",
               403 => "HTTP/1.1 403 Forbidden",
               404 => "HTTP/1.1 404 Not Found",
               405 => "HTTP/1.1 405 Method Not Allowed",
               406 => "HTTP/1.1 406 Not Acceptable",
               407 => "HTTP/1.1 407 Proxy Authentication Required",
               408 => "HTTP/1.1 408 Request Time-out",
               409 => "HTTP/1.1 409 Conflict",
               410 => "HTTP/1.1 410 Gone",
               411 => "HTTP/1.1 411 Length Required",
               412 => "HTTP/1.1 412 Precondition Failed",
               413 => "HTTP/1.1 413 Request Entity Too Large",
               414 => "HTTP/1.1 414 Request-URI Too Large",
               415 => "HTTP/1.1 415 Unsupported Media Type",
               416 => "HTTP/1.1 416 Requested range not satisfiable",
               417 => "HTTP/1.1 417 Expectation Failed",
               500 => "HTTP/1.1 500 Internal Server Error",
               501 => "HTTP/1.1 501 Not Implemented",
               502 => "HTTP/1.1 502 Bad Gateway",
               503 => "HTTP/1.1 503 Service Unavailable",
               504 => "HTTP/1.1 504 Gateway Time-out"
           );
        header($http[$code]);
    }


    /**
     * inspect urls, find matched class and then run requested method
     *
     * @param string $array
     * @param string $_baseURLPath
     * @return void
     * @author Salimane Adjao Moustapha
     */

    public static function run(array $urls)
    {
        if (empty($urls)) {
            throw new Exception("You must pass an array of valid urls to web::run()");
            return;
        }

        // get instance of Web
        $instance = self::instance();

        // process the request uri
        $instance->requestUri();

        foreach ($urls as $url_path => $options) {
            if (preg_match($url_path, $instance->request_uri, $matches)) {
                // assuming pattern => class in URLS array
                if (is_string($options)) {
                    $saved = $options;
                    $options = array();
                    $options['module'] = $saved;
                    unset($saved);
                }

                if ($options) {
                    $route = array_merge($matches, $options);
                } else {
                    $route = $matches;
                }
                unset($matches);

                $instance->params = $route;
                break;
            }
        }

        // if there is no uri match - module not at least set, throw a 404 error.
        if (!array_key_exists('module', $route)) {
            throw new RequestErrorException("Page not found.", 404);
            return;
        }

        // lets check matches for named patterns
        // be aware this is somewhat tied into __autoload function
        $class_to_load = $route['module'];

        // now check that module exists:
        // finds it based on __autoload function
        if (!class_exists($class_to_load)) {
            throw new RequestErrorException("Page not found.", 404);
            return;
        }

        // ok now check if there was an 'action', if action is named index
        // it just loads the forementioned module
        if (array_key_exists('class', $route)
            && $route['class'] != ''
            && $route['class'] != $route['module']) {
            // do an index check.
            if ($route['class'] != 'index') {
                $class_to_load = $route['class'];
            }

            // now check that the requested action class exists,
            // this assumes that it has been included because of the module
            // check from above.
            if (!class_exists($class_to_load)) {
                throw new RequestErrorException("Page not found.", 404);
                return;
            }
        }

        // instantiate class
        $loaded_class = new $class_to_load();

        // see if class has any pre-run hooks
        if (method_exists($loaded_class, 'preRun')) {
            $retval = $loaded_class->preRun();

            // if pre-run hook returns false, stop processing.
            if($retval === false) {
                return;
            }
        }

        // check for class method based on REQUEST_METHOD
        $method = $_SERVER['REQUEST_METHOD'];

        // ajax hook
        if ($instance->isAjaxRequest()) {
            $method = "AJAX";
        }

        // whitelist of allowed method
        if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'AJAX'))) {
            throw new RequestErrorException("HTTP Method not supported.", 405);
            return;
        }

        // see if currently loaded class even supports it
        if (!method_exists($loaded_class, $_SERVER['REQUEST_METHOD'])) {
            throw new RequestErrorException("HTTP Method not supported by class.", 405);
            return;
        }

        // run request
        $loaded_class->$method();

        // see if class has any post-run hooks
        if (method_exists($loaded_class, 'postRun')) {
            $retval = $loaded_class->postRun();

            // if post-run hook returns false, stop processing.
            if($retval === false) {
                return;
            }
        }
    }


    /**
     * inspect headers to see if request is of ajax variety
     *
     * @return void
     * @author Salimane Adjao Moustapha
     */

    private function isAjaxRequest()
	{
	    return ( isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	             && $_SERVER['HTTP_X_REQUESTED_WITH']  == 'XMLHttpRequest');
	}


	/**
	 * getTemplate
	 * creates instance of template object
	 *
	 * @return object tpl instance
	 * @author Salimane Adjao Moustapha
	 */

	public function &getTemplate()
	{
	    if (!$this->_tpl) {
	        require_once 'lib/Savant3/Savant3.php';
            $this->_tpl = new Savant3();
	    }
        return $this->_tpl;
	}

    /**
     * use savant3 to render template from a file
     * echo Web::render('template.html', $tplvars);
     *
     * @param string $file
     * @param array $tpl_vars
     * @return void
     * @author Salimane Adjao Moustapha
     */

    public static function render($file, array $tpl_vars=null)
    {
        $instance = self::instance();
        $_tpl = $instance->getTemplate();

        // the only real magic, its really for convenice.
        // if you included the class via autoload, it will try and look
        // in an expected TEMPLATES_DIR for the file to render.
        if (defined('TEMPLATES_DIR')) {
            $_tpl->addPath('template', TEMPLATES_DIR);
        }

        // assign template vars by copy?
        // if you need to assign something by reference to the tpl, just do this:
        // Web::instance()->getTemplate()->assignRef('name','value);
        if($tpl_vars) {
            $_tpl->assign($tpl_vars);
        }

        $output = $_tpl->fetch($file);

        // check for template errors
        if ($_tpl->isError($output)) {
            return "Failed to load template: ".basename($file);
        }

        // wrap content in layout. if its being used
        if ($instance->_useLayout && $instance->_layoutFile) {
            $_tpl->addPath('template', $instance->_layoutDir);
            $_tpl->assign('content_for_layout', $output);
            $output = $_tpl->fetch($instance->_layoutFile);
            // check for template errors
            if ($_tpl->isError($output)) {
                return "Failed to load layout template: ".basename($instance->_layoutFile);
            }
        }

        return $output;
    }

   /**
     * useLayout - sets the layout file to use when rendering
     * if set to false, then layout rendering is bypassed
     * echo Web::layout('file.php')->render('template.html', $tplvars);
     *
     * @param mixed $file
     * @return void
     * @author Salimane Adjao Moustapha
     */
    public static function layout($file=null,$dir=null)
    {
        $instance = self::instance();

        // quick switch, turn off layout if $file is FALSE
        if ($file === false) {
            $instance->_useLayout = false;
            return;
        }
        $_tpl = $instance->getTemplate();

        // will check this dir first
        if (defined('LAYOUT_DIR')) {
            $instance->setLayoutDirectory(LAYOUT_DIR);
        }

        // if dir is set, it will overide
        if ($dir !== null) {
            $instance->setLayoutDirectory($dir);
        }

        if ($file !== null) {
            $instance->useLayout($file);
        }
        $instance->_useLayout  = true;

        return $instance;
    }

    /**
     * setLayoutDirectory
     * sets the directory path of layouts
     *
     * @param string $dir
     * @return void
     * @author Salimane Adjao Moustapha
     */
    public function setLayoutDirectory($dir)
    {
        $this->_layoutDir = $dir;
        return $this;
    }

    /**
     * useLayout
     *
     * @param string $filename
     * @return $this
     * @author Salimane Adjao Moustapha
     */
    public function useLayout($filename)
    {
        if($filename === false) {
            $this->_layoutFile = null;
        } else {
            $this->_layoutFile = $filename;
            return $this;
        }
    }

    /**
     * send a request to a url and get back the response
     * useful for background requests
     * dependent upon allow_url_fopen being turned on
     * http://us3.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen
     * if allow_url_fopen is off, it will try curl
     *
     * @param string $url
     * @param array $data
     * @param string $method http method
     * @param array $optional_headers
     * @return string $response
     * @throws RequestErrorException 400 on bad request
     * @author Salimane Adjao Moustapha
     */

    public function request($url, array $data=null, $method='POST', array $optional_headers=null)
    {
        if (!$on = ini_get('allow_url_fopen')) {
            return self::curlRequest($url, $data, $method, $optional_headers);
        }
        $params = array('http'    => array(
                        'method'  => $method,
                        'content' => http_build_query($data)
                        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new RequestErrorException("Problem with $url, $php_errormsg", 400);
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new RequestErrorException("Problem reading data from $url, $php_errormsg", 400);
        }
        return $response;
    }

    /**
     * request a url via curl instead of fopen if allow_url_fopen is off
     * takes the same parameters as request, the is called by self::request()
     * if allow_url_fopen is off.
     *
     * @param string $url
     * @param array $data
     * @param string $method
     * @param string $optional_headers
     * @return string $response
     * @throws RequestErrorException
     * @author Salimane Adjao Moustapha
     */

    function curlRequest($url, array $data=null, $method='POST', array $optional_headers=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!is_null($optional_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
        }

        // check method
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if (!empty($data)) {
                $url .= '?'.http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RequestErrorException("$url not reachable", 400);
        }
        return $response;
    }

    /**
     * cache a string to the file system
     * its not overly robust, and if you need something super
     * awesome, use pear's Cache_Lite
     *
     * @param string $cache_id
     * @param string $content
     * @param string $cache_time
     * @param string $cache_dir
     * @return string $content
     * @author Salimane Adjao Moustapha
     */

    public function cache($cache_id, $content=null, $cache_time=3600, $cache_dir=null)
    {
        if (is_null($cache_dir)) {
            $cache_dir = sys_get_temp_dir();
        }

        if (!is_writable($cache_dir)) {
            return false;
        }

        $cache_id = md5($cache_id);

        if ($cached = self::isCached($cache_id, $cache_time, $cache_dir)) {
            return $cached;
        } else {
            // write to cache
            $fname = $cache_dir.'/'.$cache_id.'.cache';
            file_put_contents($fname, $content, LOCK_EX);
            return $content;
        }
    }

    /**
     * isCached
     * checks to see if given id is cached, and if so
     * returns that content
     *
     * @param string $cache_id
     * @param string $cache_time
     * @param string $cache_dir
     * @return void
     * @author Salimane Adjao Moustapha
     */
    public function isCached($cache_id, $cache_time, $cache_dir)
    {
        clearstatcache();
        $fname = $cache_dir.'/'.$cache_id.'.cache';
        if (!file_exists($fname)) {
            return false;
        }
        if ( (filemtime($fname) + $cache_time) < time() ) {
            unlink($fname);
            return false;
        } else {
            return file_get_contents($fname);
        }
    }

    /**
     * params
     *
     * @return stored web params from request_uri
     * @author Salimane Adjao Moustapha
     */
    public static function params()
    {
        return self::instance()->params;
    }

}


