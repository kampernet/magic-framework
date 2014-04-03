<?php
namespace Kampernet\Magic\Aspect;

use Kampernet\Magic\Base\Aspect\AspectInterface;
use Kampernet\Magic\Base\Model\RestfulModelInterface;
use Kampernet\Magic\Base\Model\Model;

/**
 * makes a model object REST aware
 *
 * @package Kampernet\Magic\Aspect
 */
class RestfulMethodMappingAspect implements AspectInterface, RestfulModelInterface {

	/**
	 * @match before.*__default
	 */
	public function makeRestAware() {

		/**
		 * @var Model $this
		 */
		$this->populate($this->getRequest()->params);

		$map = array(
			'POST' => 'save',
			'PUT' => 'save',
			'DELETE' => 'delete',
			'GET' => 'fetch',
			'HEAD' => 'head',
			'OPTIONS' => 'options',
			'TRACE' => 'trace',
			'CONNECT' => 'connect'
		);

		$method = $map[$this->getRequest()->server['REQUEST_METHOD']];

		$this->$method();
	}

}