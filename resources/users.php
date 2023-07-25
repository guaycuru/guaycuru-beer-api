<?php

namespace Guaybeer;

use Guaybeer\Entities\User;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

global $_JSON;

/**
 * Get user by uuid and return it with a 200 http code
 *
 * @param User $user
 * @return never
 */
function getUser(User $user): never {
	Shared::jsonOk($user->toDTO(), 200);
}

/**
 * List users and return them with a 200 http code
 *
 * @return never
 */
function listUsers(): never {
	checkAdminReturningForbidden();

	// Convert to DTOs
	$users = array_map(fn($user) => $user->toDTO(), User::list());
	Shared::jsonOk($users, 200);
}

/**
 * Gets the user given by uuid (if set) and check if access is allowed
 *
 * @return User
 */
function getRequestedUserReturningIfForbidden(): User {
	global $_user;

	$uuid = $_GET['uuid'] ?? null;
	if ($uuid === 'me') {
		return $_user;
	}

	if ($_user->isAdmin() === false) {
		if (!empty($uuid) && $_user->getUuid() !== $uuid) {
			Shared::jsonForbidden();
		}
	} elseif (!empty($uuid)) {
		return User::findByUuid($uuid);
	}

	return $_user;
}

/**
 * Checks if the current user is an admin returning 403 Forbidden if not
 *
 * @return void
 */
function checkAdminReturningForbidden(): void {
	global $_user;

	if ($_user->isAdmin() === false) {
		Shared::jsonForbidden();
	}
}

// Determine correct method to call
switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (!empty($_GET['uuid'])) {
			$user = getRequestedUserReturningIfForbidden();
			getUser($user);
		} else {
			listUsers();
		}
	default:
		Shared::jsonMethodNotAllowed();
}
