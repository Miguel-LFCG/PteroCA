<?php

namespace App\Core\Controller\Panel;

use App\Core\Entity\TokenEarningLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TokenEarningLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TokenEarningLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Token Earning Log')
            ->setEntityLabelInPlural('Token Earning Logs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Token Earning Logs')
            ->setSearchFields(['user.email', 'method', 'ipAddress']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('user')
            ->setLabel('User')
            ->setCrudController(null);
        yield TextField::new('method')
            ->setLabel('Method')
            ->hideOnForm();
        yield MoneyField::new('amount')
            ->setCurrency('USD')
            ->setLabel('Amount')
            ->hideOnForm();
        yield TextField::new('ipAddress')
            ->setLabel('IP Address')
            ->hideOnForm();
        yield TextareaField::new('details')
            ->setLabel('Details')
            ->hideOnIndex();
        yield DateTimeField::new('createdAt')
            ->setLabel('Created At')
            ->hideOnForm();
    }
}
