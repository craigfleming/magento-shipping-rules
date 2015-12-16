<?php
class Meanbee_Shippingrules_Model_Rule_Condition_PostalCode extends Mage_Rule_Model_Condition_Combine
{
    /**
     * @override
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('meanship/rule_condition_postalCode');
    }

    /**
     * Provides list of possible conditions for select field.
     *
     * @override
     *
     * @return array Plaintext array of condition labels with associated attribute codes.
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $types = array('str', 'b26', 'b10', 'b36');
        foreach ($types as $type) {
            $conditions[] = array(
                'label' => 'Entire Postal Code',
                'value' => "meanship/rule_condition|dest_postal_code_p0_{$type}"
            );
        }
        foreach ($types as $type) {
            $conditions[] = array(
                'label' => '1st Part',
                'value' => "meanship/rule_condition|dest_postal_code_p1_{$type}"
            );
        }
        foreach ($types as $type) {
            $conditions[] = array(
                'label' => '2nd Part',
                'value' => "meanship/rule_condition|dest_postal_code_p2_{$type}"
            );
        }
        foreach ($types as $type) {
            $conditions[] = array(
                'label' => '3rd Part',
                'value' => "meanship/rule_condition|dest_postal_code_p3_{$type}"
            );
        }
        foreach ($types as $type) {
            $conditions[] = array(
                'label' => '4th Part',
                'value' => "meanship/rule_condition|dest_postal_code_p4_{$type}"
            );
        }
        return $conditions;
    }

    /**
     * Provides values for value select field. Overriden here to give list of postal code formats.
     *
     * @override
     * @chainable
     */
    public function loadValueOptions()
    {
        $valueOptions = array();
        foreach (Mage::helper('meanship/postcode')->getPostalCodeData() as $postalCodeData) {
            $valueOptions[$postalCodeData['code']] = Mage::helper('meanship/country')->toRegionalIndicatorSymbols($postalCodeData['code']) . ' '
                                                    . $postalCodeData['name']
                                                    . (isset($postalCodeData['prefix']) ? '  ['.$postalCodeData['prefix'].']' : '');
        }
        $this->setValueOptions($valueOptions);
        return $this;
    }

    /**
     * Gets user-friendly value name.
     *
     * @override
     *
     * @return string Label for value.
     */
    public function getValueName()
    {
        $value = $this->getValue();
        if (is_null($value) || '' === $value) {
            return '...';
        }
        $options = $this->getValueOptions();
        if (!empty($options)) {
            foreach ($options as $v => $label) {
                if ($v == $value) {
                    return $label;
                }
            }
        }
    }

    /**
     * Creates field for value. Overriden here for postal code formats.
     *
     * @override
     */
    public function getValueElement()
    {
        $this->loadValueOptions();
        return $this->getForm()->addField("{$this->getPrefix()}__{$this->getId()}__value", 'select', array(
            'name'    => "rule[{$this->getPrefix()}][{$this->getId()}][value]",
            'value'   => $this->getValue(),
            'values'  => array_merge(array('Please choose a country...'), $this->getValueOptions()),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply'     => $this->getExplicitApply()
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * Validates conatained conditions against shipping rate request, guarded by check on country code.
     *
     * @override
     *
     * @param  Varien_Object $request (Mage_Shipping_Model_Rate_Request)
     * @return boolean                Result of short-circuit evaluating of guard and contained conditions.
     */
    public function validate(Varien_Object $request)
    {
        if (strpos($this->getValue(), $request->getDestCountryId()) !== 0) return false;

        if (!$this->getConditions()) {
            return true;
        }
        $all    = $this->getAggregator() === 'all';
        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($request);
            if ($all && !$validated) {
                return false;
            } elseif (!$all && $validated) {
                return true;
            }
        }
        return $all ? true : false;
    }

    /**
     * Forms hypertext that describes condition.
     *
     * @override
     *
     * @return string Hypertext descriptor.
     */
    public function asHtml()
    {
        return $this->getTypeElement()->getHtml().Mage::helper('meanship')->__('Postal Code of %s matches %s of these conditions:', $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml()).$this->getRemoveLinkHtml();
    }

    /**
     * Forms plaintext that describes condition.
     *
     * @override
     *
     * @return string Plaintext descriptor.
     */
    public function asString()
    {
        return Mage::helper('meanship')->__('Postal Code of %s matches %s of these conditions:', $this->getValueName(), strtoupper($this->getAggregator()));
    }
}
