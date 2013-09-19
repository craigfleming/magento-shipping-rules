<?php
class Meanbee_Shippingrules_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {
    protected $_code = 'meanship';

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!$this->isActive()) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        foreach ($this->_getApplicableRules($request) as $method_name =>  $rule_data) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($this->_code);
            $method->setCarrierTitle('Shipping');

            // record method information
            $method->setMethod($rule_data->getId());
            $method->setMethodTitle($method_name);

            // rate cost is optional property to record how much it costs to vendor to ship
            $method->setCost($rule_data->getCost());
            $method->setPrice($rule_data->getPrice());

            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods() {
        $methods = array();

        /** @var $rule_collection Meanbee_Shippingrules_Model_Resource_Rule_Collection */
        $rule_collection = Mage::getModel('meanship/rule')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_ASC);

        foreach ($rule_collection as $rule) {
            $methods[$rule->getId()] = $rule->getName();
        }

        return $methods;
    }

    protected function _getApplicableRules(Mage_Shipping_Model_Rate_Request $request) {
        $methods = array();

        /** @var $rule_collection Meanbee_Shippingrules_Model_Resource_Rule_Collection */
        $rule_collection = Mage::getModel('meanship/rule')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_ASC);

        /**
         * The customer doesn't come to us through $request, so we need to check for it manually.  This following will
         * work on the frontend checkout.
         */
        if(Mage::getSingleton('adminhtml/session_quote')->getCustomer()->hasData()) {
            $customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
            $request->setCustomer($customer);
            $request->setCustomerGroupId($customer->getGroupId());
        } elseif (Mage::helper('customer')->getCustomer()->hasData()) {
            $customer = Mage::helper('customer')->getCustomer();
            $request->setCustomer($customer);
            $request->setCustomerGroupId($customer->getGroupId());
        } else {
            $request->setCustomer(null);
            $request->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $stop_flag = array();

        foreach ($rule_collection as $rule) {
            if (!$rule->validate($request)) {
                continue;
            }

            $rule_name = $rule->getName();

            if (array_key_exists($rule_name, $methods)) {
                if (!isset($stop_flag[$rule_name])) {
                    $stop_flag[$rule_name] = false;
                }

                /**
                 * We'll skip this rule if we've already matched at a cheaper price, or we've hit a stop flag.
                 */
                if ($methods[$rule_name]->getPrice() < $rule->getPrice() || $stop_flag[$rule_name]) {
                    continue;
                }
            }

            $methods[$rule_name] = new Varien_Object(array(
                'price' => $rule->getPrice(),
                'cost'  => $rule->getCost(),
                'id'    => $rule->getId()
            ));

            if ($rule->getStopRulesProcessing()) {
                $stop_flag[$rule_name] = true;
            }
        }

        return $methods;
    }
}