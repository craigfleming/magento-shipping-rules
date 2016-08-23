<?php
class Meanbee_Shippingrules_Calculator_Term_Multiple
    extends Meanbee_Shippingrules_Calculator_Term_Variable
{
    /**
     * {@inheritdoc}
     * @override
     * @param  Mage_Shipping_Model_Rate_Request $request
     * @return int|float
     */
    public function evaluate($request)
    {
        return parent::evaluate($request) * $this->getValue();
    }
}