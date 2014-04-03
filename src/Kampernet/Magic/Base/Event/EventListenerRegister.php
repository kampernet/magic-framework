<?php
namespace Kampernet\Magic\Base\Event;

use Kampernet\Magic\Base\Application;
use Kampernet\Magic\Base\Delegator;
use Closure;
use Kampernet\Magic\Base\Aspect\Aspect;

/**
 * This class is used to register the event listeners
 *
 * @package Kampernet\Magic\Base\Event
 */
class EventListenerRegister {

	/**
	 * registers listeners and binds them to events
	 *
	 * @param Delegator $d
	 * @param array $listeners
	 */
	public static function registerEventListeners(Delegator $d, array $listeners = null) {

		if (!$listeners) {
			$listeners = array();
			$ls = Application::getInstance()->listeners;
			if ($ls->listener) {
				foreach ($ls->listener as $l) {
					$listeners[(string) $l->attributes()->listenFor] = (string) $l->attributes()->listenerMethod;
				}
			}
		}

		foreach ($listeners as $event => $listener) {
			if ($listener instanceof Closure) {
				Event::bind($event, $listener);
			} else {
				$split = explode("::", $event);
				$split = $split[0];
				$lclass = $split . "Listener";
				$x = ($d->$split instanceof Aspect) ? $d->$split->getObject() : $d->$split;
				$l = new $lclass($x);
				Event::bind($event, $listener, $l);
			}
		}
	}
}