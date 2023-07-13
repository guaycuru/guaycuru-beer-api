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

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (!empty($_GET['uuid'])) {
			$brand = getOrReturnNotFound($_GET['uuid']);
			getBrand($brand);
		} else {
			listBrands();
		}
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$brand = getOrReturnNotFound($_GET['uuid']);
		deleteBrand($brand);
	default:
		Shared::JSON_Method_Not_Allowed();
}
