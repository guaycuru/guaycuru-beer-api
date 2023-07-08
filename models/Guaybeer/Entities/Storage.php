<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\IdAndUuid;

#[ORM\Table(name: 'stocks')]
#[ORM\Entity]
class Storage {
	use IdAndUuid;

	#[ORM\Column]
	private string $name;

	#[ORM\ManyToOne(targetEntity: 'User')]
	private User $owner;
}
