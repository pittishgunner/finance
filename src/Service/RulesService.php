<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryRule;
use App\Entity\SubCategory;
use App\Repository\AccountRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategoryRuleRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class RulesService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository     $categoryRepository,
        private SubCategoryRepository  $subCategoryRepository,
        private CategoryRuleRepository $categoryRuleRepository,
        private readonly AccountRepository $accountRepository,
    ) {}

    /**
     * @throws Exception
     */
    public function importData(array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        $connection->executeQuery('UPDATE record SET category_id = NULL, sub_category_id = NULL;');
        foreach (['category', 'category_rule', 'sub_category', 'tag', 'tagging'] as $table) {
            $truncateSql = $platform->getTruncateTableSQL($table);
            $connection->executeStatement($truncateSql);
        }
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
        foreach ($data as $category) {
            $Category = new Category();
            $Category->setName($category['name']);

            $this->entityManager->persist($Category);
            foreach ($category['subcategories'] as $subCategory) {
                $SubCategory = new SubCategory();
                $SubCategory->setCategory($Category);
                $SubCategory->setName($subCategory['name']);

                $this->entityManager->persist($SubCategory);
                foreach ($subCategory['rules'] as $rule) {
                    $CategoryRule = new CategoryRule();
                    $CategoryRule->setCategory($Category);
                    $CategoryRule->setSubCategory($SubCategory);
                    $CategoryRule->setName($rule['name'] ?? '');
                    if (!empty($rule['accountId'])) {
                        $Account = $this->accountRepository->find($rule['accountId']);
                        if (!empty($Account)) {
                            $CategoryRule->setAccount($Account);
                        }
                    }
                    $CategoryRule->setMatches($rule['matches']);
                    $CategoryRule->setDebit($rule['debit']);
                    $CategoryRule->setCredit($rule['credit']);
                    $CategoryRule->setEnabled($rule['enabled']);

                    $this->entityManager->persist($CategoryRule);
                }
            }
        }

        $this->entityManager->flush();
    }

    public function validateRules(array $data): bool
    {
        foreach ($data as $category) {
            if (empty($category['name'])) {
                return false;
            }
            if (!isset($category['subcategories'])) {
                return false;
            } else {
                foreach ($category['subcategories'] as $subcategory) {
                    if (empty($subcategory['name'])) {
                        return false;
                    }
                    if (!isset($subcategory['rules'])) {
                        return false;
                    } else {
                        foreach ($subcategory['rules'] as $rule) {
                            if (!isset($rule['name'])) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function getExportData(): array
    {
        $d = [];
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as $category) {
            $subcategories = [];
            $subcategoryEntities = $this->subCategoryRepository->findBy(['category' => $category]);
            foreach ($subcategoryEntities as $subCategory) {
                $rules = [];
                $ruleEntities = $this->categoryRuleRepository->findBy(['subCategory' => $subCategory]);
                foreach ($ruleEntities as $ruleEntity) {
                    $rules[] = [
                        'name' => $ruleEntity->getName(),
                        'accountId' => $ruleEntity->getAccount()?->getId(),
                        'matches' => $ruleEntity->getMatches(),
                        'debit' => $ruleEntity->getDebit(),
                        'credit' => $ruleEntity->getCredit(),
                        'enabled' => $ruleEntity->isEnabled(),
                    ];
                }
                $subcategories[] = [
                    'name' => $subCategory->getName(),
                    'rules' => $rules,
                ];
            }
            $d[] = [
                'name' => $category->getName(),
                'subcategories' => $subcategories,
            ];
        }
        return $d;
    }
}
