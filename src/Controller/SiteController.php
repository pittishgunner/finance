<?php

namespace App\Controller;

use App\Service\RecordsService;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use WebPush\Action;
use WebPush\Message;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\WebPush;


class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordsService $recordsService,
        private KernelInterface $kernel,
        private readonly WebPush $webpushService
    ) {

    }

    #[Route(path: '/notify', name: 'app_notify')]
    public function __invoke(Request $request): JsonResponse
    {
        $message = Message::create('My super Application', 'Hello World!')
            ->rtl()
            //->renotify()
            ->vibrate(200, 300, 200, 300)
            ->withImage('https://placebear.com/1024/512')
            ->withIcon('https://placebear.com/512/512')
            ->withBadge('https://placebear.com/256/256')
            //->withData(['foo' => 'BAR'])
            //->withTag('tag1')
            ->withLang('fr_FR')
            //->mute()
            ->withTimestamp(time())
            ->addAction(Action::create('accept', 'Accept'))
            ->addAction(Action::create('cancel', 'Cancel'))
        ;
        $notification = Notification::create()
            //->highUrgency()
            ->withPayload($message->toString());
        $subscription = Subscription::createFromString($request->getContent());

        $statusReport = $this->webpushService->send($notification, $subscription);
        //dd($statusReport);

        return new JsonResponse(
            [
                'error' => !$statusReport->isSuccess(),
                'links' => $statusReport->getLinks(),
                'location' => $statusReport->getLocation(),
                'expired' => $statusReport->isSubscriptionExpired(),
            ],
            $statusReport->isSuccess() ? 200 : 400,
        );
    }

    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        return $this->render('site/homepage.html.twig', [

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
