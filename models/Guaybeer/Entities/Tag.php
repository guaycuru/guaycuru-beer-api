<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Enums\TagType;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\GetOrNew;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'tags')]
#[ORM\Index(columns: ['name'], name: 'name')]
#[ORM\Entity]
class Tag {
	use IdAndUuid, FindAndList, GetOrNew;

	#[ORM\Column(unique: true)]
	private string $name;

	#[ORM\Column(unique: true)]
	private TagType $type;

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
	 * @return TagType
	 */
	public function getType(): TagType {
		return $this->type;
	}

	/**
	 * @param TagType $type
	 */
	public function setType(TagType $type): void {
		$this->type = $type;
	}

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
			'name' => $this->name
		];
	}
}
