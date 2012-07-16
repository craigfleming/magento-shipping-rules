<?php
class Meanbee_Shippingrules_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'meanship';
        $this->_controller = 'adminhtml_rules';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
            'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('meanship')->__('Save Shipping Rule'));
    }

    public function getHeaderText() {
        if (Mage::registry('meanship_data') && Mage::registry('meanship_data')->getId()) {
            return Mage::helper('meanship')->__('Edit Shipping Rule "%s"', $this->escapeHtml(Mage::registry('meanship_data')->getName()));
        } else {
            return Mage::helper('meanship')->__('New Shipping Rule');
        }
    }

    public function getHeaderCssClass() {
        return 'head-shipping-method ' . parent::getHeaderCssClass();
    }
}