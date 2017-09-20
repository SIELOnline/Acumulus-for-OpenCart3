<?php

use Siel\Acumulus\Api;
use Siel\Acumulus\Invoice\Result;
use Siel\Acumulus\Invoice\Source;

/**
 * This extension contains example code to:
 * - Customise the invoice before it is sent to Acumulus.
 * - Process the results of sending the invoice to Acumulus.
 *
 * Usage of this extension:
 * You can use and modify this example extension as you like:
 * - only register the events you are going to use.
 * - add your own event handling n those event handler methods.
 *
 * Or, if you already have an extension with custom code, you can add this code
 * over there:
 * - installEvents() + call to it in install(): only register the events you are
 *   going to use.
 * - uninstallEvents() + call to it in uninstall()
 * Plus the events below, but only those you are going to use:
 * - invoiceCreatedAfter(): add your own event handling
 * - invoiceSendBefore(): add your own event handling
 * - invoiceSendAfter(): add your own event handling
 *
 * Documentation for the events:
 * The events defined by the Acumulus extension:
 * 1) admin/model/extension/module/acumulus/invoiceCreated/after
 * 2) admin/model/extension/module/acumulus/invoiceSend/before
 * 3) admin/model/extension/module/acumulus/invoiceSend/after
 *
 * ad 1)
 * This event is triggered after the raw invoice has been created but before it
 * is "completed". The raw invoice contains all data from the original order or
 * refund needed to create an invoice in the Acumulus format. The raw invoice
 * needs to be completed before it can be sent. Completing includes:
 * - Determining vat rates for those lines that do not yet have one (mostly
 *   discount lines or other special lines like processing or payment costs).
 * - Correcting vat rates if they were based on dividing a vat amount (in cents)
 *   by a price (in cents).
 * - Splitting discount lines over multiple vat rates.
 *
 * So with tis event you can make changes to the raw invoice based on your
 * specific situation. Normally you should prefer the 2nd event, where you can
 * assume that all fields are filled in and have a valid value.
 *
 * However, in some specific cases this event maybe needed, e.g. setting or
 * correcting tax rates before the completor strategies are executed.
 *
 * ad 2)
 * This event is triggered just before the invoice is sent to Acumulus. You can
 * make changes to the invoice or add warnings or errors to the Result object.
 *
 * Typical use cases are:
 * - Template, account number, or cost center selection based on order
 *   specifics, e.g. in a multi-shop environment.
 * - Adding descriptive info to the invoice or invoice lines based on custom
 *   order meta data or data from not supported modules.
 * - Correcting payment info based on specific knowledge of your situation or on
 *   payment modules not supported by this module.
 *
 * ad 3)
 * This event is triggered after the invoice has been sent to Acumulus. The
 * Result object will tell youif there was an exception or if errors or warnings
 * were returned by the Acumulus API. On success, the entry id and token for the
 * newly created invoice inAcumulus are available, so you can e.g. retrieve the
 * pdf of the Acumulus invoice.
 *
 * External Resources:
 * - https://apidoc.sielsystems.nl/content/invoice-add.
 * - https://apidoc.sielsystems.nl/content/warning-error-and-status-response-section-most-api-calls
 *
 * @property \Loader $load
 * @property \ModelExtensionEvent $model_extension_event
 */
class ControllerExtensionModuleCustomiseAcumulusInvoice extends Controller
{
    /** @var \Siel\Acumulus\Helpers\ContainerInterface */
    protected static $container = null;

    /**
     * Constructor.
     *
     * @param \Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        if (static::$container === NULL) {
            // Load the Acumulus autoloader, so we have access to Acumulus
            // helper classes.
            require_once(DIR_SYSTEM . 'library/Siel/psr4.php');
            static::$container = new \Siel\Acumulus\Helpers\Container($this->getShopNamespace(), 'nl');
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
     * Install controller action, called when the module is installed.
     */
    public function install()
    {
        $this->installEvents();
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     */
    public function uninstall()
    {
        $this->uninstallEvents();
    }

    /**
     * Main controller action: show/process the basic settings form for this
     * module.
     */
    public function index()
    {
      $this->load->language('extension/module/customise_acumulus_invoice');
      $this->document->setTitle($this->language->get('heading_title'));
      $data['heading_title'] = $this->language->get('heading_title');
      $data['button_save'] = $this->language->get('button_save');

      // Add an intermediate level to the breadcrumb.
      $data['breadcrumbs'] = array();
      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
      );
      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_extension'),
        'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
      );
      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('extension/module/customise_acumulus_invoice', 'token=' . $this->session->data['token'], true)
      );

      $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');

      $this->response->setOutput($this->load->view('extension/module/customise_acumulus_invoice', $data));
    }

  /**
     * Installs our events.
     *
     * This will add them to the table 'event' from where they are registered on
     * the start of each request. The controller actions can be found below.
     */
    protected function installEvents()
    {
        $this->uninstallEvents();
        $this->model_extension_event->addEvent('customise_acumulus_invoice', 'admin/model/extension/module/acumulus/invoiceCreated/after', 'extension/module/customise_acumulus_invoice/invoiceCreatedAfter');
        $this->model_extension_event->addEvent('customise_acumulus_invoice', 'admin/model/extension/module/acumulus/invoiceSend/before', 'extension/module/customise_acumulus_invoice/invoiceSendBefore');
        $this->model_extension_event->addEvent('customise_acumulus_invoice', 'admin/model/extension/module/acumulus/invoiceSend/after', 'extension/module/customise_acumulus_invoice/invoiceSendAfter');
    }

    /**
     * Removes the Acumulus event handlers from the event table.
     */
    protected function uninstallEvents()
    {
        $this->load->model('extension/event');
        $this->model_extension_event->deleteEvent('customise_acumulus_invoice');
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * @param array $invoice
     *   The invoice in Acumulus format as will be sent to Acumulus.
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     *   The original OpenCart order for which te invoice has been created.
     * @param \Siel\Acumulus\Invoice\Result $localResult
     *   Any local error or warning messages that were created locally.
     */
    public function invoiceCreatedAfter(array &$invoice, Source $invoiceSource, Result $localResult)
    {
        // Here you can make changes to the raw invoice based on your specific
        // situation, e.g. setting or correcting tax rates before the completor
        // strategies execute.
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * @param array $invoice
     *   The invoice in Acumulus format as will be sent to Acumulus.
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     *   The original OpenCart order for which te invoice has been created.
     * @param \Siel\Acumulus\Invoice\Result $localResult
     *   Any local error or warning messages that were created locally.
     */
    public function invoiceSendBefore(array &$invoice, Source $invoiceSource, Result $localResult)
    {
        // Here you can make changes to the invoice based on your specific
        // situation, e.g. setting the payment status to its correct value:
        $invoice['customer']['invoice']['paymentstatus'] = $this->isOrderPaid($invoiceSource) ? Api::PaymentStatus_Paid : Api::PaymentStatus_Due;
    }

    /**
     * Processes the event triggered after an invoice has been sent to Acumulus.
     *
     * You can add warnings and errors to the result and they will be mailed.
     *
     * @param array $invoice
     *   The invoice in Acumulus format as will be sent to Acumulus.
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     *   The original OpenCart order for which te invoice has been created.
     * @param \Siel\Acumulus\Invoice\Result $result
     *   The result as sent back by Acumulus.
     */
    public function invoiceSendAfter(array $invoice, Source $invoiceSource, Result $result)
    {
        if ($result->getException()) {
            // Serious error:
            if ($result->isSent()) {
                // During sending.
            }
            else {
                // Before sending.
            }
        }
        elseif ($result->hasError()) {
            // Invoice was sent to Acumulus but not created due to errors in invoice.
        }
        else {
            // Sent successfully, invoice has been created in Acumulus:
            if ($result->getWarnings()) {
                // With warnings.
            }
            else {
                // Without warnings.
            }
        }
    }

    /**
     * Returns if the order has been paid or not.
     *
     * OpenCart does not store any payment data, so determining the payment
     * status is not really possible for the Acumulus extension. Therefore this
     * is a very valid example of a change you may want to make to the invoice
     * before it is being send.
     *
     * Please fill in your own logic here in this method.
     *
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     *   The original order for which the invoice is being made.
     *
     * @return bool
     *   True if the order has been paid, false otherwise.
     *
     */
    protected function isOrderPaid(Source $invoiceSource)
    {
//        static::$container->getLog()->info('ControllerExtensionModuleCustomiseAcumulusInvoice::isOrderPaid(): invoiceSource = ' . var_export($invoiceSource->getSource(), true));
        return true;
    }
}
