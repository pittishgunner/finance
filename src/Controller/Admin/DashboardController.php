<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategoryRule;
use App\Entity\CommandResult;
use App\Entity\Record;
use App\Entity\SubCategory;
use App\Entity\User;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    private $dateRange = [];

    public function __construct(
        private RequestStack $requestStack,
        private AdminUrlGenerator $adminUrlGenerator,
        private RecordsService $recordsService,
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
            $from = DateTime::createFromFormat('Y-m-d', $_GET['filters']['date']['value']);
            $to = DateTime::createFromFormat('Y-m-d', $_GET['filters']['date']['value2']);
            $this->dateRange = [
                'readable' => $from->format('d M Y') . ' - ' . $to->format('d M Y'),
                'from' => $_GET['filters']['date']['value'],
                'to' => $_GET['filters']['date']['value2'],
            ];

            $session->set('dateRange', $this->dateRange);
        }
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(ChartBuilderInterface $chartBuilder = null): Response
    {
        assert(null !== $chartBuilder);

        return $this->render('admin/index.html.twig', [
            'chart' => $this->createChart($chartBuilder),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/test_react', name: 'admin_react_test')]
    public function testReact(ChartBuilderInterface $chartBuilder = null): Response
    {
        assert(null !== $chartBuilder);

        return $this->render('admin/react.html.twig', [
            'chart' => $this->createChart($chartBuilder),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/unmatched_records', name: 'admin_unmatched_records')]
    public function unmatchedRecords(KernelInterface $kernel): Response
    {
        set_time_limit(0);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'assign-categories',
            '--force' => 'false',
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();


        return $this->render('admin/unmatched.html.twig', [
            'content' => $content,
            'records' => $this->recordsService->getUnmatchedRecords(),
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

        yield MenuItem::linkToUrl(
            'Transactions',
            'fa-solid fa-arrows-turn-right',
            $this->adminUrlGenerator
                ->unsetAll()
                ->setController(RecordCrudController::class)
                ->setAction(Action::INDEX)
                ->set('filters[date][comparison]', 'between')
                ->set('filters[date][value]', $this->dateRange['from'])
                ->set('filters[date][value2]', $this->dateRange['to'])
                ->generateUrl()
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


        yield MenuItem::section('Miscellaneous');
        yield MenuItem::linkToCrud('Command results', 'fa fa-terminal', CommandResult::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class)
            ->setPermission('ROLE_SUPER_ADMIN');
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

    private function createChart(ChartBuilderInterface $chartBuilder): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                   'suggestedMin' => 0,
                   'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }
}
