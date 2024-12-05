<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setDisabled()->onlyOnDetail();
        yield TextField::new('alias');
        yield TextField::new('bank');
        yield TextField::new('currency');
        yield BooleanField::new('defaultAccount')
            ->setHelp('Default accounts will be pre-selected in the filters')
        ;
        yield BooleanField::new('enabled');
        yield TextField::new('iban');
        yield TextField::new('description');

        yield DateTimeField::new('createdAt')->onlyOnDetail();
    }
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->showEntityActionsInlined()
        ;
    }
}
