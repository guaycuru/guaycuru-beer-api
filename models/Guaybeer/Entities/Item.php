<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'items')]
#[ORM\Entity]
class Item {
	use IdAndUuid;

	#[ORM\Column]
	private string $name;

	#[ORM\Column]
	private \DateTimeImmutable $expiry;

	#[ORM\ManyToOne(targetEntity: 'Product')]
	private Product $product;

	#[ORM\ManyToOne(targetEntity: 'Storage')]
	private Storage $storage;
}
