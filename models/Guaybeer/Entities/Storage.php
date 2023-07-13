<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'stocks')]
#[ORM\Entity]
class Storage {
	use IdAndUuid, FindAndList;

	#[ORM\Column]
	private string $name;

	#[ORM\ManyToOne(targetEntity: 'User')]
	private User $owner;

	/**
	 * toDTO
	 *
	 * Returns a DTO representation of this
	 *
	 * @return array
	 */
	public function toDTO(): array {
		return [
			'uuid' => $this->uuid,
			'name' => $this->name,
			'owner' => $this->owner->toDTO()
		];
	}
}
