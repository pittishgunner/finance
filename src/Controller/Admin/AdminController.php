<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategoryRule;
use App\Entity\SubCategory;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Service\NotificationsService;
use App\Service\RulesService;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use WebPush\Exception\OperationException;
use WebPush\Subscription as WebPushSubscription;
use WebPush\WebPush;

class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly WebPush       $webPushService,
        private SubscriptionRepository $subscriptionRepository,
        private readonly NotificationsService $notificationsService,
        private readonly RulesService $rulesService,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {

    }

    /**
     * @throws OperationException
     * @throws JsonException
     */
    #[Route('/notify/subscribe', name: 'admin_notify_subscribe', methods: [Request::METHOD_POST])]
    public function notificationSubscribe(Request $request): Response
    {
        $subscription = WebPushSubscription::createFromString($request->getContent());
        $Subscription = new Subscription($subscription->getEndpoint());
        $Subscription->setUser($this->getUser());
        $Subscription->setSubscription($request->getContent());

        $this->entityManager->persist($Subscription);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/notify/unsubscribe', name: 'admin_notify_unsubscribe', methods: [Request::METHOD_POST])]
    public function notificationUnsubscribe(Request $request): Response
    {
        $Subscription = $this->subscriptionRepository->findOneBy(['user' => $this->getUser(), 'subscription' => $request->getContent()]);
        if ($Subscription !== null) {
            $this->entityManager->remove($Subscription);
            $this->entityManager->flush();
        }

        return new JsonResponse(['status' => 'ok']);
    }


    #[Route(path: '/notify/test', name: 'admin_notify_test', methods: [Request::METHOD_POST])]
    public function notificationTest(Request $request): JsonResponse
    {
        $notificationAndSubscription = $this->notificationsService->getNotificationByType($request->getContent());

        $statusReport = $this->webPushService->send(
            $notificationAndSubscription['notification'],
            $notificationAndSubscription['subscription'],
        );

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

    #[Route('/admin/setFilters', name: 'admin_set_filters', methods: [Request::METHOD_POST])]
    public function setFilters(Request $request, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $dateRange = $request->getPayload()->get('dateRange');
        $accounts = $request->get('accounts');
        $fromUrl = $request->getPayload()->get('fromUrl');
        if (null !== $dateRange) {
            $split = explode(' - ', $dateRange);
            $from = DateTime::createFromFormat('d M Y', $split[0]);
            $to = DateTime::createFromFormat('d M Y', $split[1]);
            if ($from && $to) {
                $request->getSession()->set('dateRange',
                    [
                        'readable' => $dateRange,
                        'from' => $from->format('Y-m-d'),
                        'to' => $to->format('Y-m-d'),
                    ]
                );
                $sessionAccounts = $request->getSession()->get('accounts');
                $sessionAccounts['selected'] = $accounts;

                $request->getSession()->set('accounts', $sessionAccounts);

                if (null !== $fromUrl) {
                    $parsed = parse_url($fromUrl);
                    if (!empty($parsed['query'])) {
                        parse_str($parsed['query'], $parsedQuery);
                        if (isset($parsedQuery['filters'])) {
                            $filters = [];

                            $g = $adminUrlGenerator
                                ->unsetAll()
                                ->setController(RecordCrudController::class)
                                ->setAction(Action::INDEX);
                            if (
                                isset($parsedQuery['filters']['date']['comparison']) &&
                                $parsedQuery['filters']['date']['comparison'] === 'between'
                            ) {
                                $filters['date'] = [
                                    'comparison' => 'between',
                                    'value' => $from->format('Y-m-d'),
                                    'value2' => $to->format('Y-m-d'),
                                ];
                            }
                            if (
                                isset($parsedQuery['filters']['account']['comparison']) &&
                                $parsedQuery['filters']['account']['comparison'] === '='
                            ) {
                                $filters['account'] = [
                                    'comparison' => '=',
                                    'value' => $accounts,
                                ];
                            }

                            $g->set('filters', $filters);

                            return new JsonResponse(['redirect' => $g->generateUrl()]);
                        }
                    }

                    return new JsonResponse(['redirect' => $fromUrl]);
                }
            }
        }

        return new JsonResponse(['redirect' => '/']);
    }

    #[Route('/admin/setNewMatchesToRule', name: 'admin_set_new_matches_to_rule', methods: [Request::METHOD_POST])]
    public function setNewMatchesToRule(Request $request): Response
    {
        $content = '{}';
        $categoryRuleRepo = $this->entityManager->getRepository(CategoryRule::class);
        $categoryRule = $categoryRuleRepo->find($request->get('ruleId'));
        if (null === $categoryRule) {
            throw new NotFoundHttpException();
        }

        $categoryRule->setMatches(array_merge(
            $categoryRule->getMatches(),
            $request->get('matches')
        ));

        $this->entityManager->flush();

        $response = new JsonResponse();
        return $response->setContent($content);
    }

    #[Route('/admin/getJsonData/{entityName}/{getColumnName}/{id<\d+>}', name: 'admin_get_json_data', methods: [Request::METHOD_POST])]
    public function getJsonData(string $entityName, string $getColumnName, int $id): Response
    {
        $content = '{}';
        $className = '\\App\\Entity\\' . $entityName;
        if (class_exists($className)) {
            $class = new $className();
            $repository = $this->entityManager->getRepository($class::class);
            $record = $repository->find($id);
            if (null === $record) {
                throw new NotFoundHttpException();
            }

            $content = $record->$getColumnName();
        }

        $response = new JsonResponse();
        return $response->setContent($content);
    }

    #[Route('/admin/get/subcategories', name: 'admin_get_subcategories', methods: [Request::METHOD_GET])]
    public function getSubcategories(Request $request): Response
    {
        $content = [];
        $categoryId = $request->get('category') ?? 0;
        if (!empty($categoryId)) {
            $categoryRepo = $this->entityManager->getRepository(Category::class);
            $category = $categoryRepo->find($categoryId);
            if (null !== $category) {
                $repository = $this->entityManager->getRepository(SubCategory::class);
                $records = $repository->findBy(['category' => $category]);
                foreach ($records as $record) {
                    $content[] = [
                        'text' => $record->getName(),
                        'value' => $record->getId(),
                    ];
                }
            }
        }

        $response = new JsonResponse();
        return $response->setContent(json_encode($content));
    }

    #[Route('/admin/export/categoryRules', name: 'admin_export_category_rules', methods: [Request::METHOD_GET])]
    public function exportCategoryRules(Request $request): Response
    {
        $dateTime = new DateTime();
        $fileName = $dateTime->format('Y-m-d-H-i-s') . '_category_rules.json';

        return new StreamedJsonResponse(
            $this->rulesService->getExportData(),
            200,
            [
                'Content-Type' => 'application/json; charset=utf-8',
                "Content-Disposition" => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $fileName,
                ),
                "Cache-Control" => "max-age=0"
            ]
        );
    }

    /**
     * @throws Exception
     */
    #[Route('/admin/import/categoryRules', name: 'admin_import_category_rules', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function importCategoryRules(Request $request): Response
    {
        $url = $this->adminUrlGenerator->setController(CategoryRuleCrudController::class)
            ->setAction(Action::INDEX);

        $file = $request->files->get('importRulesFile');
        if (null === $file) {
            $this->addFlash('danger', 'No file was uploaded.');

            return new RedirectResponse($url->generateUrl());
        }
        $rules = json_decode($file->getContent(), true);
        if (empty($rules)) {
            $this->addFlash('danger', 'Invalid file contents');

            return new RedirectResponse($url->generateUrl());
        }

        if (!$this->rulesService->validateRules($rules)) {
            $this->addFlash('danger', 'Invalid rules');

            return new RedirectResponse($url->generateUrl());
        }

        $this->rulesService->importData($rules);
        $this->addFlash('success', sprintf(
            'New rules have been installed! You may want to scan records by going to <a href="%s">Unmatched records</a>',
            '/admin?routeName=admin_unmatched_records'
        ));

        return new RedirectResponse($url->generateUrl());
    }
}
