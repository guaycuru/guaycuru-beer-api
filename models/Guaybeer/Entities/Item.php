<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\FindAndList;
use Guaybeer\Traits\IdAndUuid;
use Util\Shared;

#[ORM\Table(name: 'items')]
#[ORM\Entity]
class Item {
	use IdAndUuid, FindAndList;

	#[ORM\Column(nullable: false)]
	private string $name;

	#[ORM\ManyToOne(targetEntity: 'Product')]
	#[ORM\JoinColumn(nullable: false)]
	private Product $product;

	#[ORM\ManyToOne(targetEntity: 'Storage')]
	#[ORM\JoinColumn(nullable: false)]
	private Storage $storage;

	#[ORM\Column(nullable: false)]
	private int $quantity;

	#[ORM\Column(nullable: true)]
	private \DateTimeImmutable $expiry;

	/**
	 * Finds all items that have a given brand or storage
	 *
	 * @param Brand|null $brand
	 * @param Storage|null $storage
	 * @return self[]|null
	 */
	public static function findByBrandOrStorage(?Brand $brand, ?Storage $storage): ?array {
		$qb = Shared::_EM()->createQueryBuilder();
		$qb->select('i')->from(get_called_class(), 'i');

		if($brand !== null) {
			$qb->andWhere('i.brand = :brand')
				->setParameter('brand', $brand);
		}

		if($storage !== null) {
			$qb->andWhere('i.storage = :storage')
				->setParameter('storage', $storage);
		}

		return $qb->getQuery()->getResult();
	}

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
	 * @return Product
	 */
	public function getProduct(): Product {
		return $this->product;
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product): void {
		$this->product = $product;
	}

	/**
	 * @return Storage
	 */
	public function getStorage(): Storage {
		return $this->storage;
	}

	/**
	 * @param Storage $storage
	 */
	public function setStorage(Storage $storage): void {
		$this->storage = $storage;
	}

	/**
	 * @return int
	 */
	public function getQuantity(): int {
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 */
	public function setQuantity(int $quantity): void {
		$this->quantity = $quantity;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function getExpiry(): \DateTimeImmutable {
		return $this->expiry;
	}

	/**
	 * @param \DateTimeImmutable $expiry
	 */
	public function setExpiry(\DateTimeImmutable $expiry): void {
		$this->expiry = $expiry;
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
			'expiry' => $this->expiry->format('Y-m-d'),
			'product' => $this->product->toDTO(),
			'storage' => $this->storage->toDTO()
		];
	}
}
