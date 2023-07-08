<?php

namespace Guaybeer\Entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'products')]
#[ORM\Entity]
class Product {
	use IdAndUuid;

	#[ORM\Column]
	private string $name;

	#[ORM\ManyToOne(targetEntity: 'Brand')]
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
}
