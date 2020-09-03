<?php
/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection DuplicatedCode
 */
use Siel\Acumulus\Helpers\Container;

/*
 * This is the Acumulus catalog side controller.
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
                $container = new Container($this->getShopNamespace());
                static::$staticOcHelper = $container->getInstance('OcHelper', 'Helpers', array($this->registry, $container));
            }
            $this->ocHelper = static::$staticOcHelper;
        }
    }

    /**
     * Returns the Shop namespace to use for this OC version.
     *
     * @return string
     *   The Shop namespace to use for this OC version.
     */
    protected function getShopNamespace()
    {
        return sprintf('OpenCart\OpenCart%1$u\OpenCart%1$u%2$u',
            substr(VERSION, 0, 1),
            substr(VERSION, 2, 1));
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
        $this->ocHelper->eventOrderUpdate((int) $order_id);
    }
}
