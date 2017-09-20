<?php

/** @noinspection PhpUndefinedClassInspection */
/**
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
        parent::__construct($registry);
        if ($this->ocHelper === NULL) {
            if (static::$staticOcHelper === NULL) {
                // Load autoloader, container and then our helper that contains
                // OC1 and OC2 shared code.
                require_once(DIR_SYSTEM . 'library/Siel/psr4.php');
                $container = new \Siel\Acumulus\Helpers\Container($this->getShopNamespace());
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
        $result = sprintf('OpenCart\OpenCart%1$u\OpenCart%1$u%2$u', substr(VERSION, 0, 1), substr(VERSION, 2, 1));
        return $result;
    }

    /**
     * Event handler that executes on the creation or update of an order.
     *
     * Parameters for OC2.0:
     * param int $order_id
     *
     * Parameters for OC 2.2:
     * param string $route
     * param mixed $output
     * param int $order_id
     * param int $order_status_id

     * Parameters for OC 2.3+
     * param string $route
     *   checkout/order/addOrder or checkout/order/addOrderHistory.
     * param array $args
     *   Array with numeric indices containing the arguments as passed to the
     *   model method.
     *   When route = checkout/order/addOrder it contains: order (but without
     *   order_id as that will be created and assigned by the method).
     *   When route = checkout/order/addOrderHistory it contains: order_id,
     *   order_status_id, comment, notify, override.
     * param mixed $output
     *   If passed by event checkout/order/addOrder it contains the order_id of
     *   the just created order. It is null for checkout/order/addOrderHistory.
     */
    public function eventOrderUpdate()
    {
        if (is_numeric(func_get_arg(0))) {
            // OC 2.0.
            $order_id = func_get_arg(0);
        } elseif (is_array(func_get_arg(1))) {
            // OC 2.3.
            $route = func_get_arg(0);
            $args = func_get_arg(1);
            $output = func_get_arg(2);
            $order_id = $route === 'checkout/order/addOrderHistory' ? $args[0] : $output;
        } else {
            // OC 2.2.
            $order_id = func_get_arg(2);
        }
        $this->ocHelper->eventOrderUpdate((int) $order_id);
    }
}
