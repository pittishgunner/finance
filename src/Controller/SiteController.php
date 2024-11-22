<?php

namespace App\Controller;

use App\Entity\CapturedRequest;
use App\Service\RecordsService;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordsService $recordsService,
        private KernelInterface $kernel,
    ) {

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


        $CapturedRequest = new CapturedRequest();
        $CapturedRequest->setCreatedAt($dateTime);
        $CapturedRequest->setIp($ip);
        $CapturedRequest->setContent($content);
        $CapturedRequest->setHeaders(json_encode(getallheaders()));
        $CapturedRequest->setSource($_REQUEST['source'] ?? null);
        $CapturedRequest->setMessage($_REQUEST['message'] ?? null);
        $CapturedRequest->setRequest(json_encode($_REQUEST));
        $CapturedRequest->setServer(json_encode($_SERVER));

        $Records = $this->recordsService->addRecordsByNotification($CapturedRequest);

        if ($Records !== false) {
            foreach ($Records as $record) {
                $CapturedRequest->addRecord($record);
            }
            if (count($Records) > 0) {
                set_time_limit(0);
                $application = new Application($this->kernel);
                $application->setAutoExit(false);

                $input = new ArrayInput([
                    'command' => 'assign-categories',
                    '--force' => 'false',
                ]);

                $output = new NullOutput();
                $application->run($input, $output);
            }

            $this->entityManager->persist($CapturedRequest);
            $this->entityManager->flush();
        }

        return new JsonResponse([
            'status' => 'ok',
            'date' => $dateTime->format(DateTimeInterface::ATOM),
            'added' => $Records === false ? 'ignored' : 'matched count: ' . count($Records),
        ]);
    }
}
