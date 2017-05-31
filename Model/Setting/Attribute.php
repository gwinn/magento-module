<?php

namespace Retailcrm\Retailcrm\Model\Setting;

//use Psr\Log\LoggerInterface;

class Attribute implements \Magento\Framework\Option\ArrayInterface
{
	protected $_entityType;
	protected $_store;
	
	public function __construct(
			\Magento\Store\Model\Store $store,
			\Magento\Eav\Model\Entity\Type $entityType
			) {
				$this->_store = $store;
				$this->_entityType = $entityType;
	}
	
	public function toOptionArray()
	{
		$types = array('text', 'multiselect', 'decimal');
		$attributes = $this->_entityType->loadByCode('catalog_product')->getAttributeCollection();
		$attributes->addFieldToFilter('frontend_input', $types);
	
		$result = array();
		foreach ($attributes as $attr) {
			if ($attr->getFrontendLabel()) {
				$result[] = array('value' => $attr->getAttributeId(), 'label' => $attr->getFrontendLabel(), 'title' => $attr->getAttributeCode());
			}
		}
	
		return $result;
	}
	
	
}
