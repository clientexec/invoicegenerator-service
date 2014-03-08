<?php
require_once 'modules/admin/models/ServicePlugin.php';
require_once 'modules/billing/models/BillingType.php';
require_once 'modules/billing/models/Invoice_EventLog.php';
require_once 'modules/billing/models/BillingGateway.php';

/**
* @package Plugins
*/
class PluginInvoicegenerator extends ServicePlugin
{
    protected $featureSet = 'billing';
    public $hasPendingItems = true;

    function getVariables()
    {
        $variables = array(
            /*T*/'Plugin Name'/*/T*/   => array(
                'type'          => 'hidden',
                'description'   => /*T*/''/*/T*/,
                'value'         => /*T*/'Invoice Generator'/*/T*/,
            ),
            /*T*/'Enabled'/*/T*/       => array(
                'type'          => 'yesno',
                'description'   => /*T*/'When enabled, invoices will automatically be sent to your customers.'/*/T*/,
                'value'         => '0',
            ),
            /*T*/'Run schedule - Minute'/*/T*/  => array(
                'type'          => 'text',
                'description'   => /*T*/'Enter number, range, list or steps'/*/T*/,
                'value'         => '30',
                'helpid'        => '8',
            ),
            /*T*/'Run schedule - Hour'/*/T*/  => array(
                'type'          => 'text',
                'description'   => /*T*/'Enter number, range, list or steps'/*/T*/,
                'value'         => '*',
            ),
            /*T*/'Run schedule - Day'/*/T*/  => array(
                'type'          => 'text',
                'description'   => /*T*/'Enter number, range, list or steps'/*/T*/,
                'value'         => '*',
            ),
            /*T*/'Run schedule - Month'/*/T*/  => array(
                'type'          => 'text',
                'description'   => /*T*/'Enter number, range, list or steps'/*/T*/,
                'value'         => '*',
            ),
            /*T*/'Run schedule - Day of the week'/*/T*/  => array(
                'type'          => 'text',
                'description'   => /*T*/'Enter number in range 0-6 (0 is Sunday) or a 3 letter shortcut (e.g. sun)'/*/T*/,
                'value'         => '*',
            ),
        );

        return $variables;
    }

    function execute()
    {
        $messages = array();
        $numCustomers = 0;

        $billingGateway = new BillingGateway($this->user);
        $initial = 0;
        $billingGateway->generate_invoice($initial);
        if (isset($this->session->all_invoices)){
              $numCustomers = count($this->session->all_invoices);
        }
        $billingGateway->send_process_invoice_summary("generate");
        $billingGateway->reportInvalidRecurringFees();

        $this->settings->updateValue("LastDateGenerateInvoices", time());

        array_unshift($messages, "$numCustomers customer(s) were invoiced");
        return $messages;
    }

    function pendingItems()
    {
        $returnArray = array();
        $returnArray['data'] = array();
        $returnArray['totalcount'] = count($returnArray['data']);
        $returnArray['headers'] = array (
            $this->user->lang('Customer'),
            $this->user->lang('Due Date')
        );

        return $returnArray;
    }

    function output() { }

    function dashboard()
    {
        $row['customers'] = 0;
        return $this->user->lang('Number of customers to be billed: %d', $row['customers']);
    }
}
?>
