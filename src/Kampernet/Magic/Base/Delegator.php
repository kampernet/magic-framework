<?php
namespace Kampernet\Magic\Base;

use Kampernet\Magic\Base\Event\Event;
use Kampernet\Magic\Base\Exception\NoSuchClassException;
use ReflectionClass, stdClass;
use Kampernet\Magic\Base\Util\AnnotationsParser;
use Kampernet\Magic\Base\Aspect\Aspect;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Delegator is the IoC container.
 * It works all the magic routing, injects
 * dependencies by recursively building
 * the object model graph and applies all
 * aspects to the models as well.
 *
 * @package Kampernet\Magic\Base
 */
class Delegator {

	private $_properties = array();

	/**
	 * the master method.  delegate the request
	 * to the right class method with parameters.
	 * returns the result of the method call
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function delegate(Request $request) {

		$args = $request->request->all();

		$uriParts = explode('?', $request->getRequestUri());

		$path = $uriParts[0];
		$path = trim($path, '/');

		($path) ? $pathParts = explode('/', urldecode($path)) : $pathParts = array();

		$method = array_pop($pathParts);
		$thing = implode('', $pathParts);

		// convert classname
		$classname_parts = explode('-', $thing);
		foreach ($classname_parts as &$part) {
			$part = ucfirst($part);
		}
		$classname = implode('', $classname_parts);

		// convert method name
		$x = explode("-", $method);
		for ($i = 1; $i < count($x); $i++) {
			$x[$i] = ucfirst($x[$i]);
		}
		$method = implode('', $x);

		Event::run($classname . '::onBefore' . ucfirst($method));
		$result = call_user_func_array(array($this->$classname, $method), $args);
		Event::run($classname . '::onAfter' . ucfirst($method));

		return $result;
	}

	/**
	 * instantiate the class with all aspects
	 * compiled in and all dependencies injected
	 *
	 * @param string $classname
	 * @throws NoSuchClassException
	 * @return mixed - an aspected and dependency injected object
	 */
	public function __get($classname) {

		if (!isset($this->_properties[$classname])) {
			if (class_exists($classname)) {

				// instantiate it and put it as a property of the delegator
				$this->_properties[$classname] = $this->getAspectedInstance($classname);
				$this->recurseObjectGraph($this->_properties[$classname]->getObject());
				$this->_properties[$classname]->init();

			} else {
				throw new NoSuchClassException("The class $classname was not found!");
			}
		}

		return $this->_properties[$classname];
	}

	/**
	 * recursively traverse the model object graph
	 * instantiating and aspecting and di'ing the
	 * object properties.
	 *
	 * @param stdClass $graph
	 */
	private function recurseObjectGraph($graph) {

		if (is_object($graph)) {
			$class = new ReflectionClass($graph);
			$properties = $class->getProperties();

			foreach ($properties as $property) {
				$b = AnnotationsParser::getAnnotations($property);
				$name = $property->getName();
				if (array_key_exists('model', $b)) {
					$object = $this->getAspectedInstance($b['model']);
					$this->recurseObjectGraph($object->getObject());
					$graph->$name = $object->getObject();
				}
			}

		}
	}

	/**
	 * aspects the instance as per is has annotations
	 *
	 * @param string $classname
	 * @return Aspect
	 */
	private function getAspectedInstance($classname) {

		// get the aspects this object has
		$c = new ReflectionClass($classname);
		$info = AnnotationsParser::getAnnotations($c);
		$aspects = null;
		if (array_key_exists('has', $info)) {
			$aspects = explode(",", $info['has']);
			foreach ($aspects as &$aspect) {
				$aspectclass = trim($aspect) . "Aspect";
				$aspect = new $aspectclass;
			}
		}

		return Aspect::createInstance()
			->setClassName($classname)
			->setAdvice($aspects)
			->compile();
	}

}