<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use eduMedia\TagBundle\Entity\TagInterface;
use eduMedia\TagBundle\Entity\TagTrait;

#[ORM\Entity]
#[ORM\Table(name: 'tag')]
class Tag implements TagInterface
{

    use TagTrait;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: 'App\Entity\Tagging')]
    protected ?Collection $tagging = null;

}
