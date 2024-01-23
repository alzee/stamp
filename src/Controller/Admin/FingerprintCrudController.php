<?php

namespace App\Controller\Admin;

use App\Entity\Fingerprint;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class FingerprintCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Fingerprint::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username');
        yield AssociationField::new('device');
    }
}
