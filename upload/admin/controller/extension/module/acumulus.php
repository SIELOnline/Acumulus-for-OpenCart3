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
            // Load autoloader (OC3 only), container, and then our helper that contains
            // OC3 and OC4 shared code.
            static::registerAcumulusAutoloader();
            // Language will be set by the helper.
            static::$acumulusContainer = new Container('OpenCart\OpenCart3');
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            static::$ocHelper = static::$acumulusContainer->getInstance(
                'OcHelper',
                'Helpers',
                [$this->registry, static::$acumulusContainer]
            );
        }
    }

    /**
     * Registers an autoloader for the Siel\Acumulus namespace.
     *
     * As not all web shops support autoloading based on namespaces or have
     * other glitches, e.g. expecting lower cased file names, we define our own
     * autoloader. If the module cannot use the autoloader of the web shop, this
     * method should be called when bootstrapping the module.
     *
     * Thanks to https://gist.github.com/mageekguy/8300961
     */
    protected static function registerAcumulusAutoloader(): void
    {
        /** @noinspection DuplicatedCode */
        // In some shops (OpenCart1) there's not one central entry point, and
        // we may risk registering twice.
        static $hasBeenRegistered = false;

        if (!$hasBeenRegistered) {
            $dir = DIR_SYSTEM . 'library/siel/acumulus/src/';
            $ourNamespace = 'Siel\\Acumulus\\';
            $ourNamespaceLen = strlen($ourNamespace);
            $autoloadFunction = static function ($class) use ($ourNamespace, $ourNamespaceLen, $dir) {
                if (strncmp($class, $ourNamespace, $ourNamespaceLen) === 0) {
                    $fileName = $dir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $ourNamespaceLen)) . '.php';
                    if (is_readable($fileName)) {
                        include($fileName);
                    }
                }
            };
            // Prepend this autoloader: it will not throw, nor warn, while the
            // shop specific autoloader might do so.
            $hasBeenRegistered = spl_autoload_register($autoloadFunction, true, true);
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
     * Param string $route
     *   The current route (common/column_left).
     * Param array $data
     *   The data as will be passed to the view.
     * Param string $code
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
