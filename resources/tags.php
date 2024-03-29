<?php

namespace Guaybeer;

use Guaybeer\Entities\Tag;
use Guaybeer\Enums\TagType;
use Util\Shared;

require_once(__DIR__.'/../common/common.inc.php');

global $_JSON;

/**
 * Get tag by uuid and return it with a 200 http code
 *
 * @param Tag $tag
 * @return never
 */
function getTag(Tag $tag): never {
	Shared::jsonOk($tag->toDTO(), 200);
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
	Shared::jsonOk($dtos, 200);
}

/**
 * Add new tag and return the newly created entity with a 201 http code
 *
 * @param array $dto
 * @return never
 */
function addTag(array $dto): never {
	$tag = new Tag();
	updateFromDTOReturningIfInvalid($tag, $dto);

	Shared::persistOrJsonUnavailable($tag);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($tag->toDTO(), 201);
}

/**
 * Update existing tag and return the updated entity with a 200 http code
 *
 * @param Tag $tag
 * @param array $dto
 * @return never
 */
function updateTag(Tag $tag, array $dto): never {
	updateFromDTOReturningIfInvalid($tag, $dto);

	Shared::persistOrJsonUnavailable($tag);
	Shared::flushOrJsonUnavailable();

	Shared::jsonOk($tag->toDTO(), 200);
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
		Shared::jsonServiceUnavailable($e->getMessage());
	}

	Shared::jsonOk(null, 204);
}

/**
 * helpers
 */

/**
 * Gets a tag by uuid returning 404 Not Found if not found
 *
 * @param $uuid
 * @return Tag
 */
function getOrReturnNotFound($uuid): Tag {
	$tag = Tag::findByUuid($uuid);
	if ($tag === null) {
		Shared::jsonNotFound();
	}

	return $tag;
}

/**
 * Update a given brand with data from the DTO returning 400 Bad Request if the DTO is invalid
 *
 * @param Tag $tag
 * @param array $dto
 * @return void
 */
function updateFromDTOReturningIfInvalid(Tag $tag, array $dto): void {
	$dto['name'] = trim($dto['name']);

	Shared::checkRequiredFieldsReturning($dto, ['name']);

	$tag->setName($dto['name']);
	$tag->setType(TagType::tryFrom($dto['type']) ?? null);
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
	case 'POST':
		addTag($_JSON);
	case 'PUT':
		if (empty($_GET['uuid'])) {
			Shared::jsonBadRequest('Missing uuid');
		}

		$tag = getOrReturnNotFound($_GET['uuid']);
		updateTag($tag, $_JSON);
	case 'DELETE':
		if (empty($_GET['uuid'])) {
			Shared::jsonBadRequest('Missing uuid');
		}

		$tag = getOrReturnNotFound($_GET['uuid']);
		deleteTag($tag);
	default:
		Shared::jsonMethodNotAllowed();
}
