<?php

namespace Guaybeer\Traits;

use Doctrine\ORM\Mapping as ORM;

trait IdAndUuid {
	#[ORM\Id]
	#[ORM\Column(options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	private ?int $id = null;

	#[ORM\Column(unique: true)]
	private string $uuid;

	/**
	 * @return integer|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getUuid(): string {
		return $this->uuid;
	}

	/**
	 * @param string $uuid
	 */
	public function setUuid(string $uuid): void {
		$this->uuid = $uuid;
	}
}
