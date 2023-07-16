<?php

namespace Guaybeer\Entities;

use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Mapping as ORM;
use Util\Shared;

#[ORM\Table(name: 'user_tokens')]
#[ORM\Entity]
class UserToken {
	#[ORM\Id]
	#[ORM\Column]
	#[ORM\GeneratedValue]
	private ?int $id = null;

	#[ORM\Column(unique: true)]
	private string $token;

	#[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'tokens')]
	private User $user;

	/**
	 * @return integer|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @param string $token
	 */
	public function setToken(string $token): void {
		$this->token = $token;
	}

	/**
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser(User $user): void {
		$this->user = $user;
	}

	/**
	 * @param $token
	 * @return self|null
	 * @throws NotSupported
	 */
	public static function findOneByToken($token): ?self {
		return Shared::_EM()->getRepository(get_called_class())->findOneBy(array('token' => $token));
	}
}
