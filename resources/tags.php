<?php

namespace Guaybeer;

use Guaybeer\Entities\Tag;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

/**
 * Get tag by uuid and return it with a 200 http code
 *
 * @param Tag $tag
 * @return never
 */
function getTag(Tag $tag): never {
	Shared::JSON_OK($tag->toDTO(), 200);
}

/**
 * List tags and return them with a 200 http code
 *
 * @return never
 */
function listTags(): never {
	$tags = Tag::find(array(), array('name' => 'ASC'));

	// Convert to DTOs
	$dtos = array_map(fn($tag) => $tag->toDTO(false), $tags);
	Shared::JSON_OK($dtos, 200);
}

/**
 * Gets a tag by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Tag
 */
function getOrReturnNotFound($uuid): Tag {
	$tag = Tag::findByUuid($uuid);
	if ($tag === null) {
		Shared::JSON_Not_Found();
	}

	return $tag;
}

/**
 * Delete tag by uuid
 *
 * @param Tag $tag
 * @return never
 */
function deleteTag(Tag $tag): never {
	// Only admins can delete tags
	checkAdminReturningForbidden();

	try {
		Shared::_EM()->remove($tag);
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
			$tag = getOrReturnNotFound($_GET['uuid']);
			getTag($tag);
		} else {
			listTags();
		}
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::JSON_Bad_Request('Missing uuid');
		}

		$tag = getOrReturnNotFound($_GET['uuid']);
		deleteTag($tag);
	default:
		Shared::JSON_Method_Not_Allowed();
}
