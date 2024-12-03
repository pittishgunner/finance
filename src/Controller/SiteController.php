<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Service\RecordsService;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordsService         $recordsService,
    ) {

    }

    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        return $this->render('site/homepage.html.twig', [

        ]);
    }

    #[Route('/admin/share/image/', name: 'app_share_image')]
    public function shareImage(Request $request): Response
    {
        $n = new Notification();
        $n->setContent($request->getContent());
        $this->entityManager->persist($n);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }

    #[Route('/notification/capture', name: 'app_capture_request')]
    public function captureRequest(): Response
    {
        $dateTime = new DateTimeImmutable();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        $content = null;
        if (!empty($_POST)) {
            $content = json_encode($_POST);
        }
        if (empty($content)) {
            $rawContent = fopen('php://input', 'rb');
            while (!feof($rawContent)) {
                $content .= fread($rawContent, 4096);
            }
            fclose($rawContent);
        }
        $message = $_REQUEST['message'] ?? null;
        $source = $_REQUEST['source'] ?? null;
        $decoded = json_decode($content, true);
        if (!empty($decoded['text'])) {
            $message = $decoded['text'];
        }
        if (!empty($decoded['packageName'])) {
            $source = $decoded['packageName'];
            if ($source === 'com.revolut.revolut') {
                $message = $decoded['title'] . ' ' . $message;
            }
        }
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        $Records = $this->recordsService->captureNotification($source, $message, $content, $ip, $headers);

        return new JsonResponse([
            'status' => 'ok',
            'date' => $dateTime->format(DateTimeInterface::ATOM),
            'added' => $Records === false ? 'ignored' : ( is_string($Records) ? $Records : 'matched count: ' . count($Records)),
        ]);
    }
}
