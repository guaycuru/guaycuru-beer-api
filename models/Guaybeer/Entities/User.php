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
	 * findByUuid
	 *
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
	 * Ping
	 *
	 * Checks if there's a logged in user
	 *
	 * @return self|false The user found via the token, if any
	 */
	public static function ping(): self|false {
		$user = new self();

		// Check token in header
		$token = self::getToken();
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
	 * getToken
	 *
	 * Gets the token from HTTP headers, if present
	 *
	 * @return string|null The token or null if not found
	 */
	private static function getToken(): ?string {
		$headers = getallheaders();
		$authorization = $headers['Authorization'] ?? $_SERVER['Authorization'] ?? '';
		if (!empty($authorization)) {
			$headers = trim($authorization);
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
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
			'admin' => $this->admin
		];
	}
}
