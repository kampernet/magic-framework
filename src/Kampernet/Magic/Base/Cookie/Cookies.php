<?php
namespace Kampernet\Magic\Base\Cookie;

/**
 * This is the cookie wrapper class. It allows us to obfuscate the setting
 * of bundled cookies.
 *
 * The purpose of bundled cookies is to reduce the number of cookies that
 * the website sets, in order to not hit the browser limit of 20. It is
 * best to keep the bundling logical, by type of cookie in particular.
 *
 * @package Kampernet\Magic\Base\Cookie
 */
class Cookies {

	/**
	 * Instance of the cookies class. It's a singleton
	 *
	 * @var Cookies
	 */
	private static $_instance;

	const DEFAULT_BUNDLE_COOKIE_EXPIRY_TIME = 31556926; //One year

	/**
	 * private constructor for singleton
	 */
	private function __construct() {

		foreach ($_COOKIE as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * get the instance
	 *
	 * @return Cookies
	 */
	public static function getInstance() {

		if (!isset(self::$_instance)) {
			self::$_instance = new Cookies();
		}

		return self::$_instance;
	}

	/**
	 * Sets the cookie $name $value with default values for path and expiry and domain.
	 * If the cookie being set is one of our bundled cookies, it sets the new bundled
	 * value to the Cookies object, and then sets the new bundled value to the bundled cookie.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {

		setcookie($name, $value, time() + Cookies::DEFAULT_BUNDLE_COOKIE_EXPIRY_TIME, '/');
	}

	/**
	 * magic get to work with cookies
	 *
	 * @param string $name
	 * @return string
	 */
	public function &__get($name) {

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
	}

	/**
	 * checks the COOKIES superglobal
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {

		return isset($_COOKIE[$name]);
	}

	/**
	 * Uses the regular setcookie method if it is appropriate. Or calls the magic set.
	 *
	 * @param string $name
	 * @param string $value
	 * @param bool $expire
	 * @param string $path
	 * @param string $domain
	 */
	public function setCookie($name, $value, $expire = false, $path = '/', $domain = null) {

		if (!$expire) {
			$expire = time() + Cookies::DEFAULT_BUNDLE_COOKIE_EXPIRY_TIME;
		}
		setcookie($name, $value, $expire, $path, $domain);
	}

	/**
	 * Unsets cookies even if they are in a bundle.
	 *
	 * @param string $name
	 * @param string $path
	 * @param null $domain
	 */
	public function unsetCookie($name, $path = '/', $domain = null) {

		setcookie($name, '', time() - 36000, $path, $domain);
	}

}