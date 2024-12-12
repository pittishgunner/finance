<?php

namespace App\Controller\Admin;

use App\Entity\Record;
use App\Repository\AccountRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use eduMedia\TagBundle\Admin\Field\TagField;

class RecordCrudController extends AbstractCrudController
{
    public function __construct(private readonly AccountRepository $accountRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Record::class;
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        if (!isset($_GET['filters']['account']['value']) || count($_GET['filters']['account']['value']) !== 1) {
            yield TextField::new('account')->setDisabled();
        }
        yield DateField::new('date')->setDisabled();
        yield NumberField::new('debit')->setDisabled();
        yield NumberField::new('credit')->setDisabled();
        yield NumberField::new('balance')->setDisabled()->onlyOnDetail();
        if (!empty($_GET['query'])) {
            yield TextField::new('description')->setDisabled()->setMaxLength(60000);
        } else {
            yield TextField::new('description')->setDisabled()->setMaxLength(44);
        }
        yield AssociationField::new('category')->setDisabled();
        yield AssociationField::new('subCategory')->setDisabled();
        yield CodeEditorField::new('details')
            ->setLabel('Details')
            ->setLanguage('js')
            ->formatValue(fn ($value) => json_encode(json_decode($value, true), JSON_PRETTY_PRINT))
            ->onlyOnDetail();
        yield TagField::new('tags');

        yield DateTimeField::new('notifiedAt')->setDisabled();
        yield BooleanField::new('reconciled')->renderAsSwitch(false);
        yield BooleanField::new('ignored')->setHelp('Ignored on graphs');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Transaction')
            ->setEntityLabelInPlural('Transactions')
            ->setPageTitle(Crud::PAGE_INDEX, 'Transactions ' . $this->getImportRecordsHtml())
            ->setDefaultSort(['notifiedAt' => 'DESC', 'date' => 'DESC'])
            ->setPaginatorPageSize(500)
            ->overrideTemplate('crud/index', 'admin/record/index.html.twig')
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
            ->add(EntityFilter::new('category')->canSelectMultiple())
            ->add(EntityFilter::new('subCategory')->canSelectMultiple())
        ;
    }

    private function getImportRecordsHtml()
    {
        $accounts = $this->accountRepository->findAll();
        $selection = '';
        foreach ($accounts as $account) {
            $selection .= '<option value="' . $account->getId() . '">' . $account->getAlias() . '</option>';
        }
        return '
<form action="' . $this->generateUrl('admin_import_records_file') . '" method="POST" enctype="multipart/form-data"
    data-controller="submit-confirm"
    data-action="submit-confirm#onSubmit"
    data-submit-confirm-title-value="This will delete all your current Categories, Subcategories and Rules!"
    data-submit-confirm-icon-value="warning"
    data-submit-confirm-confirm-button-text-value="Yes, I want to proceed"
    data-submit-confirm-submit-async-value=""
    style="display: inline-block"
>
    <button id="importRecordsButton" class="btn-info btn">
        Import Transactions CSV File <span></span>
    </button>
    <input id="importRecordsFile" type="file" accept=".csv" name="importRecordsFile" class="d-none" />
    <select id="importRecordsAccount" name="importRecordsAccount"
            style="font-size: 16px" class="d-none">
        <option value="">Please select an account</option>
        ' . $selection . '        
    </select>
    <button id="importRecordsSubmit" class="btn-primary btn d-none">
        Import
    </button>        
</form>';
    }
}
