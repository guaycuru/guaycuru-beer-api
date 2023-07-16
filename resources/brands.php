<?php

namespace Guaybeer;

// Determine correct method to call
use Guaybeer\Entities\Brand;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

/**
 * Get brand by uuid and return it with a 200 http code
 *
 * @param Brand $brand
 * @return never
 */
function getBrand(Brand $brand): never {
	Shared::JSON_OK($brand->toDTO(), 200);
}

/**
 * List brands and return them with a 200 http code
 *
 * @return never
 */
function listBrands(): never {
	$brands = Brand::find(array(), array('name' => 'ASC'));

	// Convert to DTOs
	$dtos = array_map(fn($brand) => $brand->toDTO(false), $brands);
	Shared::JSON_OK($dtos, 200);
}

/**
 * Add new brand and return the newly created entity with a 201 http code
 *
 * @param array $dto
 * @return never
 */
function addBrand(array $dto): never {
	$brand = new Brand();
	updateFromDTOReturningIfInvalid($brand, $dto);

	Shared::Persist_Or_Json_Unavailable($brand);
	Shared::Flush_Or_Json_Unavailable();

	Shared::JSON_OK($brand->toDTO(), 201);
}

/**
 * Update existing brand and return the updated entity with a 200 http code
 *
 * @param Brand $brand
 * @param array $dto
 * @return never
 */
function updateBrand(Brand $brand, array $dto): never {
	updateFromDTOReturningIfInvalid($brand, $dto);

	Shared::Persist_Or_Json_Unavailable($brand);
	Shared::Flush_Or_Json_Unavailable();

	Shared::JSON_OK($brand->toDTO(), 200);
}

/**
 * Delete brand by uuid
 *
 * @param Brand $brand
 * @return never
 */
function deleteBrand(Brand $brand): never {
	// Only admins can delete brands
	checkAdminReturningForbidden();

	try {
		Shared::_EM()->remove($brand);
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
 * Gets a brand by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Brand
 */
function getOrReturnNotFound($uuid): Brand {
	$item = Brand::findByUuid($uuid);
	if ($item === null) {
		Shared::JSON_Not_Found();
	}

	return $item;
}

/**
 * Update a given brand with data from the DTO returning 400 Bad Request if the DTO is invalid
 *
 * @param Brand $brand
 * @param array $dto
 * @return void
 */
function updateFromDTOReturningIfInvalid(Brand $brand, array $dto): void {
	$dto['name'] = trim($dto['name']);

	Shared::checkRequiredFieldsReturning($dto, ['name']);

	$brand->setName($dto['name']);
}

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (!empty($_GET['uuid'])) {
			$brand = getOrReturnNotFound($_GET['uuid']);
			getBrand($brand);
		} else {
			listBrands();
		}
	case 'POST':
		addBrand($_GET);
	case 'PUT':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$brand = getOrReturnNotFound($_GET['uuid']);
		updateBrand($brand, $_GET);
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$brand = getOrReturnNotFound($_GET['uuid']);
		deleteBrand($brand);
	default:
		Shared::JSON_Method_Not_Allowed();
}
