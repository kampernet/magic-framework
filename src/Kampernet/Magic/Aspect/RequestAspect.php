<?php
namespace Kampernet\Magic\Aspect;

use Kampernet\Magic\Base\Aspect\AspectInterface;
use Kampernet\Magic\Base\Request;
use Kampernet\Magic\Base\Model\Model;

/**
 * injects the request object onto the model
 *
 * @package Kampernet\Magic\Aspect
 */
class RequestAspect implements AspectInterface {

	/**
	 * @match before.*Init
	 */
	public function injectRequest() {

		/**
		 * @var Model $this
		 */
		$this->setRequest(Request::getInstance());
	}

}