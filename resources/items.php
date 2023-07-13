<?php

namespace Guaybeer;

use Guaybeer\Entities\Brand;
use Guaybeer\Entities\Item;
use Guaybeer\Entities\Storage;
use Guaybeer\Entities\User;
use Util\Shared;
use Util\Uuid;

require_once(__DIR__.'/../common/common.inc.php');

/**
 * Get item by uuid and return it with a 200 http code
 *
 * @param Item $item
 * @return never
 */
function getItem(Item $item): never {
	Shared::JSON_OK($item->toDTO(), 200);
}

/**
 * List items and return them with a 200 http code
 *
 * @return never
 */
function listItems(): never {
	$items = Item::list();

	// Convert to DTOs
	$dtos = array_map(fn($item) => $item->toDTO(false, false), $items);
	Shared::JSON_OK($dtos, 200);
}

/**
 * Find items and return them with a 200 http code
 *
 * @param string|null $brandUuid
 * @param string|null $storageUuid
 * @return never
 */
function findItems(?string $brandUuid, ?string $storageUuid): never {
	$brand = null;
	if (!empty($labelUuid)) {
		$brand = Brand::findByUuid($brandUuid);
		if ($brand === null) {
			Shared::JSON_Error('Brand not found', 404);
		}
	}
	$storage = null;
	if (!empty($ipAddress)) {
		$storage = Storage::findByUuid($storageUuid);
		if ($storage === null) {
			Shared::JSON_Error('Storage not found', 404);
		}
	}

	$items = Item::findByBrandOrStorage($brand, $storage);

	// Convert to DTOs
	$dtos = array_map(fn($item) => $item->toDTO(), $items);
	Shared::JSON_OK($dtos, 200);
}

/**
 * Add new item and return the newly created entity with a 201 http code
 *
 * @param User $user
 * @param $contents
 * @param $filename
 * @return never
 */
function addItem(User $user, $contents, $filename): never {
	$sha1 = sha1($contents);
	$item = Item::findBySha1($sha1);
	$status = 200;
	$now = new \DateTimeImmutable();

	if ($item === null) {
		$item = new Item();
		$item->setUuid(Uuid::v4());
		$item->setUser($user);
		$item->setDatetime($now);
		$item->setName($filename);

		// Calculate sha1 and size
		$item->setSha1($sha1);
		$item->setMd5(md5($contents));
		$item->setSize(strlen($contents));

		Shared::Persist_Or_Json_Unavailable($item);
	}

	try {
		foreach(Provider::cases() as $provider) {
			$analysis = Analysis::getOrNew($item, $provider, $now);
			$new = $analysis->getId() === null;

			$refreshed = $analysis->refreshIfNeeded($now, $contents, $filename);

			if ($new) {
				if (!$refreshed) {
					$item->getAnalyses()->removeElement($analysis);
				} else {
					Shared::_EM()->persist($analysis);
				}
			} elseif ($refreshed) {
				Shared::_EM()->persist($analysis);
			}
		}
	} catch(\Exception $e) {
		Shared::JSON_Service_Unavailable($e->getMessage(), ['trace' => $e->getTraceAsString()]);
	}

	try {
		Shared::_EM()->flush();
		$status = 201;
	} catch(\Exception $e) {
		Shared::JSON_Service_Unavailable($e->getMessage(), ['trace' => $e->getTraceAsString()]);
	}

	Shared::JSON_OK($item->toDTO(true, true), $status);
}

/**
 * Delete item by uuid
 *
 * @param Item $item
 * @return never
 */
function deleteItem(Item $item): never {
	// Only admins can delete items
	checkAdminReturningForbidden();

	try {
		Shared::_EM()->remove($item);
		Shared::_EM()->flush();
	} catch(\Exception $e) {
		Shared::JSON_Service_Unavailable($e->getMessage());
	}

	Shared::JSON_OK(null, 204);
}

/*
 * helpers
 */

/**
 * Gets a item by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Item
 */
function getOrReturnNotFound($uuid): Item {
	$item = Item::findByUuid($uuid);
	if ($item === null) {
		Shared::JSON_Not_Found();
	}

	return $item;
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

/**
 * Update a given item with data from the DTO returning 400 Bad Request if the DTO is invalid
 *
 * @param Item $item
 * @param array $dto
 * @return void
 */
function updateFromDTOReturningIfInvalid(Item $item, array $dto): void {
	$dto['name'] = trim($dto['name']);

	checkRequiredFieldsReturning($dto, ['name']);

	$item->setName($dto['name']);
}

/**
 * Checks if all required fields are set, returning 400 Bad Request if not
 *
 * @param array $dto
 * @param array $requiredFields
 * @return void
 */
function checkRequiredFieldsReturning(array $dto, array $requiredFields): void {
	foreach($requiredFields as $field) {
		if (empty($dto[$field])) {
			Shared::JSON_Bad_Request('Missing required field: ' . $field, $dto);
		}
	}
}

// Load the user given in in the URI
$user = Shared::getUserReturningIfForbidden();

// Determine correct method to call
switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (!empty($_GET['uuid'])) {
			$item = getOrReturnNotFound($_GET['uuid']);
			getItem($item);
		} elseif (!empty($_GET['brand']) || !empty($_GET['storage'])) {
			findItems($_GET['brand'] ?? null, $_GET['storage'] ?? null);
		} else {
			listItems();
		}
	/*case 'POST':
		if (!empty($_GET['uuid'])) {
			$item = getOrReturnNotFound($_GET['uuid']);
		}

		if (empty($_FILES['file'])) {
			Shared::JSON_Bad_Request('Missing file');
		}

		updateItem($user, file_get_contents($_FILES['file']['tmp_name']), $_FILES['file']['name']);
	case 'PUT':
		addItem($user, file_get_contents('php://input'), $_GET['uuid'] ?? '');*/
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$item = getOrReturnNotFound($_GET['uuid']);
		deleteItem($item);
	default:
		Shared::JSON_Method_Not_Allowed();
}
