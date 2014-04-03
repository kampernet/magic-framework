<?php
namespace Kampernet\Magic\Aspect;

use Kampernet\Magic\Base\Aspect\AspectInterface;
use Kampernet\Magic\Base\Model\ConnectionProvider;
use Kampernet\Magic\Base\Model\Model;

/**
 * injects the data access implementation to model objects
 *
 * @package Kampernet\Magic\Aspect
 */
class DataAccessAspect implements AspectInterface {

	/**
	 * @match before.*Init
	 */
	public function injectConnection() {

		/**
		 * @var Model $this
		 */
		$this->setDAO(ConnectionProvider::getInstance()->getConnection());
	}

}