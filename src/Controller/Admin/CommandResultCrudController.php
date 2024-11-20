<?php

namespace App\Controller\Admin;

use App\Entity\CommandResult;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommandResultCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandResult::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::BATCH_DELETE)
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('date'),
            TextField::new('command'),
            TextField::new('result')->formatValue(static function ($value, ?CommandResult $carrier) {
                return strstr($value, 'error') ?
                    '<span class="badge badge-danger">error</span>' :
                    '<span class="badge badge-success">success</span>';
            }),
            TextField::new('output'),
            NumberField::new('duration'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('command')
            ->add('date')
            ->add('result');
    }
}
