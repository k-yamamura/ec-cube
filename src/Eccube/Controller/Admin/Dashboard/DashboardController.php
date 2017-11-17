<?php

namespace Eccube\Controller\Admin\Dashboard;

use Doctrine\ORM\EntityManager;
use Eccube\Annotation\Inject;
use Eccube\Application;
use Eccube\Entity\Member;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\DeliveryRepository;
use Eccube\Repository\Master\ShippingStatusRepository;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\ShippingRepository;
use Eccube\Service\TaxRuleService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route(service=DashboardController::class)
 */
class DashboardController
{
    /**
     * @Inject(OrderItemRepository::class)
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @Inject(CategoryRepository::class)
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @Inject("session")
     * @var Session
     */
    protected $session;

    /**
     * @Inject("config")
     * @var array
     */
    protected $appConfig;

    /**
     * @Inject("monolog")
     * @var Logger
     */
    protected $logger;

    /**
     * @Inject("serializer")
     * @var Serializer
     */
    protected $serializer;

    /**
     * @Inject(DeliveryRepository::class)
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @Inject(TaxRuleService::class)
     * @var TaxRuleService
     */
    protected $taxRuleService;

    /**
     * @Inject("eccube.event.dispatcher")
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @Inject("form.factory")
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @Inject(ShippingRepository::class)
     * @var ShippingRepository
     */
    protected $shippingRepository;

    /**
     * @Inject(ShippingStatusRepository::class)
     * @var ShippingStatusRepository
     */
    protected $shippingStatusReposisotry;

    /**
     * @Inject("orm.em")
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * 出荷登録/編集画面.
     *
     * @Route("/{_admin}/dashboard/{column}", name="admin_dashboard")
     * @Template("Dashboard/dashboard.twig")
     *
     */
    public function index(Application $app, Request $request, $column = 1)
    {

        /** @var Member $Member */
        $Member = $app->user();

        $dashboard = json_decode($Member->getDashboard(), true);
        $Widgets = null;
        if (isset($dashboard['column'.$column][0])) {
            $Widgets = $dashboard['column'.$column][0];
        }

        return [
            'Widgets' => $Widgets,
        ];
    }

}
