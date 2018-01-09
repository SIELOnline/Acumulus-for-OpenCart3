<?php

use Siel\Acumulus\Api;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Meta;

/**
 * This extension contains example code to:
 * - Customise the invoice before it is sent to Acumulus.
 * - Process the results of sending the invoice to Acumulus.
 *
 * Usage of this extension:
 * You can use and modify this example extension as you like:
 * - only register the events you are going to use.
 * - add your own event handling in those event handler methods.
 *
 * NOTE that upon install this file will be hard linked to the catalog
 * controller section of this webshop. This is done so because the events can
 * be triggered from both the catalog and admin side of the shop. In doing so,
 * you only have to program your customisations once.
 *
 * Documentation for the events:
 * The events defined by the Acumulus extension:
 * 1) admin/model/extension/module/acumulus/invoiceCreated/after
 *    and  catalog/model/extension/module/acumulus/invoiceCreated/after
 * 2) admin/model/extension/module/acumulus/invoiceSend/before
 *    and catalog/model/extension/module/acumulus/invoiceSend/before
 * 3) admin/model/extension/module/acumulus/invoiceSend/after
 *    and catalog/model/extension/module/acumulus/invoiceSend/after
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
 * - Making prices ex vat more precise to prevent invoice amount differences.
 * - Converting non Euro currencies (future feature).
 * - Flattening composed products or products with options.
 *
 * So with this event you can make changes to the raw invoice based on your
 * specific situation. By setting the invoice to null, you can prevent having
 * the invoice been sent to Acumulus. Normally you should prefer the 2nd event,
 * where you can assume that the invoice has been flattened and all fields are
 * filled in and have a valid value.
 *
 * However, in some specific cases this event may be needed, e.g. setting or
 * correcting tax rates before the completor strategies are executed.
 *
 * ad 2)
 * This event is triggered just before the invoice is sent to Acumulus. You can
 * make changes to the invoice or add warnings or errors to the Result object.
 * By setting the invoice to null, you can prevent having the invoice been sent
 * to Acumulus.
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
 * Result object will tell you if there was an exception or if errors or
 * warnings were returned by the Acumulus API. On success, the entry id and
 * token for the newly created invoice in Acumulus are available, so you can
 * e.g. retrieve the pdf of the Acumulus invoice.
 *
 * External Resources:
 * - https://apidoc.sielsystems.nl/content/invoice-add.
 * - https://apidoc.sielsystems.nl/content/warning-error-and-status-response-section-most-api-calls
 *
 * @property \ModelSettingEvent $model_setting_event
 */
class ControllerExtensionModuleAcumulusCustomiseInvoice extends Controller
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
            require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
            SielAcumulusAutoloader::register();
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
     *
     * @throws \Exception
     */
    public function install()
    {
        if ($this->isAdmin())
        {
            $this->createLink();
            $this->installEvents();
        }
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     *
     * @throws \Exception
     */
    public function uninstall()
    {
        if ($this->isAdmin())
        {
            $this->uninstallEvents();
            $this->removeLink();
        }
    }

    /**
     * Main controller action: show/process the basic settings form for this
     * module.
     */
    public function index()
    {
        if ($this->isAdmin())
        {
            $this->load->language('extension/module/acumulus_customise_invoice');
            $this->document->setTitle($this->language->get('heading_title'));
            $data['heading_title'] = $this->language->get('heading_title');
            $data['button_save'] = $this->language->get('button_save');
            $data['text_edit'] = $this->language->get('text_edit');

            // Add an intermediate level to the breadcrumb.
            $data['breadcrumbs']   = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
            ];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/acumulus_customise_invoice', 'user_token=' . $this->session->data['user_token'], true),
            ];

            $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('extension/module/acumulus_customise_invoice', $data));
        }
    }

    /**
     * Creates a hard link to this file in the catalog controller part.
     *
     * Because order status change events may be triggered in both the catalog
     * and amin part of the application, this code should be executable in both
     * parts as well. To ease the maintenance task, we create a hard link from
     * the catalog part to this file
     */
    protected function createLink()
    {
        $target = DIR_APPLICATION . '../admin/controller/extension/module/acumulus_customise_invoice.php';
        $link = DIR_APPLICATION . '../catalog/controller/extension/module/acumulus_customise_invoice.php';
        if (file_exists($link)) {
            unlink($link);
        }
        link($target, $link);
    }

    /**
     * Removes the hard link that was created during install.
     */
    protected function removeLink()
    {
        $link = DIR_APPLICATION . '../catalog/controller/extension/module/acumulus_customise_invoice.php';
        if (file_exists($link)) {
            unlink($link);
        }
    }

    /**
     * Installs our events.
     *
     * This will add them to the table 'event' from where they are registered on
     * the start of each request. The controller actions can be found below.
     *
     * @throws \Exception
     */
    protected function installEvents()
    {
        $this->uninstallEvents();
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'admin/model/extension/module/acumulus/invoiceCreated/after', 'extension/module/acumulus_customise_invoice/invoiceCreatedAfter');
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'catalog/model/extension/module/acumulus/invoiceCreated/after', 'extension/module/acumulus_customise_invoice/invoiceCreatedAfter');
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'admin/model/extension/module/acumulus/invoiceSend/before', 'extension/module/acumulus_customise_invoice/invoiceSendBefore');
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'catalog/model/extension/module/acumulus/invoiceSend/before', 'extension/module/acumulus_customise_invoice/invoiceSendBefore');
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'admin/model/extension/module/acumulus/invoiceSend/after', 'extension/module/acumulus_customise_invoice/invoiceSendAfter');
        $this->model_setting_event->addEvent('acumulus_customise_invoice', 'catalog/model/extension/module/acumulus/invoiceSend/after', 'extension/module/acumulus_customise_invoice/invoiceSendAfter');
    }

    /**
     * Removes the Acumulus event handlers from the event table.
     *
     * @throws \Exception
     */
    protected function uninstallEvents()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('acumulus_customise_invoice');
    }

    /**
     * Processes the event triggered after the raw invoice has been created.
     *
     * @param string $route
     *   The route that triggered this event.
     * @param array $args
     *   The arguments for the events.
     */
    public function invoiceCreatedAfter(/** @noinspection PhpUnusedParameterInspection */ $route, array $args)
    {
        /** @var array $invoice */
        $invoice = &$args['invoice'];
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $args['source'];
        /** @var \Siel\Acumulus\Invoice\Result $localResult */
        $localResult = $args['localResult'];

        // Here you can make changes to the raw invoice based on your specific
        // situation, e.g. setting or correcting tax rates before the completor
        // strategies execute.

	    // NOTE: the example below is now an option in the advanced settings:
	    // Prevent sending 0-amount invoices (free products).
	    if (empty($invoice) || $invoice['customer']['invoice'][Meta::InvoiceAmountInc] == 0) {
		    $invoice = null;
	    } else {
		    // Change invoice here.
	    }
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * @param string $route
     *   The route that triggered this event.
     * @param array $args
     *   The arguments for the events.
     */
    public function invoiceSendBefore(/** @noinspection PhpUnusedParameterInspection */ $route, array $args)
    {
        /** @var array $invoice */
        $invoice = &$args['invoice'];
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $args['source'];
        /** @var \Siel\Acumulus\Invoice\Result $localResult */
        $localResult = $args['localResult'];

        // Here you can make changes to the invoice based on your specific
        // situation, e.g. setting the payment status to its correct value:
        //$invoice['customer']['invoice']['paymentstatus'] = $this->isOrderPaid($invoiceSource) ? Api::PaymentStatus_Paid : Api::PaymentStatus_Due;
    }

    /**
     * Processes the event triggered after an invoice has been sent to Acumulus.
     *
     * You can add warnings and errors to the result and they will be mailed.
     *
     * @param string $route
     *   The route that triggered this event.
     * @param array $args
     *   The arguments for the events.
     */
    public function invoiceSendAfter(/** @noinspection PhpUnusedParameterInspection */ $route, array $args)
    {
        /** @var array $invoice */
        $invoice = &$args['invoice'];
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $args['source'];
        /** @var \Siel\Acumulus\Invoice\Result $result */
        $result = $args['result'];

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
            // Invoice was sent to Acumulus but not created due to errors in the
            // invoice.
        }
        else {
            // Sent successfully, invoice has been created in Acumulus:
            if ($result->getWarnings()) {
                // With warnings.
            }
            else {
                // Without warnings.
            }

            $acumulusInvoice = $result->getResponse();
            // Check if an entry id was created.
            $acumulusInvoice = $result->getResponse();
            if (!empty($acumulusInvoice['entryid'])) {
                $token = $acumulusInvoice['token'];
                $entryId = $acumulusInvoice['entryid'];
            }
            else {
                // If the invoice was sent as a concept, no entryid will be returned.
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
     *   Wrapper around the original order for which the invoice has been
     *   created.
     *
     * @return bool
     *   True if the order has been paid, false otherwise.
     *
     */
    protected function isOrderPaid(Source $invoiceSource)
    {
        //static::$container->getLog()->info('ControllerExtensionModuleAcumulusCustomiseInvoice::isOrderPaid(): invoiceSource = ' . var_export($invoiceSource->getSource(), true));
        return true;
    }

    /**
     *
     *
     *
     * @return bool
     *
     */
    protected function isAdmin() {
        return strrpos(DIR_APPLICATION, '/admin/') === strlen(DIR_APPLICATION) - strlen('/admin/');
    }
}
