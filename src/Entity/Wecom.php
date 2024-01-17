<?php

namespace App\Entity;

use App\Repository\WecomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WecomRepository::class)]
class Wecom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $corpId;

    #[ORM\Column(type: 'string', length: 255)]
    private $contactsSecret;

    #[ORM\Column(type: 'string', length: 255)]
    private $approvalSecret;

    #[ORM\Column(type: 'string', length: 255)]
    private $callbackToken;

    #[ORM\Column(type: 'string', length: 255)]
    private $callbackAESKey;

    #[ORM\Column(type: 'string', length: 255)]
    private $stampingTemplateId;

    #[ORM\Column(type: 'string', length: 255)]
    private $addingFprTemplateId;

    #[ORM\OneToOne(inversedBy: 'wecom', targetEntity: Organization::class, cascade: ['persist', 'remove'])]
    private $org;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appsecret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorpId(): ?string
    {
        return $this->corpId;
    }

    public function setCorpId(string $corpId): self
    {
        $this->corpId = $corpId;

        return $this;
    }

    public function getContactsSecret(): ?string
    {
        return $this->contactsSecret;
    }

    public function setContactsSecret(string $contactsSecret): self
    {
        $this->contactsSecret = $contactsSecret;

        return $this;
    }

    public function getApprovalSecret(): ?string
    {
        return $this->approvalSecret;
    }

    public function setApprovalSecret(string $approvalSecret): self
    {
        $this->approvalSecret = $approvalSecret;

        return $this;
    }

    public function getCallbackToken(): ?string
    {
        return $this->callbackToken;
    }

    public function setCallbackToken(string $callbackToken): self
    {
        $this->callbackToken = $callbackToken;

        return $this;
    }

    public function getCallbackAESKey(): ?string
    {
        return $this->callbackAESKey;
    }

    public function setCallbackAESKey(string $callbackAESKey): self
    {
        $this->callbackAESKey = $callbackAESKey;

        return $this;
    }

    public function getStampingTemplateId(): ?string
    {
        return $this->stampingTemplateId;
    }

    public function setStampingTemplateId(string $stampingTemplateId): self
    {
        $this->stampingTemplateId = $stampingTemplateId;

        return $this;
    }

    public function getAddingFprTemplateId(): ?string
    {
        return $this->addingFprTemplateId;
    }

    public function setAddingFprTemplateId(string $addingFprTemplateId): self
    {
        $this->addingFprTemplateId = $addingFprTemplateId;

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

    public function getAppid(): ?string
    {
        return $this->appid;
    }

    public function setAppid(?string $appid): static
    {
        $this->appid = $appid;

        return $this;
    }

    public function getAppsecret(): ?string
    {
        return $this->appsecret;
    }

    public function setAppsecret(?string $appsecret): static
    {
        $this->appsecret = $appsecret;

        return $this;
    }
}
