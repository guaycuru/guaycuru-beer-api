<?php

namespace Guaybeer\Traits;

use Util\Shared;
use Util\Uuid;

trait GetOrNew {
	/**
	 * @var self[]
	 */
	private static array $_cache;

	/**
	 * @param string $name
	 * @return static
	 */
	public static function getOrNew(string $name): static {
		$lowered = mb_strtolower($name);
		if (!isset(static::$_cache[$lowered])) {
			$entity = Shared::_EM()->getRepository(get_called_class())->findOneBy(array('name' => $name));

			if ($entity === null) {
				$entity = new static();
				$entity->setUuid(Uuid::v4());
				$entity->setName($name);
			}

			static::$_cache[$lowered] = $entity;
		} else {
			$entity = static::$_cache[$lowered];
		}

		return $entity;
	}
}
