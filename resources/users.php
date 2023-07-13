<?php

namespace Guaybeer;

use Guaybeer\Entities\User;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

/**
 * Get user by uuid and return it with a 200 http code
 *
 * @param User $user
 * @return never
 */
function getUser(User $user): never {
	Shared::JSON_OK($user->toDTO(), 200);
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
	Shared::JSON_OK($users, 200);
}

/**
 * Gets the user given by uuid (if set) and check if access is allowed
 *
 * @return User
 */
function getRequestedUserReturningIfForbidden(): User {
	global $_user;

	if ($_user->isAdmin() === false) {
		if (!empty($_GET['uuid']) && $_user->getUuid() !== $_GET['uuid']) {
			Shared::JSON_Forbidden();
		}
	} elseif (!empty($_GET['uuid'])) {
		return User::findByUuid($_GET['uuid']);
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
		Shared::JSON_Forbidden();
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
		Shared::JSON_Method_Not_Allowed();
}
