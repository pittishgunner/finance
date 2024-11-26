<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class NotificationCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {

    }
    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield DateTimeField::new('createdAt')
            ->setDisabled();
        yield DateTimeField::new('originalTime')
            ->setDisabled();
        yield DateTimeField::new('sentAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield TextField::new('source')
            ->onlyOnDetail();
        yield TextField::new('message');
        yield TextField::new('result')
            ->renderAsHtml();
        yield CodeEditorField::new('content')
           ->setLanguage('js')
           ->formatValue(fn ($value) => json_encode(json_decode($value, true), JSON_PRETTY_PRINT))
           ->onlyOnDetail();
        yield TextField::new('ip')->onlyOnDetail();
        yield CodeEditorField::new('headers')->setLabel('Headers')
            ->setLanguage('js')
            ->formatValue(fn ($value) => json_encode(json_decode($value, true), JSON_PRETTY_PRINT))
            ->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Captured Notification')
            ->setEntityLabelInPlural('Captured Notifications')
            ->setPageTitle(Crud::PAGE_INDEX, 'Captured Notifications')
            ->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $showJsonAction = Action::new('showJson', 'Show json', 'fa fa-magnifying-glass text-success')
            ->addCssClass('text-success')
            ->linkToUrl(static function(Notification $notification) {
                return '#/Notification/getContent/' . $notification->getId();
            })
            ->setHtmlAttributes([
                'data-action' => 'click->json-modal#openJson',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#json-modal',
            ])
        ;

        return $actions
            ->disable(Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static function (Action $action) {
                return $action->setLabel('Show full details');
            })
            ->add(Crud::PAGE_INDEX, $showJsonAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('source')
            ->add('message')
            ->add('result')
            ->add('createdAt')
            ->add('ip')
            ->add('content');
    }
}
