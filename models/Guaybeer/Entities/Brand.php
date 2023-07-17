<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'brands')]
#[ORM\Entity]
class Brand {
	use IdAndUuid, FindAndList;

	#[ORM\Column(nullable: false)]
	private string $name;

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
	 * Returns a DTO representation of this
	 *
	 * @return array
	 */
	public function toDTO(): array {
		return [
			'uuid' => $this->uuid,
			'name' => $this->name
		];
	}
}
