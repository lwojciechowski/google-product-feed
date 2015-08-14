<?php

/**
* Made with ❤ by nostrzak
*
* Super simple templating engine based on php files.
*/

namespace Chucky\Tool;

class Template {

	private $vars = array();
	private $view_template_file;


	/**
	 * Construct the object providing template file path
	 *
	 * @param $template Template file path
	 */
	public function __construct($template)
	{
		$this->view_template_file = $template;
	}

	/**
	 * Magic method for getting template variable
	 *
	 * @param mixed $name parameter name
	 * @return mixed parameter value
	 */
	public function __get($name) 
	{
		return $this->vars[$name];
	}

	/**
	 * Magic method for setting template variable
	 *
	 * @param mixed $name parameter name
	 * @param mixed $value parameter value
	 */
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}

	/**
	 * Display template (php file) providing only values from $this->vars template
	 *
	 * @return string processed template
	 */
	public function render()
	{
		if(!file_exists($this->view_template_file)) {
			throw new Exception("Template does not exits.");	
		}
		
		extract($this->vars, EXTR_SKIP);
		ob_start();

		include($this->view_template_file);

		return ob_get_clean();
	}
}