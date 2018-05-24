<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 22.08.17
 * Time: 15:35
 */

namespace Tealium\Tags\Block;

class Template extends \Magento\Framework\View\Element\Template{

    protected $_objectManager;

    protected $_tealiumType;
    protected $_tealiumName;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getStore(){
        return $this->_storeManager->getStore();
    }

    public function getObjectManager(){
        return $this->_objectManager;
    }

    public function setType($type){
        $this->_tealiumType = $type;
    }

    public function getTealiumType(){
        return $this->_tealiumType;
    }

    public function setName($name){
        $this->_tealiumName = $name;
    }

    public function getTealiumName(){
        return $this->_tealiumName;
    }

    public function getRegestry(){
        return $this->_registry;
    }
}