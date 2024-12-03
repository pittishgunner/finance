<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WebPush\Subscription as WebPushSubscription;

#[ORM\Table(name: 'subscriptions')]
#[ORM\Entity]
class Subscription extends WebPushSubscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]

    private ?User $user;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $subscription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    // We need to override this method as it returns a WebPush\Subscription and we want an entity
    /*public static function createFromString(string $input): self
    {
        $base = BaseSubscription::createFromString($input);
        $object = new self($base->getEndpoint());
        $object->withContentEncodings($base->getSupportedContentEncodings());
        foreach ($base->getKeys()->all() as $k => $v) {
            $object->getKeys()->set($k, $v);
        }

        return $object;
    }*/

    public function getSubscription(): ?string
    {
        return $this->subscription;
    }

    public function setSubscription(string $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }
}
