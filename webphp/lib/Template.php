<?php
/**
 * Template class taking care of the templating system
 * @author Salimane Adjao Moustapha
 */
class Template {

  //array holding the variables of the currently rendered template
  public $templateVars = array ();
  public $view = NULL;

  /**
   * Contructor seeting default variables needed for a new template
   * @author Salimane Adjao Moustapha
   */
  function __construct() {
    $this->view = new View;
  }

  /**
   * Set a variable with the content of a supllied template file for the current template
   * @param  $varName
   * @param  $value
   * @param  $request
   * @return void
   * @author Salimane Adjao Moustapha
   */
  function assign($varName, $value, $request = NULL) {
    $this->templateVars [$varName] = $this->view->render(TEMPLATES_DIR . DIRECTORY_SEPARATOR . $value);
  }

  /**
   * Set a variable with a specified value for the current template
   * @param  $varName
   * @param  $value
   * @param  $request
   * @return void
   * @author Salimane Adjao Moustapha
   */
  function assignValue($varName, $value, $request = NULL) {
    $this->view->$varName = $value;
    $this->templateVars [$varName] = $value;
  }

  /**
   * Set empty default values for variables if not avalaible
   * @param  $varname
   * @return void
   * @author Salimane Adjao Moustapha
   */
  function __get($varname){
    return isset($this->templateVars[$varname]) ? $this->templateVars[$varname] : '';
  }
  
  /**
   * magic function to avoid a bug in "empty()" function overloading __isset
   * @param  $varname
   * @return bool
   * @author Salimane Adjao Moustapha
   */
  function __isset($varname){
    return isset($this->templateVars[$varname]);
  }
  
  /**
   * Set variables for the current template and render the specified template
   * @param  $template
   * @return void
   * @author Salimane Adjao Moustapha
   */
  function show($template) {
    foreach($this->templateVars as $key => $value) {
      $this->$key = $value;
    }
    include $template;
  }


}
