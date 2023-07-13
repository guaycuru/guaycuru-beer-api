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

	#[ORM\Column]
	private string $name;

	#[ORM\Column]
	private \DateTimeImmutable $expiry;

	#[ORM\ManyToOne(targetEntity: 'Product')]
	private Product $product;

	#[ORM\ManyToOne(targetEntity: 'Storage')]
	private Storage $storage;

	/**
	 * findByLabel
	 *
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
	 * toDTO
	 *
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
