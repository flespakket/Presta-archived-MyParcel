<?php
/**
 * Flespakket bootstrap file
 *
 * @copyright Copyright (c) 2014 Total Internet Group (http://www.totalinternetgroup.nl/)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Flespakket extends Module
{
    /**
     * Inits the main settings of the module
     *
     * @return Flespakket
     */
    public function __construct()
    {
        $this->name = 'flespakket';
        $this->tab = 'shipping_logistics';

        if ('1.5' == substr(_PS_VERSION_, 0, 3)) {
            $this->version = 'v1.1.1';
        } elseif ('1.6' == substr(_PS_VERSION_, 0, 3)) {
            $this->version = '1.1.1';
        }

        $this->author = 'Total Internet Group';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.6.9.9');
        // NOTE: Prestashop does not validate max version == version due to their invalid implementation of version_compare() >= 0 (should be > 0)

        parent::__construct();

        $this->displayName = $this->l('Flespakket');
        $this->description = $this->l('Assistance with the parcel service through Flespakket.nl');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Flespakket module?');
    }

    /**
     * Installs the module
     *
     * @return boolean
     */
    public function install()
    {
        if (false === parent::install()) {
            return false;
        }

        Db::getInstance()->Execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "flespakket` (
                                      `flespakket_id` int(11) NOT NULL AUTO_INCREMENT,
                                      `order_id` int(11) NOT NULL,
                                      `consignment_id` bigint(20) NOT NULL,
                                      `retour` tinyint(1) NOT NULL DEFAULT '0',
                                      `tracktrace` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                                      `postcode` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
                                      `tnt_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                      `tnt_updated_on` datetime NOT NULL,
                                      `tnt_final` tinyint(1) NOT NULL DEFAULT '0',
                                      PRIMARY KEY (`flespakket_id`)
                                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

        Configuration::updateValue('FLESPAKKET_ACTIVE', 'true');

        $this->registerHook('displayBackOfficeHeader');

        return true;
    }

    /**
     * Uninstalls the module
     *
     * @return boolean
     */
    public function uninstall()
    {
        Db::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "flespakket`");

        Configuration::deleteByName('FLESPAKKET_ACTIVE');

        if (false === parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Adds JavaScript files
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'js/flespakket.js', 'all');
        $this->context->controller->addCSS($this->_path . 'css/flespakket.css', 'all');
    }

    /**
     * Gets Flespakket order data
     *
     * @param integer $orderId
     * @return array
     */
    static public function getOrderData($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'flespakket` WHERE `order_id` = ' . $orderId;

        $result = Db::getInstance()->ExecuteS($sql, true, false);

        $items = '';
        $checks = '';

        foreach ($result as &$row)
        {
            $_SESSION['FLESPAKKET_VISIBLE_CONSIGNMENTS'] .= $row['consignment_id'] . '|';

            $row['flpa_tracktrace_link'] = 'https://mijnpakket.postnl.nl/Inbox/Search?' . http_build_query(array(
                'lang' => 'nl',
                'B'    => $row['tracktrace'],
                'P'    => $row['postcode'],
            ));
            $row['flpa_tnt_status'] = empty($row['tnt_status']) ? 'Track&Trace' : $row['tnt_status'];
            $row['flpa_pdf_image'] = ($row['retour'] == 1) ? 'flespakket_retour.png' : 'flespakket_pdf.png';

            // get the order, then address, then country to reach the country ISO code
            $order = new Order(intval($row['order_id']));
            $address = new Address($order->id_address_delivery);
            $country = new Country();
            if($countryId = Country::getIdByName(null, $address->country))
            {
                $country = new Country($countryId);
            }
            if(!empty($country->iso_code) && $country->iso_code != 'NL')
            {
                $row['flpa_tracktrace_link'] = 'https://www.internationalparceltracking.com/Main.aspx#/track/' . implode('/', array(
                    $row['tracktrace'],
                    $country->iso_code,
                    $address->postcode,
                ));
            }

            $items .= '<a href="' . $row['flpa_tracktrace_link'] . '" target="_blank">' . $row['flpa_tnt_status'] . '</a>'
                    . '<a href="#" onclick="return printConsignments(\'' . $row['consignment_id'] . '\');" class="flespakket-pdf">'
                    . '<img border="0" alt="Print" src="/modules/flespakket/images/' . $row['flpa_pdf_image'] . '">'
                    . '</a>'
                    . '<br/>';

            $checks .= '|' . $row['consignment_id'];
        }

        if (!empty($checks)) {
            $checks = substr($checks, 1);
        }

        $flespakketData = array(
            'checks' => $checks,
            'items'  => $items,
        );

        return $flespakketData;
    }
}