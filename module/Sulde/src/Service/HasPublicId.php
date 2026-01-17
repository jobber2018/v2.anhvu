<?php

namespace Sulde\Service;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

trait HasPublicId
{
    /**
     * @ORM\Column(type="string", length=22, unique=true)
     */
    protected ?string $public_id = null;

    public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    /**
     * @ORM\PrePersist
     */
    public function generatePublicId(): void
    {
        if ($this->public_id === null) {
            $uuid = Uuid::uuid4()->getBytes();
            $this->public_id = rtrim(strtr(base64_encode($uuid), '+/', '-_'), '=');
        }
    }
}