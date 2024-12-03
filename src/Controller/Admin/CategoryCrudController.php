<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(AdminUrlGenerator $adminUrlGenerator, RequestStack $requestStack)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->requestStack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureActions(Actions $actions): Actions
    {

        $subcategoriesAction = Action::new('subcategories', 'Subcategories', 'fa fa-bars-staggered')
            ->linkToUrl(
                fn(Category $connection) => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(SubCategoryCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[category][comparison]', '=')
                    ->set('filters[category][value]', $connection->getId())
                    ->generateUrl()
            );

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $subcategoriesAction)
            ->reorder(Crud::PAGE_DETAIL, [
                Action::EDIT,
                Action::INDEX,
                Action::DELETE,
            ]);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined();
    }
}
