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
	#[ORM\JoinColumn(nullable: false)]
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
	 */
	public static function findOneByToken($token): ?self {
		return Shared::_EM()->getRepository(get_called_class())->findOneBy(array('token' => $token));
	}

	/**
	 * Gets the token from HTTP headers, if present
	 *
	 * @return string|null The token or null if not found
	 */
	public static function getTokenFromHeader(): ?string {
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
}
