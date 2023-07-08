<?php

namespace Guaybeer\Traits;

use Doctrine\Common\Collections\Collection;
use Util\Shared;

trait FindAndList {
	/**
	 * list
	 *
	 * List all entities
	 *
	 * @return static[]|Collection
	 */
	public static function list(): array|Collection {
		return Shared::_EM()->getRepository(get_called_class())->findAll();
	}

	/**
	 * find
	 *
	 * Find entities
	 *
	 * @return static[]|Collection
	 */
	public static function find(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array|Collection {
		return Shared::_EM()->getRepository(get_called_class())->findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * findByUuid
	 *
	 * Finds an entity by uuid
	 *
	 * @param $uuid
	 * @return static|null
	 */
	public static function findByUuid($uuid): ?static {
		return Shared::_EM()->getRepository(get_called_class())->findOneBy(array('uuid' => $uuid));
	}
}
