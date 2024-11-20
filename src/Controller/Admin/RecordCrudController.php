<?php

namespace App\Controller\Admin;

use App\Entity\Record;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use eduMedia\TagBundle\Admin\Field\TagField;

class RecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Record::class;
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('account')->setDisabled();
        yield DateField::new('date')->setDisabled();
        yield NumberField::new('debit')->setDisabled();
        yield NumberField::new('credit')->setDisabled();
        yield NumberField::new('balance')->setDisabled()->onlyOnDetail();
        if (!empty($_GET['query'])) {
            yield TextField::new('description')->setDisabled()->setMaxLength(60000);
        } else {
            yield TextField::new('description')->setDisabled()->setMaxLength(64);
        }
        yield AssociationField::new('category')->setDisabled();
        yield AssociationField::new('subCategory')->setDisabled();
        yield CodeEditorField::new('details')
            ->setLabel('Details')
            ->setLanguage('js')
            ->formatValue(fn ($value) => json_encode(json_decode($value, true), JSON_PRETTY_PRINT));
        yield TagField::new('tags');

        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Transaction')
            ->setEntityLabelInPlural('Transactions')
            ->setPageTitle(Crud::PAGE_INDEX, 'Transactions')
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(100)
            //->overrideTemplate('crud/index', 'admin/record/index.html.twig')
            //->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::BATCH_DELETE, Action::NEW)
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('date')
            ->add(EntityFilter::new('account')->canSelectMultiple())
            ->add('description')
            ->add('debit')
            ->add('credit')
            ->add('category')
            ->add('subCategory');
    }
}