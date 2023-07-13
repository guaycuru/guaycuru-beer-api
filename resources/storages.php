<?php

namespace Guaybeer;

use Guaybeer\Entities\Storage;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

/**
 * Get storage by uuid and return it with a 200 http code
 *
 * @param Storage $storage
 * @return never
 */
function getStorage(Storage $storage): never {
	Shared::JSON_OK($storage->toDTO(), 200);
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
	Shared::JSON_OK($dtos, 200);
}

/**
 * Gets a storage by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Storage
 */
function getOrReturnNotFound($uuid): Storage {
	$storage = Storage::findByUuid($uuid);
	if ($storage === null) {
		Shared::JSON_Not_Found();
	}

	return $storage;
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
		Shared::JSON_Service_Unavailable($e->getMessage());
	}

	Shared::JSON_OK(null, 204);
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
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$storage = getOrReturnNotFound($_GET['uuid']);
		deleteStorage($storage);
	default:
		Shared::JSON_Method_Not_Allowed();
}
