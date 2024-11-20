<?php

namespace App\Controller\Admin;

use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class SubCategoryCrudController extends AbstractCrudController
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
        $this->filters = $_GET['filters'] ?? [];
    }

    public static function getEntityFqcn(): string
    {
        return SubCategory::class;
    }

    public function configureFields(string $pageName): iterable
    {
            yield IdField::new('id')->setDisabled()->onlyOnDetail();
            yield AssociationField::new('category', 'Category')
                ->formatValue(static function ($value) {
                    return '<b>' . $value->getName() . '</b>';
                });
            yield TextField::new('name');
    }

    public function configureCrud(Crud $crud): Crud
    {
        $categoryId = $this->getFilterCategoryId();
        $pageTitle = 'All Subcategories';
        if ($categoryId) {
            $Category = $this->categoryRepository->find($categoryId);
            $pageTitle = 'Subcategories for: ' . $Category->getName();
        }

        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Subcategories')
            ->setEntityLabelInPlural('Subcategory')
            ->setPageTitle(Crud::PAGE_INDEX, $pageTitle)
            ->setDefaultSort(['category' => 'ASC', 'name' => 'ASC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(100);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(NumericFilter::new('category'));
    }

    private function getFilterCategoryId(): ?int
    {
        return (empty($this->filters['category']['value']) ? null : (int) $this->filters['category']['value']);
    }
}
