<?php

namespace App\Controller\Admin;

use App\Entity\CategoryRule;
use App\Repository\CategoryRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use eduMedia\TagBundle\Admin\Field\TagField;
use Insitaction\EasyAdminFieldsBundle\EasyAdminFieldsBundle;
use Insitaction\EasyAdminFieldsBundle\Field\DependentField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}

class CategoryRuleCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private CategoryRuleRepository $categoryRuleRepository,
        private RequestStack $requestStack,
    )
    {

    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets = parent::configureAssets($assets);

        return EasyAdminFieldsBundle::configureAssets($assets);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setDisabled();
        yield TextField::new('name');

        yield AssociationField::new('category', 'Category')
            ->formatValue(static function ($value) {
                return '<b>' . $value->getName() . '</b>';
            })
            ->setHelp('This category will be assigned if the rule matches');
        yield DependentField::adapt(
            AssociationField::new('subCategory'),
            [
                'callback_url' => $this->urlGenerator->generate('admin_get_subcategories', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'dependencies' => ['category'],
                'fetch_on_init' => !($pageName === 'edit')
            ]
        )
            ->setHelp('This subcategory will be assigned if the rule matches');

        yield ArrayField::new('matches', 'Matches')
            ->setHelp('A set of strings that need to match the "description". You can use a pipeline | to put the searchable string on the left and the assigned tag on the right. Eg: "DISTRI-HIPER|Auchan"')
            ->formatValue(static function ($matches) {
                return '<span title=\'' . (implode(", ", $matches)) . '\'>' .
                    ($matches[0] ?? '') . (count($matches) > 1 ? ' ....' : '') .
                    '</span>';
            })
        ;
        yield AssociationField::new('account', 'Account')
            ->setHelp('Restrict rule to specific account')
            ->hideOnIndex()
        ;
        yield TextField::new('debit', 'Debit')
            ->setHelp('For example "amount > 100" to only check expenses higher than 100')
            ->hideOnIndex()
        ;
        yield TextField::new('credit', 'Credit')
            ->setHelp('For example "amount > 100" to only check incomes higher than 100')
            ->hideOnIndex()
        ;

        yield TagField::new('tags')
            ->setHelp('These tags will be assigned if the rule matches');

        yield BooleanField::new('enabled')
            ->setHelp('');
    }

    public static function getEntityFqcn(): string
    {
        return CategoryRule::class;
    }

    public function edit(AdminContext $context): KeyValueStore|RedirectResponse|Response
    {
        if ($context->getRequest()->query->has('duplicate')) {
            $entity = $context->getEntity()->getInstance();
            /** @var CategoryRule $cloned */
            $cloned = clone $entity;
            $context->getEntity()->setInstance($cloned);
        }

        return parent::edit($context);
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplicate')
            ->linkToUrl(
                fn(CategoryRule $entity) => $this->adminUrlGenerator
                    ->setAction(Action::EDIT)
                    ->setEntityId($entity->getId())
                    ->set('duplicate', '1')
                    ->generateUrl()
            );

        $entityCount = $this->categoryRuleRepository->count([]);

        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Move to top'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);

        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Move up'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);

        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Move down'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);

        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Move to bottom'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);

        $actions
            ->add(Crud::PAGE_INDEX, $moveBottom)
            ->add(Crud::PAGE_INDEX, $moveDown)
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveTop)
            ->add(Crud::PAGE_INDEX, $duplicate)
            //->disable(Crud::PAGE_DETAIL)
            ->reorder(Crud::PAGE_INDEX, ['moveTop', 'moveUp', 'moveDown', 'moveBottom', Action::DELETE, Action::EDIT]);

        return parent::configureActions($actions);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['position' => 'ASC'])
            ->setPaginatorPageSize(100)
            ->showEntityActionsInlined();
    }

    public function moveTop(AdminContext $context): Response
    {
        return $this->move($context, Direction::Top);
    }

    public function moveUp(AdminContext $context): Response
    {
        return $this->move($context, Direction::Up);
    }

    public function moveDown(AdminContext $context): Response
    {
        return $this->move($context, Direction::Down);
    }

    public function moveBottom(AdminContext $context): Response
    {
        return $this->move($context, Direction::Bottom);
    }

    private function move(AdminContext $context, Direction $direction): Response
    {
        $object = $context->getEntity()->getInstance();
        $newPosition = match($direction) {
            Direction::Top => 0,
            Direction::Up => $object->getPosition() - 1,
            Direction::Down => $object->getPosition() + 1,
            Direction::Bottom => -1,
        };

        $object->setPosition($newPosition);
        $this->entityManager->flush();

        $this->addFlash('success', 'The element has been successfully moved.');

        return $this->redirect($context->getRequest()->headers->get('referer'));
    }

    public function createEntity(string $entityFqcn): CategoryRule
    {
        $entityCount = $this->categoryRuleRepository->count([]);
        $CategoryRule = new CategoryRule();
        $CategoryRule->setPosition($entityCount - 1);

        return $CategoryRule;
    }
}
