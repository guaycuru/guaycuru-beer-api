<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'brands')]
#[ORM\Entity]
class Brand {
	use IdAndUuid;

	#[ORM\Column]
	private string $name;
}
