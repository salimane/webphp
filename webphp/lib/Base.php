<?php
/**
 * Abstract Base for controllers classes to enable templating functions
 * @author Salimane Adjao Moustapha
 */
abstract class Base {

  //holder for Template, View instances
  protected $_t, $_view, $_path;

  /**
   * Constructor set new View & Template instances
   * @author Salimane Adjao Moustapha
   */
  function __construct() {
    header("Content-Type:text/html;charset=utf-8");
    $this->_t = new Template;
    $this->_view = $this->_t->view;
  }

  /**
   * Assign the content of a template file to a
   * variable in the rendered view
   * @param  $var
   * @param  $value
   * @return void
   * @author Salimane Adjao Moustapha
   */
  protected function template($var, $value) {
    $this->_view = $this->_t->view;
    $this->_t->assign($var, $value);
  }

  /**
   * Assign a value to a
   * variable in the rendered view
   * @param  $var
   * @param  $value
   * @return void
   * @author Salimane Adjao Moustapha
   */
  protected function templateValue($var, $value) {
    $this->_view = $this->_t->view;
    $this->_t->assignValue($var, $value);
  }

  /**
   * Assign multiple values to
   * variables in the rendered view
   * @param  $pairs $var => $value pairs
   * @return void
   * @author Salimane Adjao Moustapha
   */
  protected function templateValues($pairs) {
    $this->_view = $this->_t->view;
    foreach($pairs AS $var => $value){
      $this->_t->assignValue($var, $value);
    }
  }

  /**
   * Render the content of a template file
   * @param  $templateFile
   * @return void
   * @author Salimane Adjao Moustapha
   */
  protected function show($templateFile) {
    $this->_view = $this->_t->view;
    $this->_t->show(LAYOUT_DIR . DIRECTORY_SEPARATOR . $templateFile);
  }
}
