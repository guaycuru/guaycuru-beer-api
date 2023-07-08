<?php

namespace Util;

use Doctrine\ORM\EntityManager;
use Guaybeer\Entities\User;

abstract class Shared {
	private static $_EM;

	/**
	 * _EM
	 *
	 * Gets or Sets the Entity Manager
	 *
	 * @param EntityManager|null $EM (optional) Entity Manager
	 * @return EntityManager Entity Manager
	 */
	public static function _EM(EntityManager $EM = null): EntityManager {
		if ($EM != null)
			self::$_EM = $EM;
		return self::$_EM;
	}

	/**
	 * @param object $entity
	 * @return void
	 */
	public static function Persist_Or_Json_Unavailable(object $entity): void {
		try {
			self::_EM()->persist($entity);
		} catch(\Exception $e) {
			self::JSON_Service_Unavailable($e->getMessage(), ['trace' => $e->getTraceAsString()]);
		}
	}

	/**
	 * To_JSON
	 *
	 * Returns the input in JSON
	 *
	 * @param mixed $input The input oO
	 * @return string The JSON encoded output
	 */
	public static function To_JSON(mixed $input): string {
		return json_encode($input, JSON_FORCE_OBJECT & JSON_NUMERIC_CHECK);
	}

	/**
	 * OK_JSON
	 *
	 * Outputs a JSON entity and terminates the execution
	 *
	 * @param mixed $entity Entity
	 * @param integer $code (Optional) HTTP response code
	 * @return never
	 */
	public static function JSON_OK(mixed $entity = null, int $code = 200): never {
		http_response_code($code);
		die(self::To_JSON($entity));
	}

	/**
	 * Error_JSON
	 *
	 * Outputs a JSON error and terminates the execution
	 *
	 * @param string $message Error message
	 * @param integer $code (Optional) HTTP response code
	 * @param array $extra (Optional) Extra info to be sent with the response JSON
	 * @return never
	 */
	public static function JSON_Error(string $message, int $code = 500, array $extra = array()): never {
		http_response_code($code);
		die(self::To_JSON(array(
				'ok' => false,
				'message' => $message
			) + $extra));
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function JSON_Bad_Request(string $message = 'Bad Request', array $extra = array()): never {
		self::JSON_Error($message, 400, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function JSON_Forbidden(string $message = 'Forbidden', array $extra = array()): never {
		self::JSON_Error($message, 403, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function JSON_Not_Found(string $message = 'Not Found', array $extra = array()): never {
		self::JSON_Error($message, 404, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function JSON_Method_Not_Allowed(string $message = 'Method Not Allowed', array $extra = array()): never {
		self::JSON_Error($message, 405, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function JSON_Service_Unavailable(string $message = 'Service Unavailable', array $extra = array()): never {
		self::JSON_Error($message, 503, $extra);
	}

	/**
	 * Gets the user given by userUuid (if set) and check if access is allowed
	 *
	 * @param bool $defaultToNull
	 * @return User|null
	 */
	public static function getUserReturningIfForbidden(bool $defaultToNull = false): ?User {
		global $_user;

		if ($_user->isAdmin() === false) {
			if (!empty($_GET['userUuid']) && $_user->getUuid() !== $_GET['userUuid']) {
				self::JSON_Forbidden();
			} else {
				return $_user;
			}
		} elseif (!empty($_GET['userUuid'])) {
			return User::findByUuid($_GET['userUuid']);
		}

		return ($defaultToNull) ? null : $_user;
	}

}
