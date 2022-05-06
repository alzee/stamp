<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
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

    #[ORM\ManyToOne(targetEntity: organization::class, inversedBy: 'devices')]
    private $org_id;

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

    public function getOrgId(): ?organization
    {
        return $this->org_id;
    }

    public function setOrgId(?organization $org_id): self
    {
        $this->org_id = $org_id;

        return $this;
    }
}
