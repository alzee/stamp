<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Fingerprint;
use App\Entity\Organization;
use App\Entity\Device;
use App\Entity\Wecom;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator
                    ->setController(OrganizationCrudController::class)
                    ->generateUrl()
        );

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Stamp');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Fingerprint', 'fas fa-list', Fingerprint::class);
        yield MenuItem::linkToCrud('Organization', 'fas fa-list', Organization::class);
        yield MenuItem::linkToCrud('Device', 'fas fa-list', Device::class);
        yield MenuItem::linkToCrud('Wecom', 'fas fa-list', Wecom::class);
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
    }
}
