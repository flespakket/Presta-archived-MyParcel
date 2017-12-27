<?php
/**
 * Backend orders controller
 * 
 * @copyright Copyright (c) 2014 Total Internet Group (http://www.totalinternetgroup.nl/)
 */

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

        $flespakketFlag = Configuration::get('FLESPAKKET_ACTIVE');

        $this->context->smarty->assign(
            array(
                'flespakket'        => $flespakketFlag,
                'prestaShopVersion' => substr(_PS_VERSION_, 0, 3),
            )
        );

        if (true == $flespakketFlag) {
            if ('' == session_id()) {
                session_start();
            }

            $_SESSION['FLESPAKKET_VISIBLE_CONSIGNMENTS'] = '';
        }
    }
}