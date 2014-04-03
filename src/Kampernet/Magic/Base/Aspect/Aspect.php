<?php
namespace Kampernet\Magic\Base\Aspect;

use Exception;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use stdClass;
use Kampernet\Magic\Base\Util\AnnotationsParser;

/**
 * this is a class for applying Aspect Oriented Programming in PHP
 * it does this by maintaining an internal array of the lines of code
 * and eval()'ing the modified ( ie: aspects injected ) code and instantiating
 * an internal object.
 *
 * it then provides access to that objects methods, with advice applied at the
 * appropriate pointcuts, via __call.
 *
 * please note this class relies on the original class being well formatted code to run.
 * it must be multi-line code and have the class endbraces on different lines than the
 * method endbraces.  the method endbraces must be on their own line.
 *
 * what I mean to say is it doesn't use the tokenizer
 *
 * @package Kampernet\Magic\Base\Aspect
 */
class Aspect {

	const POINTCUT_BEFORE = "before";

	const POINTCUT_AFTER = "after";

	/**
	 * this is the object place holder for what will become
	 * the new object.  the modified class ( with advice added )
	 *
	 * @var stdClass
	 */
	private $object;

	/**
	 * the className you are adding advice to
	 *
	 * @var string
	 */
	private $className;

	/**
	 * the generated classname of the compiled code
	 *
	 * @var string
	 */
	private $compiledClassName;

	/**
	 * the array of method names you want to inject the adivce into
	 *
	 * @var array
	 */
	private $methodNames;

	/**
	 * an array for storing the advice methodNames.
	 * used when we are creating overrides for aspecting
	 *
	 * @var array
	 */
	private $adviceMethodNames;

	/**
	 * the array of aspect objects
	 *
	 * @var array
	 */
	private $advice;

	/**
	 * the new class code that gets made from the className supplied
	 *
	 * @var string
	 */
	private $compiledClassCode;

	/**
	 * the array that temporarily holds the lines of code
	 *
	 * @var array
	 */
	private $classCodeArray;

	/**
	 * the object of this singleton class
	 *
	 * @var Aspect
	 */
	private static $instance;

	/**
	 * @return Aspect
	 */
	public static function createInstance() {

		return new Aspect();
	}

	/**
	 * private constructor
	 */
	private function __construct() {
	}

	/**
	 * set the classname.  or don't.  I'm a comment not a cop.
	 *
	 * @param string $className
	 * @return Aspect
	 */
	public function setClassName($className) {

		$this->className = $className;
		$this->compiledClassName = $className;

		return $this;
	}

	/**
	 * sets the method name.  if not called, or set, it'll just use the first method called
	 * if set to the string "all" will apply all methods of the class given
	 *
	 * @param mixed $methodNames
	 * @return Aspect
	 */
	public function setMethodNames($methodNames) {

		if (is_array($methodNames)) {
			$this->methodNames = $methodNames;
		} elseif ($methodNames == "all") {
			// set all the methodNames
			$this->methodNames = array();
			$class = new ReflectionClass($this->className);
			$methods = $class->getMethods();
			foreach ($methods as $method) {
				array_push($this->methodNames, $method->getName());
			}
		}

		return $this;
	}

	/**
	 * set the advice.  or don't.  I'm a comment not a cop.
	 *
	 * @param array $advice an array of AspectInterface objects
	 * @return Aspect
	 */
	public function setAdvice(array $advice = null) {

		$this->advice = ($advice) ? $advice : array();

		return $this;
	}

	/**
	 * return the object property
	 *
	 * @return stdClass
	 */
	public function getObject() {

		$this->prepareObject();

		return $this->object;
	}

	/**
	 * retruns the compiled code for debug purposes
	 */
	public function getCompiledCode() {

		return $this->compiledClassCode;
	}

	/**
	 * compiles the aspected code
	 *
	 * @throws Exception
	 * @return Aspect
	 */
	public function compile() {

		$this->adviceMethodNames = array();

		$class = new ReflectionClass($this->className);
		$fileName = $class->getFileName();
		$lines = file($fileName);

		// get all the code of the class into a variable
		$this->classCodeArray [] = $class->getDocComment() . PHP_EOL;
		for ($i = $class->getStartLine() - 1; $i < $class->getEndLine(); $i++) {
			$this->classCodeArray [] = $lines[$i];
		}

		// override the method with the advice
		if (!isset($this->methodNames)) {
			$this->setMethodNames("all");
		}

		foreach ($this->methodNames as $origMethodName) {
			// loop through applied aspects
			foreach ($this->advice as $advice) {

				if (!$advice instanceof AspectInterface) {
					continue;
				} // a tiny bit of security, as we're using eval, this might just help make sure ppl know what they're doing
				$method = $class->getMethod($origMethodName); // note this throws an exception too

				// use reflection to get applicable methods to this class and method
				$adviceClass = new ReflectionClass($advice);
				$this->applyBeforeAdvice($class, $method, $adviceClass);
				$this->applyAfterAdvice($class, $method, $adviceClass);
			}
		}

		// replace $className with md5(rand())_$className
		$this->compiledClassName = $this->className . "_" . md5(rand());
		$this->compiledClassCode = str_replace($this->className, $this->compiledClassName, implode("", $this->classCodeArray));

		return $this;
	}

	/**
	 * use magic set to di the object
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 */
	public function __set($name, $value) {

		$this->prepareObject();
		$this->object->$name = $value;
	}

	/**
	 * why am I doing this again?
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {

		$this->prepareObject();
		$x = null;
		if (isset($this->object->$name)) {
			$x = $this->object->$name;
		}

		return $x;
	}

	/**
	 * use magic __call to implement the weave.
	 * this will only work on user classes, not
	 * built in PHP classes.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {

		$this->prepareObject();

		return call_user_func_array(array($this->object, $name), $arguments);
	}

	/**
	 * method to instantiate the advice applied object
	 *
	 * @throws Exception
	 * @return void
	 */
	private function prepareObject() {

		if (!isset($this->object)) {
			// instantiate the newly modified class and run the called method
			$evalCode = eval($this->compiledClassCode);
			if ($evalCode === false) {
				throw new Exception('could not evaluate the newly generated code');
			}
			$className = $this->compiledClassName;
			$this->object = new $className();
			$this->object->setOriginalClassName($this->className);
		}
	}

	/**
	 * put the code in the classCodeArray
	 *
	 * @param string $code
	 * @param int $index
	 */
	private function injectCode($code, $index) {

		array_splice($this->classCodeArray, $index, 0, $code);
	}

	/**
	 * get the index in the code array for where to insert code
	 * before the function ( when applying before advice )
	 *
	 * @param string $methodName
	 * @return mixed
	 */
	private function getBeforeIndex($methodName) {

		$index = false;
		for ($i = 0; $i < count($this->classCodeArray); $i++) {
			// regex for methodname
			$pattern = '/.+function([\s]+)' . $methodName . '([\s]*)\(.*\)/';
			if (preg_match($pattern, $this->classCodeArray[$i])) {
				$index = $i + 1;
				break;
			}
		}

		return $index;
	}

	/**
	 * get the index in the code array for where to insert code
	 * at the end of the function ( when applying after advice )
	 *
	 * for conditional returns in original method, advice will be ignored.
	 *
	 * if your return is the very last line of code in the method and is not conditional
	 * then the advice will be injected before that return statment
	 *
	 * if the last line of code in the method is not a return statement, the advice will
	 * be injected at the end of the method
	 *
	 * @param string $methodName
	 * @return mixed
	 */
	private function getAfterIndex($methodName) {

		$index = $this->getBeforeIndex($methodName); // where does this method start
		if ($index !== false) {
			$nextMethod = false;
			// now get the start of the NEXT method declaration
			for ($i = $index; $i < count($this->classCodeArray); $i++) {
				// regex for ANY methodname ( note this SHOULD NOT match Closures )
				$pattern = '/.+function([\s]+)([A-Za-z0-9_]+)([\s]*)\(.*\)/';
				if (preg_match($pattern, $this->classCodeArray[$i])) {
					$nextMethod = $i;
					break;
				}
			}
			// just in case it's the last method we're injecting code into
			if ($nextMethod === false) {
				$nextMethod = count($this->classCodeArray) - 2;
			} // -2 to make sure we get rid of class end brace

			// get the end brace line for this function
			$endBrace = false;
			for ($i = $nextMethod; $i >= $index; $i--) {
				if (trim($this->classCodeArray[$i]) == "}") { // get first curly
					$endBrace = $i;
					break;
				}
			}

			// now go backwords from $endBrace to $index and look for a return statement
			$returnStatement = false;
			for ($i = $endBrace; $i >= $index; $i--) {
				// if it's a blank line or a return then we use it's position
				if (trim($this->classCodeArray[$i]) == "" || $this->startsWith(trim(strtolower($this->classCodeArray[$i])), "return")) {
					$returnStatement = $i;
					break;
				}
			}

			$index = ($returnStatement !== false) ? $returnStatement : $endBrace;
		}

		return $index;

	}

	/**
	 * the timeless classic
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	private function startsWith($haystack, $needle) {

		$haystack = substr($haystack, 0, strlen($needle));

		return (strcmp($haystack, $needle) == 0);
	}

	/**
	 * gets the advice code to apply
	 *
	 * @param ReflectionClass $adviceClass
	 * @param ReflectionMethod $adviceMethod
	 * @return string
	 */
	private function getAdviceCode(ReflectionClass $adviceClass, ReflectionMethod $adviceMethod) {

		// get the contents of the advice method
		$adviceFile = $adviceClass->getFileName();
		$adviceLines = file($adviceFile);
		$adviceCode = "";

		for ($i = $adviceMethod->getStartLine(); $i < $adviceMethod->getEndLine() - 1; $i++) {
			$adviceCode .= $adviceLines[$i];
		}

		return $adviceCode;
	}

	/**
	 * generates the method signature for method overriding
	 *
	 * @param ReflectionMethod $method
	 * @return string
	 */
	private function generateMethodSignature(ReflectionMethod $method) {

		$signature = implode(' ', Reflection::getModifierNames($method->getModifiers())) . " function ";
		$signature .= $method->getName() . "(";

		$haz_params = false;
		$params = $method->getParameters();
		foreach ($params as $param) {
			// we haz paramz
			$haz_params = true;
			$paramName = "$" . $param->getName();
			if ($param->isOptional()) {
				$paramName .= " = " . $param->getDefaultValue();
			}
			$signature .= $paramName . ", ";
		}

		if ($haz_params) {
			$signature = substr($signature, 0, -2);
		}

		$signature .= ") { 
		";

		return $signature;
	}

	/**
	 * generates the method invoking call for the parent class
	 * when we're adding advice to an inherited method
	 *
	 * @param ReflectionMethod $method
	 * @return string
	 */
	private function generateMethodInvoking(ReflectionMethod $method) {

		$invoke = $method->getName() . "(";

		$haz_params = false;
		$params = $method->getParameters();
		foreach ($params as $param) {
			// we haz paramz
			$haz_params = true;
			$paramName = $param->getName();
			$invoke .= "$" . $paramName . ", ";
		}

		if ($haz_params) {
			$invoke = substr($invoke, 0, -2);
		}

		$invoke .= ");
		";

		return $invoke;
	}

	/**
	 * apply any before advice to the method
	 *
	 * @param ReflectionClass $class
	 * @param ReflectionMethod $method
	 * @param ReflectionClass $adviceClass
	 */
	private function applyBeforeAdvice(ReflectionClass $class, ReflectionMethod $method, ReflectionClass $adviceClass) {

		$adviceMethods = array();
		$methods = $adviceClass->getMethods();
		foreach ($methods as $advice) {
			$name = $advice->getName();
			$info = AnnotationsParser::getAnnotations($advice);
			// preg match
			if (isset($info['match']) && preg_match("/" . strtolower($info['match']) . "/", strtolower(Aspect::POINTCUT_BEFORE . $this->className . $method->getName()))) {
				$adviceMethods [] = $advice;
			}
			// exact match
			if ($name == Aspect::POINTCUT_BEFORE . $this->className . $method->getName()) {
				$adviceMethods [] = $advice;
			}
		}

		foreach ($adviceMethods as $adviceMethod) {
			$adviceCode = $this->getAdviceCode($adviceClass, $adviceMethod);

			// handle inherited methods
			if ($method->getDeclaringClass()->getName() != $class->getName() &&
				!in_array($method->getName(), $this->adviceMethodNames)
			) { // and not already override generated
				$signature = $this->generateMethodSignature($method);
				$invoke = $this->generateMethodInvoking($method);

				$random = "var" . md5(rand());
				$adviceCode = array($signature . "\n", $adviceCode, "\$$random = parent::$invoke\n", "return \$$random;\n", "}\n", "\n");
				$index = count($this->classCodeArray) - 1; // had to change as per adding new array element on the classCodeArray for the doc comments

				array_push($this->adviceMethodNames, $method->getName());

				foreach ($adviceCode as $code) {
					$this->injectCode($code, $index);
					$index++;
				}
			} else {
				// add the advice to the new class method code
				$index = $this->getBeforeIndex($method->getName());
				$this->injectCode($adviceCode, $index);
			}
		}
	}

	/**
	 * applies the advice after the method
	 *
	 * @param ReflectionClass $class
	 * @param ReflectionMethod $method
	 * @param ReflectionClass $adviceClass
	 * @throws Exception
	 */
	private function applyAfterAdvice(ReflectionClass $class, ReflectionMethod $method, ReflectionClass $adviceClass) {

		$adviceMethods = array();
		$methods = $adviceClass->getMethods();
		foreach ($methods as $advice) {
			$name = $advice->getName();
			$info = AnnotationsParser::getAnnotations($advice);

			// preg match
			if (isset($info['match']) && preg_match("/" . strtolower($info['match']) . "/", strtolower(Aspect::POINTCUT_AFTER . $this->className . $method->getName()))) {
				$adviceMethods [] = $advice;
			}
			// exact match
			if ($name == Aspect::POINTCUT_AFTER . $this->className . $method->getName()) {
				$adviceMethods [] = $advice;
			}
		}

		foreach ($adviceMethods as $adviceMethod) {
			$adviceCode = $this->getAdviceCode($adviceClass, $adviceMethod);

			if ($method->getDeclaringClass()->getName() != $class->getName() &&
				!in_array($method->getName(), $this->adviceMethodNames)
			) { // and not already override generated

				$signature = $this->generateMethodSignature($method);
				$invoke = $this->generateMethodInvoking($method);

				$random = "var" . md5(rand());
				$adviceCode = array($signature . "\n", "\$$random = parent::$invoke\n", $adviceCode, "return \$$random;\n", "}\n", "\n");
				$index = count($this->classCodeArray) - 2;

				array_push($this->adviceMethodNames, $method->getName());

				foreach ($adviceCode as $code) {
					$this->injectCode($code, $index);
					$index++;
				}
			} else {
				$index = $this->getAfterIndex($method->getName());
			}

			$this->injectCode($adviceCode, $index);
		}
	}
}