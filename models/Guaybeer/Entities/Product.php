<?php

namespace Guaybeer\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'products')]
#[ORM\Entity]
class Product {
	use IdAndUuid, FindAndList;

	#[ORM\Column(nullable: false)]
	private string $name;

	#[ORM\ManyToOne(targetEntity: 'Brand')]
	#[ORM\JoinColumn(nullable: false)]
	private Brand $brand;

	/**
	 * @var Item[]|Collection|Selectable
	 **/
	#[ORM\OneToMany(mappedBy: 'product', targetEntity: 'Item')]
	private array|Collection|Selectable $items;

	/**
	 * @var Tag[]|Collection|Selectable
	 **/
	#[ORM\ManyToMany(targetEntity: 'Tag')]
	private array|Collection|Selectable $tags;

	public function __construct() {
		$this->items = new ArrayCollection();
		$this->tags = new ArrayCollection();
	}

	/**
	 * Returns a DTO representation of this
	 *
	 * @param bool $includeItems
	 * @param bool $includeTags
	 * @return array
	 */
	public function toDTO(bool $includeItems = false, bool $includeTags = true): array {
		$dto = [
			'uuid' => $this->uuid,
			'name' => $this->name,
			'brand' => $this->brand->toDTO()
		];

		if ($includeItems) {
			$dto['items'] = $this->items->map(fn($item) => $item->toDTO())->toArray();
		}

		if ($includeTags) {
			$dto['tags'] = $this->tags->map(fn($tag) => $tag->toDTO())->toArray();
		}

		return $dto;
	}
}
