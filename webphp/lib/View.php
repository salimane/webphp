<?php
/**
 * View class to render templates
 * @author Salimane Adjao Moustapha
 */
class View {

  public $viewvars;

  function __construct() {
  }

  function __set($name, $value) {
    $this->viewvars[$name] = $value;
  }

  function __get($name) {
    return isset($this->viewvars[$name]) ? $this->viewvars[$name] : NULL;
  }

  /**
   * Render a script content
   * @param  $script
   * @return the rendered output string
   * @author Salimane Adjao Moustapha
   */
  function render($script) {
    ob_start();
    include $script;
    $html = ob_get_clean();
    return $html;
  }

}
