<?php
namespace Kampernet\Magic\Base\Event;

/**
 * Event class
 *
 * @package Kampernet\Magic\Base\Event
 */
class Event {

	public static $events = array();

	/**
	 * binds event listeners to event handlers
	 *
	 * @param string $event
	 * @param Ambiguous <string, Closure> $callback
	 * @param Listener $obj
	 */
	public static function bind($event, $callback, $obj = null) {

		if (!self::$events[$event]) {
			self::$events[$event] = array();
		}

		self::$events[$event][] = ($obj === null) ? $callback : array($obj, $callback);
	}

	/**
	 * executes an event handle
	 *
	 * @param string $event
	 */
	public static function run($event) {

		if (!self::$events[$event]) {
			return;
		}

		foreach (self::$events[$event] as $callback) {
			if (call_user_func($callback) === false) {
				break;
			}
		}
	}
}