<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'org_id', targetEntity: Device::class)]
    private $devices;

    #[ORM\OneToMany(mappedBy: 'org', targetEntity: Fingerprint::class)]
    private $fingerprints;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
        $this->fingerprints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Device>
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->devices->contains($device)) {
            $this->devices[] = $device;
            $device->setOrgId($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getOrgId() === $this) {
                $device->setOrgId(null);
            }
        }

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
            $fingerprint->setOrg($this);
        }

        return $this;
    }

    public function removeFingerprint(Fingerprint $fingerprint): self
    {
        if ($this->fingerprints->removeElement($fingerprint)) {
            // set the owning side to null (unless already changed)
            if ($fingerprint->getOrg() === $this) {
                $fingerprint->setOrg(null);
            }
        }

        return $this;
    }
}
