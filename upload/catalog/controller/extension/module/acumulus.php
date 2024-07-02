<?php
/**
 * @noinspection AutoloadingIssuesInspection
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection DuplicatedCode
 */

declare(strict_types=1);

use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\OpenCart\OpenCart3\Helpers\OcHelper;

/**
 * This is the Acumulus controller for the catalog side.
 */
class ControllerExtensionModuleAcumulus extends Controller
{
    private static OcHelper $ocHelper;

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
            // Load autoloader, container and then our helper that contains
            // OC3 and OC4 shared code.
            require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
            SielAcumulusAutoloader::register();
            $container = new Container('OpenCart\OpenCart3');
            static::$ocHelper = $container->getInstance('OcHelper', 'Helpers', [$this->registry, $container]);
        }
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
     * @noinspection PhpUnused
     */
    public function eventOrderUpdate(...$args): void
    {
        $order_id = static::$ocHelper->extractOrderId($args);
        static::$ocHelper->eventOrderUpdate($order_id);
    }
}
