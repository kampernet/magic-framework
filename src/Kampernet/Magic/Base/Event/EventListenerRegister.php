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
					$listeners[(string) $l->attributes()->listenFor] = (string) $l->attributes()->listener;
				}
			}
		}

		foreach ($listeners as $event => $listener) {
			if ($listener instanceof Closure) {
				Event::bind($event, $listener);
			} else {
				$split = explode("::", $event);
				$model = $split[0];

				$split2 = explode("::", $listener);
				$lclass = $split2[0];
				$lmethod = $split2[1];

				$x = ($d->$model instanceof Aspect) ? $d->$model->getObject() : $d->$model;
				$l = new $lclass($x);
				Event::bind($event, $lmethod, $l);
			}
		}
	}
}