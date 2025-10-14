<?php
/**
 * @noinspection AutoloadingIssuesInspection
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpUndefinedClassInspection
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
            // Load autoloader (OC3 only), container, and then our helper that contains
            // OC3 and OC4 shared code.
            static::registerAcumulusAutoloader();
            $container = new Container('OpenCart\OpenCart3');
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            static::$ocHelper = $container->getInstance('OcHelper', 'Helpers', [$this->registry, $container]);
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
