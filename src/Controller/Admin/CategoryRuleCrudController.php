<?php

namespace App\Controller\Admin;

use App\Entity\CategoryRule;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryRuleCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private UrlGeneratorInterface $urlGenerator,
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
        yield IdField::new('id')->setDisabled()->onlyOnDetail();
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

        $export = Action::new('export', 'Export Current Rules and Categories')
            ->linkToRoute('admin_export_category_rules')
            ->setHtmlAttributes(['target' => '_blank'])
            ->createAsGlobalAction()
        ;

        $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_INDEX, $export)
            //->disable(Crud::PAGE_DETAIL)
        ;

        return parent::configureActions($actions);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Category Rule')
            ->setEntityLabelInPlural('Category rules')
            ->setPageTitle(Crud::PAGE_INDEX, 'Category Rules ' . $this->getImportRulesHtml())
            ->setDefaultSort(['category' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->showEntityActionsInlined();
    }

    private function getImportRulesHtml()
    {
        return '
<form action="' . $this->generateUrl('admin_import_category_rules') . '" method="POST" enctype="multipart/form-data"
    data-controller="submit-confirm"
    data-action="submit-confirm#onSubmit"
    data-submit-confirm-title-value="This will delete all your current Categories, Subcategories and Rules!"
    data-submit-confirm-icon-value="warning"
    data-submit-confirm-confirm-button-text-value="Yes, I want to proceed"
    data-submit-confirm-submit-async-value=""
>
    <button id="importRulesButton" class="btn-info btn">
        Replace Rules by JSON <span></span>
    </button>
    <input id="importRulesFile" type="file" accept="application/JSON" name="importRulesFile" class="d-none" />
    <button id="importRulesSubmit" class="btn-primary btn d-none">
        Import
    </button>        
</form>';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('account');
    }
}
