<?php

namespace Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
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
	public static function persistOrJsonUnavailable(object $entity): void {
		try {
			self::_EM()->persist($entity);
		} catch(\Exception $e) {
			self::jsonServiceUnavailable($e->getMessage(), ['trace' => $e->getTraceAsString()]);
		}
	}

	/**
	 * @return void
	 */
	public static function flushOrJsonUnavailable(): void {
		try {
			Shared::_EM()->flush();
		} catch(\Exception $e) {
			Shared::jsonServiceUnavailable($e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
	public static function toJson(mixed $input): string {
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
	public static function jsonOk(mixed $entity = null, int $code = 200): never {
		http_response_code($code);
		die(self::toJson($entity));
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
	public static function jsonError(string $message, int $code = 500, array $extra = array()): never {
		http_response_code($code);
		die(self::toJson(array(
				'ok' => false,
				'message' => $message
			) + $extra));
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function jsonBadRequest(string $message = 'Bad Request', array $extra = array()): never {
		self::jsonError($message, 400, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function jsonForbidden(string $message = 'Forbidden', array $extra = array()): never {
		self::jsonError($message, 403, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function jsonNotFound(string $message = 'Not Found', array $extra = array()): never {
		self::jsonError($message, 404, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function jsonMethodNotAllowed(string $message = 'Method Not Allowed', array $extra = array()): never {
		self::jsonError($message, 405, $extra);
	}

	/**
	 * @param string $message
	 * @param array $extra
	 * @return never
	 */
	public static function jsonServiceUnavailable(string $message = 'Service Unavailable', array $extra = array()): never {
		self::jsonError($message, 503, $extra);
	}

	/**
	 * Gets the user given by userUuid (if set) and check if access is allowed
	 *
	 * @param bool $defaultToNull
	 * @return User|null
	 * @throws NotSupported
	 */
	public static function getUserReturningIfForbidden(bool $defaultToNull = false): ?User {
		global $_user;

		if ($_user->isAdmin() === false) {
			if (!empty($_GET['userUuid']) && $_user->getUuid() !== $_GET['userUuid']) {
				self::jsonForbidden();
			} else {
				return $_user;
			}
		} elseif (!empty($_GET['userUuid'])) {
			return User::findByUuid($_GET['userUuid']);
		}

		return ($defaultToNull) ? null : $_user;
	}

	/**
	 * Checks if all required fields are set, returning 400 Bad Request if not
	 *
	 * @param array $dto
	 * @param array $requiredFields
	 * @return void
	 */
	public static function checkRequiredFieldsReturning(array $dto, array $requiredFields): void {
		foreach($requiredFields as $field) {
			if (empty($dto[$field])) {
				self::jsonBadRequest('Missing required field: ' . $field, $dto);
			}
		}
	}

}
