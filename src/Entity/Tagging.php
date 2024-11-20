<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use eduMedia\TagBundle\Entity\TaggingInterface;
use eduMedia\TagBundle\Entity\TaggingTrait;
use eduMedia\TagBundle\Entity\TagInterface;

#[ORM\Entity]
#[ORM\Table(name: 'tagging')]
class Tagging implements TaggingInterface
{

    use TaggingTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tag', inversedBy: 'tagging')]
    protected TagInterface $tag;

}
