<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {

    }

    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        return $this->render('site/homepage.html.twig', [

        ]);
    }
}
