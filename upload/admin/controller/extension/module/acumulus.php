<?php
/**
 * @noinspection AutoloadingIssuesInspection
 * @noinspection PhpMissingParamTypeInspection
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpUndefinedClassInspection
 */

declare(strict_types=1);

use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\OpenCart\Helpers\OcHelper;

/**
 * This is the Acumulus admin side controller.
 *
 * @property \Cart\User $user;
 */
class ControllerExtensionModuleAcumulus extends Controller
{
    private static OcHelper $ocHelper;
    private static Container $acumulusContainer;

    /**
     * Constructor.
     *
     * @param \Registry $registry
     */
    public function __construct($registry)
    {
        /** @noinspection DuplicatedCode */
        parent::__construct($registry);
        if (!isset(static::$ocHelper)) {
            // Load autoloader, container, and then our helper that contains
            // OC3 and OC4 shared code.
            require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
            SielAcumulusAutoloader::register();
            // Language will be set by the helper.
            static::$acumulusContainer = new Container('OpenCart\OpenCart3');
            static::$ocHelper = static::$acumulusContainer->getInstance(
                'OcHelper', 'Helpers', [$this->registry, static::$acumulusContainer]
            );
        }
    }

    /**
     * Returns the location of the extension's files.
     *
     * @return string
     *   The location of the extension's files.
     */
    protected function getRoute(): string
    {
        return \Siel\Acumulus\OpenCart\Helpers\Registry::getInstance()->getRoute('');
    }

    /**
     * Install controller action, called when the module is installed.
     *
     * @throws \Exception
     */
    public function install(): void
    {
        static::$ocHelper->install();
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     *
     * @throws \Exception
     */
    public function uninstall(): void
    {
        static::$ocHelper->uninstall();
    }

    /**
     * Main controller action: the config form.
     *
     * @throws \Throwable
     */
    public function index(): void
    {
        $this->settings();
    }

    /**
     * Controller action: show/process the basic settings form.
     *
     * @throws \Throwable
     */
    public function settings(): void
    {
        static::$ocHelper->settings();
    }

    /**
     * Controller action: show/process the mappings form.
     *
     * @throws \Throwable
     */
    public function mappings(): void
    {
        static::$ocHelper->mappings();
    }

    /**
     * Controller action: show/process the batch form.
     *
     * @throws \Throwable
     */
    public function batch(): void
    {
        static::$ocHelper->batch();
    }

    /**
     * Controller action: show/process the "Activate pro-support" form.
     *
     * @throws \Throwable
     */
    public function activate(): void
    {
        static::$ocHelper->activate();
    }

    /**
     * Controller action: show/process the register form.
     *
     * @throws \Throwable
     */
    public function register(): void
    {
        static::$ocHelper->register();
    }

    /**
     * Controller action: show/process the invoice status overview form.
     *
     * @throws \Throwable
     */
    public function invoice(): void
    {
        static::$ocHelper->invoice();
    }

    /**
     * Event handler that executes on the creation or update of an order.
     *
     * The arguments passed in depend on the version of OC (and possibly if it
     * is OC self or another plugin that triggered the event).
     *
     * Note: in admin it can only be another plugin as OC self redirects to the
     * catalog part to update an order.
     *
     * @noinspection PhpUnused event handler
     */
    public function eventOrderUpdate(...$args): void
    {
        $order_id = static::$ocHelper->extractOrderId($args);
        static::$ocHelper->eventOrderUpdate($order_id);
    }

    /**
     * Adds our menu-items to the admin menu.
     *
     * @param string $route
     *   The current route (common/column_left).
     * @param array $data
     *   The data as will be passed to the view.
     *
     * @noinspection PhpUnused : event handler
     */
    public function eventViewColumnLeft(/** @noinspection PhpUnusedParameterInspection */ $route, &$data): void
    {
        if ($this->user->hasPermission('access', $this->getRoute())) {
            static::$ocHelper->eventViewColumnLeft($data['menus']);
        }
    }

    /**
     * Adds our menu-items to the admin menu.
     *
     * param string $route
     *   The current route (common/column_left).
     * param array $data
     *   The data as will be passed to the view.
     * param string $code
     *
     * @noinspection PhpUnused : event handler
     */
    public function eventControllerSaleOrderInfo(): void
    {
        if ($this->user->hasPermission('access', $this->getRoute())) {
            static::$ocHelper->eventControllerSaleOrderInfo();
        }
    }

    /**
     * Adds our menu-items to the admin menu.
     *
     * @param string $route
     *   The current route (common/column_left).
     * @param array $data
     *   The data as will be passed to the view.
     * @param string $code
     *
     * @throws \Throwable
     *
     * @noinspection PhpUnused : event handler
     * @noinspection PhpUnusedParameterInspection
     */
    public function eventViewSaleOrderInfo($route, &$data, &$code): void
    {
        if ($this->user->hasPermission('access', $this->getRoute())) {
            static::$ocHelper->eventViewSaleOrderInfo((int) $data['order_id'], $data['tabs']);
        }
    }
}
