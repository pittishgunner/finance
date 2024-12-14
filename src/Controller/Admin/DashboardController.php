<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Category;
use App\Entity\CategoryRule;
use App\Entity\CommandResult;
use App\Entity\Notification;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Repository\AccountRepository;
use App\Service\ChartDataService;
use App\Service\RecordsService;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    private $dateRange = [];

    private $accounts = [];

    public function __construct(
        private RequestStack      $requestStack,
        private AdminUrlGenerator $adminUrlGenerator,
        private RecordsService  $recordsService,
        private ChartDataService  $chartDataService,
        private AccountRepository $accountRepository,
    ) {
        $session = $this->requestStack->getSession();
        $currentFilters = $this->requestStack->getCurrentRequest()->request->all();
        if (null === $session->get('dateRange')) {
            $from = new DateTime('now -7 days');
            $to = new DateTime();
            $dateRange = [
                'readable' => $from->format('d M Y') . ' - ' . $to->format('d M Y'),
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ];

            $this->dateRange = $dateRange;

            $session->set('dateRange', $dateRange);
        } else {
            $this->dateRange = $session->get('dateRange');
        }

        if (null === $session->get('accounts')) {
            $availableAccounts = $this->accountRepository->findBy(['enabled' => true], ['id' => 'DESC']);
            $selected = [];
            foreach ($availableAccounts as $account) {
                if ($account->isDefaultAccount()) {
                    $selected[] = $account->getId();
                }
            }
            $accounts = [
                'accounts' => $availableAccounts,
                'selected' => $selected,
            ];

            $this->accounts = $accounts;
            $session->set('accounts', $accounts);
        } else {
            $this->accounts = $session->get('accounts');
        }

        // This should handle changing from filters and not from range picker
        if (
            isset($_GET['filters']['date']['comparison']) &&
            isset($_GET['filters']['date']['value']) &&
            isset($_GET['filters']['date']['value2']) &&
            (
                $_GET['filters']['date']['value'] !== $this->dateRange['from'] ||
                $_GET['filters']['date']['value2'] !== $this->dateRange['to']
            )
        ) {
            if (!isset($_GET['skipSettingSession'])) {
                $from = DateTime::createFromFormat('Y-m-d', $_GET['filters']['date']['value']);
                $to = DateTime::createFromFormat('Y-m-d', $_GET['filters']['date']['value2']);
                if ($from && $to) {
                    $this->dateRange = [
                        'readable' => $from->format('d M Y') . ' - ' . $to->format('d M Y') ?? '',
                        'from' => $_GET['filters']['date']['value'],
                        'to' => $_GET['filters']['date']['value2'],
                    ];

                    $session->set('dateRange', $this->dateRange);
                }
            }
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(ChartBuilderInterface $chartBuilder = null): Response
    {
        assert(null !== $chartBuilder);
        $type = $this->requestStack->getCurrentRequest()->query->get('type') ?? 'expenses';
        $graphType = $this->requestStack->getCurrentRequest()->query->get('graphType') ?? 'daily';

        return $this->render('admin/index.html.twig', [
            'chart' => $this->createChart($chartBuilder, $type, $graphType),
            'tagsChart' => $this->createTagsChart($chartBuilder, $type, $graphType),
            'type' => $type,
            'graphType' => $graphType,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/test_react', name: 'admin_react_test')]
    public function testReact(ChartBuilderInterface $chartBuilder = null): Response
    {
//        $p = new INGB('INGB');
//        $r = [
//            'Ai autorizat tranzactia de 470.79 RON la PENNY HUNED3 4495 C2 din contul 999901181524 in 21-11-2024. Sold: 1,204.3 RON.',
//            'Suma 10.26 RON a fost creditata in 21-11-2024 in contul 999901181524 - Cumparare POS corectie la APPLE.COM/BILL. Sold: 1,675.09.',
//            'Suma 72.8 RON a fost debitata in 21-11-2024 din contul 999901181524 - Plata debit direct catre PPC ENERGIE S.A.. Sold: 1,664.83 RON.',
//            'Ai economisit 2.5 RON prin Round Up.',
//            'Ai autorizat tranzactia de 39.5 RON la PENNY HUNED3 4495 C3 din contul 999901181524 in 20-11-2024. Sold: 1,740.13.',
//            'Ai economisit 9.7 RON prin Round Up.',
//            'Ai autorizat tranzactia de 608.06 RON la RO 0040 Deva din contul 999901181524 in 20-11-2024. Sold: 1,789.33.',
//            'Ai autorizat tranzactia de 400 RON la Revolut**3887* din contul 999901181524 in 20-11-2024. Sold: 2,397.39.',
//            'Ai autorizat tranzactia de 183.98 RON la Glovo 19NOV HD2DCL1RR din contul 999908493441 in 19-11-2024. Sold: 169,863.47.',
//            'Ai economisit 19 RON prin Round Up.',
//            'Suma 170.04 RON a fost debitata in 21-11-2024 din contul 999901181524 - Transfer Home\'Bank catre Cord Blood Center Storage AG. Sold: 3,514.83 RON.',
//            'Suma 170.04 RON a fost debitata in 21-11-2024 din contul 999901181524 - Transfer Home\'Bank catre Cord Blood Center Storage AG. Sold: 3,684.87 RON.',
//            'Apasa aici pentru a aproba sau anula (din Home',
//            'Tocmai ai incercat sa accesezi Home'
//        ];
//        foreach ($r as $s) {
//            print_r($p->predictRecord($s));
//        }
//
//        exit;

        assert(null !== $chartBuilder);

        return $this->render('admin/react.html.twig', [
            'chart' => $this->createChart($chartBuilder),
        ]);
    }

    /**
     * @throws Exception
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/unmatched_records', name: 'admin_unmatched_records')]
    public function unmatchedRecords(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $content = '';

        if (isset($_GET['importToo'])) {
            $input = new ArrayInput([
                'command' => 'import-csv',
            ]);

            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $application->run($input, $output);

            // return the output, don't use if you used NullOutput()
            $content .= $output->fetch();
        }


        $input = new ArrayInput([
            'command' => 'assign-categories',
            '--force' => 'false',
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content .= $output->fetch();

        return $this->render('admin/unmatched.html.twig', [
            'content' => $content,
            'records' => $this->recordsService->getUnmatchedRecords($this->dateRange['from'], $this->dateRange['to'], $this->accounts['selected']),
            'rules' => $this->recordsService->getRulesTree(),
        ]);
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Munteanu Finance');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::section('Transactions')->setPermission('ROLE_MODERATOR');

        $filters = [
            'date' => [
                'comparison' => 'between',
                'value' => $this->dateRange['from'],
                'value2' => $this->dateRange['to'],
            ],
            'account' => [
                'comparison' => '=',
                'value' => $this->accounts['selected'],
            ]
        ];

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(RecordCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters', $filters)
        ;

        yield MenuItem::linkToUrl(
            'Transactions',
            'fa-solid fa-arrows-turn-right',
                $url->generateUrl()
            )
            ->setPermission('ROLE_MODERATOR');
        yield MenuItem::subMenu('Categories', 'fa fa-bars')
            ->setSubItems([
                MenuItem::linkToCrud('All', 'fa fa-bars', Category::class)
                    ->setPermission('ROLE_MODERATOR'),
                MenuItem::linkToCrud('Subcategories', 'fa fa-bars-staggered', SubCategory::class)
                    ->setPermission('ROLE_MODERATOR'),
            ]);
        yield MenuItem::linkToCrud('Category rules', 'fa fa-wand-sparkles', CategoryRule::class);
        yield MenuItem::linkToRoute('Unmatched records','fa-solid fa-triangle-exclamation', 'admin_unmatched_records');


        yield MenuItem::section('Accounts');
        yield MenuItem::linkToCrud('Accounts', 'fa fa-user', Account::class);

        yield MenuItem::section('Miscellaneous');
        yield MenuItem::linkToCrud('Command results', 'fa fa-terminal', CommandResult::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class)
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Captured Notifications', 'fas fa-arrow-down-short-wide', Notification::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToRoute('React tree','fa-solid fa-bars-staggered', 'admin_react_test');

        yield MenuItem::section('External');
        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', $this->generateUrl('app_homepage'));
        yield MenuItem::linkToUrl('API Docs', 'fas fa-home', '/api/docs');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatarUrl())
            ->addMenuItems([
                MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl('app_profile_show'))
            ]);
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDefaultSort([
                'id' => 'DESC',
            ])
            ->overrideTemplate('crud/field/id', 'admin/field/id_with_icon.html.twig')
            ->setThousandsSeparator('')
            ->setDateFormat('YYYY-MM-dd')
            ->setTimeFormat('HH:mm:ss')

            // first argument = datetime pattern or date format; second optional argument = time format
            ->setDateTimeFormat('YYYY-MM-dd HH:mm:ss')

            //->setDateIntervalFormat('%%y Year(s) %%m Month(s) %%d Day(s)')
            ->setTimezone('Europe/Bucharest');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_DETAIL, Action::EDIT, static function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, static function (Action $action) {
                return $action->setIcon('fa fa-list');
            });
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }

    private function createChart(ChartBuilderInterface $chartBuilder, string $type = 'expenses', string $graphType = 'daily'): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData($this->chartDataService->groupedRecords($this->dateRange['from'], $this->dateRange['to'], $type, $graphType, $this->accounts['selected']));

        $chart->setOptions([
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                       // 'wheel' => ['enabled' => true],
                        //'pinch' => ['enabled' => true],
                        'drag' => ['enabled' => true],
                        'mode' => 'x',
                    ],
                    /*'pan' => [
                        'enabled' => true,
                        'mode' => 'x',
                    ],*/
                ],
                'annotation' => [
                    'annotations' => [
                        [
                            'type' => 'line',
                        ]
                    ]
                ],
                'autocolors' => [
                    'enabled' => true,
                ],
                'legend' => [
                    'display' => false,
                ],
                'htmlLegend' => [
                    'containerID' => 'legend-container',
                ],
                'tooltip' => [
                    'callbacks' => [ ],
                ]
            ],
            'responsive' => true,
            'scales' => [
                'x' => [
                   'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ]);


        //dd($chart->getOptions());


        return $chart;
    }

    private function createTagsChart(ChartBuilderInterface $chartBuilder, string $type = 'expenses', string $graphType = 'daily'): Chart
    {
        $chart = $chartBuilder->createChart('wordCloud');
        $chart->setData($this->chartDataService->tagCount($this->dateRange['from'], $this->dateRange['to'], $type, $graphType, $this->accounts['selected']));

        /*$chart->setData([
            'labels' => ['Hello', 'world', 'normally', 'you', 'want', 'more', 'words', 'than', 'this'],
            'datasets' => [
                [
                    'label' => 'DS',
                    // size in pixel
                    'data' => [90, 80, 70, 60, 50, 40, 30, 20, 10],
                ],
            ]
        ]);*/
        $chart->setOptions([
            'elements' => [
                'word' => [
//                    'strokeStyle' => 'red',
//                    'strokeWidth' => 8,
                ]
            ],
            'title' => [
                'display' => true,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ]
            ]
        ]);

       /* $chart->setOptions([
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                        // 'wheel' => ['enabled' => true],
                        //'pinch' => ['enabled' => true],
                        'drag' => ['enabled' => true],
                        'mode' => 'x',
                    ],
                ],
                'annotation' => [
                    'annotations' => [
                        [
                            'type' => 'line',
                        ]
                    ]
                ],
                'autocolors' => [
                    'enabled' => true,
                ],
                'legend' => [
                    'display' => false,
                ],
                'htmlLegend' => [
                    'containerID' => 'legend-container',
                ],
                'tooltip' => [
                    'callbacks' => [ ],
                ]
            ],
            'responsive' => true,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ]);*/


        //dd($chart->getOptions());


        return $chart;
    }
}
