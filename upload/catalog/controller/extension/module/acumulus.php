<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection DuplicatedCode
 */
use Siel\Acumulus\Helpers\Container;

/*
 * This is the Acumulus controller for the catalog side.
 */
class ControllerExtensionModuleAcumulus extends Controller
{
    /** @var \Siel\Acumulus\OpenCart\Helpers\OcHelper */
    static private $staticOcHelper = null;

    /** @var \Siel\Acumulus\OpenCart\Helpers\OcHelper */
    private $ocHelper = null;

    /**
     * Constructor.
     *
     * @param \Registry $registry
     */
    public function __construct($registry)
    {
        /** @noinspection DuplicatedCode */
        parent::__construct($registry);
        if ($this->ocHelper === NULL) {
            if (static::$staticOcHelper === NULL) {
                // Load autoloader, container and then our helper that contains
                // OC1, OC2 and OC3 shared code.
                require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
                SielAcumulusAutoloader::register();
                $container = new Container('OpenCart');
                static::$staticOcHelper = $container->getInstance('OcHelper', 'Helpers', array($this->registry, $container));
            }
            $this->ocHelper = static::$staticOcHelper;
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
    public function eventOrderUpdate()
    {
        $order_id = $this->ocHelper->extractOrderId(func_get_args());
        $this->ocHelper->eventOrderUpdate($order_id);
    }
}
