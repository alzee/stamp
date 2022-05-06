<?php

namespace App\Entity;

use App\Repository\FingerprintRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FingerprintRepository::class)]
class Fingerprint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $uid;

    #[ORM\Column(type: 'string', length: 255)]
    private $username;

    #[ORM\ManyToOne(targetEntity: organization::class, inversedBy: 'fingerprints')]
    private $org;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;

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

    public function getOrg(): ?organization
    {
        return $this->org;
    }

    public function setOrg(?organization $org): self
    {
        $this->org = $org;

        return $this;
    }
}
