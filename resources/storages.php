<?php

namespace Guaybeer;

use Guaybeer\Entities\Storage;
use Guaybeer\Entities\User;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

global $_JSON;

/**
 * Get storage by uuid and return it with a 200 http code
 *
 * @param Storage $storage
 * @return never
 */
function getStorage(Storage $storage): never {
	Shared::jsonOk($storage->toDTO(), 200);
}

/**
 * List storages and return them with a 200 http code
 *
 * @return never
 */
function listStorages(): never {
	$storages = Storage::find(array(), array('name' => 'ASC'));

	// Convert to DTOs
	$dtos = array_map(fn($storage) => $storage->toDTO(false), $storages);
	Shared::jsonOk($dtos, 200);
}

/**
 * Add new storage and return the newly created entity with a 201 http code
 *
 * @param User $user
 * @param array $dto
 * @return never
 */
function addStorage(User $user, array $dto): never {
	$storage = new Storage();
	$storage->setOwner($user);
	updateFromDTOReturningIfInvalid($storage, $dto);

	Shared::persistOrJsonUnavailable($storage);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($storage->toDTO(), 201);
}

/**
 * Update existing storage and return the updated entity with a 200 http code
 *
 * @param Storage $storage
 * @param array $dto
 * @return never
 */
function updateStorage(Storage $storage, array $dto): never {
	updateFromDTOReturningIfInvalid($storage, $dto);

	Shared::persistOrJsonUnavailable($storage);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($storage->toDTO(), 200);
}

/**
 * Delete storage by uuid
 *
 * @param Storage $storage
 * @return never
 */
function deleteStorage(Storage $storage): never {
	// Only admins can delete storages
	checkAdminReturningForbidden();

	try {
		Shared::_EM()->remove($storage);
		Shared::_EM()->flush();
	} catch(\Exception $e) {
		Shared::jsonServiceUnavailable($e->getMessage());
	}

	Shared::jsonOk(null, 204);
}

/*
 * helpers
 */

/**
 * Gets a storage by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Storage
 */
function getOrReturnNotFound($uuid): Storage {
	$storage = Storage::findByUuid($uuid);
	if ($storage === null) {
		Shared::jsonNotFound();
	}

	return $storage;
}

/**
 * Update a given storage with data from the DTO returning 400 Bad Request if the DTO is invalid
 *
 * @param Storage $storage
 * @param array $dto
 * @return void
 */
function updateFromDTOReturningIfInvalid(Storage $storage, array $dto): void {
	$dto['name'] = trim($dto['name']);

	Shared::checkRequiredFieldsReturning($dto, ['name']);

	$storage->setName($dto['name']);
}

// Load the user given in in the URI
$user = Shared::getUserReturningIfForbidden();

// Determine correct method to call
switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (!empty($_GET['uuid'])) {
			$storage = getOrReturnNotFound($_GET['uuid']);
			getStorage($storage);
		} else {
			listStorages();
		}
	case 'POST':
		addStorage($user, $_JSON);
	case 'PUT':
		if (empty($_GET['uuid'])) {
			Shared::jsonBadRequest('Missing uuid');
		}

		$storage = getOrReturnNotFound($_GET['uuid']);
		updateStorage($storage, $_JSON);
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::jsonBadRequest('Missing uuid');
		}

		$storage = getOrReturnNotFound($_GET['uuid']);
		deleteStorage($storage);
	default:
		Shared::jsonMethodNotAllowed();
}
