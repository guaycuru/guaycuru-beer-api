<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'storages')]
#[ORM\Entity]
class Storage {
	use IdAndUuid, FindAndList;

	#[ORM\Column(nullable: false)]
	private string $name;

	#[ORM\Column(nullable: false)]
	#[ORM\ManyToOne(targetEntity: 'User')]
	private User $owner;

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * @return User
	 */
	public function getOwner(): User {
		return $this->owner;
	}

	/**
	 * @param User $owner
	 */
	public function setOwner(User $owner): void {
		$this->owner = $owner;
	}

	/**
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
