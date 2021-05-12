<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Gedmo\SoftDeleteable()
 */
class User implements UserInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $uuid;
    
     /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;
    
     /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $image_url;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];
    
    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $accessToken;
    
    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $refreshToken;
    
    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $lastConn;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
    
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
    
    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $imaegUrl): self
    {
        $this->image_url = $imaegUrl;

        return $this;
    }
    
    public static function getImageUrlFromSpotifyInformations(SpotifyResourceOwner $spotifyUser)
    {
        $images = $spotifyUser->getImages();
        if (isset($images[0])) {
            return $images[0]['url'];
        }
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        $roles = ['ROLE_USER', 'ROLE_SPOTIFY'];

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
	
	public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }
    
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }
	
	/**
     * Sets createdAt.
     *
     * @return $this
     */
    public function setLastConn(DateTime $lastConn = null)
    {
        if (!$lastConn instanceOf DateTime) {
            $lastConn = new DateTime();
        }
        $this->lastConn = $lastConn;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTime
     */
    public function getLastConn()
    {
        return $this->lastConn;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
