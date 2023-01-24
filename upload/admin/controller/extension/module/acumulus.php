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
use Siel\Acumulus\OpenCart\OpenCart3\Helpers\OcHelper;

/**
 * This is the Acumulus admin side controller.
 *
 * @property \Cart\User $user;
 */
class ControllerExtensionModuleAcumulus extends Controller
{
    private static OcHelper $staticOcHelper;
    private OcHelper $ocHelper;

    /**
     * Constructor.
     *
     * @param \Registry $registry
     */
    public function __construct($registry)
    {
        /** @noinspection DuplicatedCode */
        parent::__construct($registry);
        if (isset($this->ocHelper)) {
            if (isset(static::$staticOcHelper)) {
                // Load autoloader, container and then our helper that contains
                // OC3 and OC4 shared code.
                require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
                SielAcumulusAutoloader::register();
                $container = new Container('OpenCart\OpenCart3');
                /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
                static::$staticOcHelper = $container->getInstance('OcHelper', 'Helpers', [$this->registry, $container]);
            }
            $this->ocHelper = static::$staticOcHelper;
        }
    }

    /**
     * Returns the location of the extension's files.
     *
     * @return string
     *   The location of the extension's files.
     */
    protected function getLocation(): string
    {
        return 'extension/module/acumulus';
    }

    /**
     * Install controller action, called when the module is installed.
     *
     * @throws \Exception
     */
    public function install(): void
    {
        $this->ocHelper->install();
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     *
     * @throws \Exception
     */
    public function uninstall(): void
    {
        $this->ocHelper->uninstall();
    }

    /**
     * Main controller action: show/process the basic settings form.
     *
     * @throws \Throwable
     */
    public function index(): void
    {
        $this->ocHelper->config();
    }

    /**
     * Controller action: show/process the advanced settings form.
     *
     * @throws \Throwable
     */
    public function advanced(): void
    {
        $this->ocHelper->advancedConfig();
    }

    /**
     * Controller action: show/process the batch form.
     *
     * @throws \Throwable
     */
    public function batch(): void
    {
        $this->ocHelper->batch();
    }

    /**
     * Controller action: show/process the "Activate pro-support" form.
     *
     * @throws \Throwable
     */
    public function activate(): void
    {
        $this->ocHelper->activate();
    }

    /**
     * Controller action: show/process the register form.
     *
     * @throws \Throwable
     */
    public function register(): void
    {
        $this->ocHelper->register();
    }

    /**
     * Controller action: show/process the invoice status overview form.
     *
     * @throws \Throwable
     */
    public function invoice(): void
    {
        $this->ocHelper->invoice();
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
        $order_id = $this->ocHelper->extractOrderId($args);
        $this->ocHelper->eventOrderUpdate($order_id);
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
    public function eventViewColumnLeft(/** @noinspection PhpUnusedParameterInspection */$route, &$data): void
    {
        if ($this->user->hasPermission('access', $this->getLocation())) {
            $this->ocHelper->eventViewColumnLeft($data['menus']);
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
        if ($this->user->hasPermission('access', $this->getLocation())) {
            $this->ocHelper->eventControllerSaleOrderInfo();
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
     */
    public function eventViewSaleOrderInfo(
        /** @noinspection PhpUnusedParameterInspection */$route,
        &$data,
        /** @noinspection PhpUnusedParameterInspection */&$code
    ): void {
        if ($this->user->hasPermission('access', $this->getLocation())) {
            $this->ocHelper->eventViewSaleOrderInfo($data['order_id'], $data['tabs']);
        }
    }
}
