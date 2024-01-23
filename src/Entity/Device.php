<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $uuid;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'devices')]
    private $org;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $tagId;

    #[ORM\OneToMany(mappedBy: 'device', targetEntity: Fingerprint::class)]
    private $fingerprints;

    public function __construct()
    {
        $this->fingerprints = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->id;
    }

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

    public function getOrg(): ?Organization
    {
        return $this->org;
    }

    public function setOrg(?Organization $org): self
    {
        $this->org = $org;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTagId(): ?int
    {
        return $this->tagId;
    }

    public function setTagId(?int $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }

    /**
     * @return Collection<int, Fingerprint>
     */
    public function getFingerprints(): Collection
    {
        return $this->fingerprints;
    }

    public function addFingerprint(Fingerprint $fingerprint): self
    {
        if (!$this->fingerprints->contains($fingerprint)) {
            $this->fingerprints[] = $fingerprint;
            $fingerprint->setDevice($this);
        }

        return $this;
    }

    public function removeFingerprint(Fingerprint $fingerprint): self
    {
        if ($this->fingerprints->removeElement($fingerprint)) {
            // set the owning side to null (unless already changed)
            if ($fingerprint->getDevice() === $this) {
                $fingerprint->setDevice(null);
            }
        }

        return $this;
    }
}
