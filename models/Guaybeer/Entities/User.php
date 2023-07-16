<?php

namespace Guaybeer\Entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Guaybeer\Traits\IdAndUuid;
use Util\Shared;

#[ORM\Table(name: 'users')]
#[ORM\Entity]
class User {
	use IdAndUuid;

	/**
	 * @var UserToken[]|Collection|Selectable
	 **/
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: 'UserToken', cascade: ['remove'], orphanRemoval: true)]
	private array|Collection|Selectable $tokens;

	#[ORM\Column]
	private string $name;

	#[ORM\Column(unique: true)]
	private string $email;

	#[ORM\Column]
	private bool $admin = false;

	/**
	 * @return UserToken[]|Collection|Selectable
	 */
	public function getTokens(): array|Collection|Selectable {
		return $this->tokens;
	}

	/**
	 * @param UserToken[]|Collection|Selectable $tokens
	 */
	public function setTokens(array|Collection|Selectable $tokens): void {
		$this->tokens = $tokens;
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
	 * @return bool
	 */
	public function isAdmin(): bool {
		return $this->admin;
	}

	/**
	 * @param bool $admin
	 */
	public function setAdmin(bool $admin): void {
		$this->admin = $admin;
	}

	/**
	 * Finds a single user by uuid
	 *
	 * @param $uuid
	 * @return self|null
	 */
	public static function findByUuid($uuid): ?self {
		return Shared::_EM()->getRepository(get_called_class())->findOneBy(array('uuid' => $uuid));
	}

	/**
	 * list
	 *
	 * Lists all users
	 *
	 * @return self[]|null
	 */
	public static function list(): ?array {
		return Shared::_EM()->getRepository(get_called_class())->findAll();
	}

	/**
	 * Checks if there's a logged in user
	 *
	 * @return self|false The user found via the token, if any
	 */
	public static function isLoggedIn(): self|false {
		$user = new self();

		// Check token in header
		$token = UserToken::getTokenFromHeader();
		if (empty($token)) {
			return false;
		} else {
			$userToken = UserToken::findOneByToken($token);
			if ($userToken !== null) {
				$user = $userToken->getUser();
			}
		}

		return $user;
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
			'admin' => $this->admin
		];
	}
}
