<?php

namespace Guaybeer;

use Guaybeer\Entities\Brand;
use Guaybeer\Entities\Item;
use Guaybeer\Entities\Product;
use Guaybeer\Entities\Storage;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

global $_JSON;

/**
 * Get item by uuid and return it with a 200 http code
 *
 * @param Item $item
 * @return never
 */
function getItem(Item $item): never {
	Shared::jsonOk($item->toDTO(), 200);
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
	Shared::jsonOk($dtos, 200);
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
			Shared::jsonError('Brand not found', 404);
		}
	}
	$storage = null;
	if (!empty($ipAddress)) {
		$storage = Storage::findByUuid($storageUuid);
		if ($storage === null) {
			Shared::jsonError('Storage not found', 404);
		}
	}

	$items = Item::findByBrandOrStorage($brand, $storage);

	// Convert to DTOs
	$dtos = array_map(fn($item) => $item->toDTO(), $items);
	Shared::jsonOk($dtos, 200);
}

/**
 * Add new item and return the newly created entity with a 201 http code
 *
 * @param array $dto
 * @return never
 */
function addItem(array $dto): never {
	$item = new Item();
	updateFromDTOReturningIfInvalid($item, $dto, true);

	Shared::persistOrJsonUnavailable($item);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($item->toDTO(), 201);
}

/**
 * Update existing item and return the updated entity with a 200 http code
 *
 * @param Item $item
 * @param array $dto
 * @return never
 */
function updateItem(Item $item, array $dto): never {
	updateFromDTOReturningIfInvalid($item, $dto, false);

	Shared::persistOrJsonUnavailable($item);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($item->toDTO(), 200);
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
		Shared::jsonServiceUnavailable($e->getMessage());
	}

	Shared::jsonOk(null, 204);
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
		Shared::jsonNotFound();
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
		Shared::jsonForbidden();
	}
}

/**
 * Update a given item with data from the DTO returning 400 Bad Request if the DTO is invalid
 *
 * @param Item $item
 * @param array $dto
 * @param bool $setProduct
 * @return void
 */
function updateFromDTOReturningIfInvalid(Item $item, array $dto, bool $setProduct = false): void {
	Shared::checkRequiredFieldsReturning($dto, ['quantity', 'expiry', 'storage.uuid']);

	$item->setQuantity($dto['quantity']);

	$expiry = \DateTimeImmutable::createFromFormat(Shared::JSON_DATE, $dto['expiry']);
	if ($expiry === false) {
		Shared::jsonBadRequest('Invalid expiry date');
	}
	$item->setExpiry($expiry);

	$storage = Storage::findByUuid($dto['storageUuid']);
	if ($storage === null) {
		Shared::jsonBadRequest('Storage not found');
	}
	$item->setStorage($storage);

	if ($setProduct) {
		if (empty($dto['product']['uuid'])) {
			Shared::jsonBadRequest('Missing product uuid');
		}

		$product = Product::findByUuid($dto['product']['uuid']);
		if ($product === null) {
			Shared::jsonBadRequest('Product not found');
		}
		$item->setProduct($product);
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
	case 'POST':
		addItem($_JSON);
	case 'PUT':
		if (!empty($_GET['uuid'])) {
			$item = getOrReturnNotFound($_GET['uuid']);
		} else {
			Shared::jsonBadRequest('Missing uuid');
		}

		updateItem($item, $_JSON);
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::jsonBadRequest('Missing uuid');
		}

		$item = getOrReturnNotFound($_GET['uuid']);
		deleteItem($item);
	default:
		Shared::jsonMethodNotAllowed();
}
