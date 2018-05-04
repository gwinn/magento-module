<?php

namespace Retailcrm\Retailcrm\Test\Unit\Observer;

class OrderUpdateTest extends \PHPUnit\Framework\TestCase
{
    private $unit;
    private $objectManager;
    private $config;
    private $mockApi;
    private $mockObserver;
    private $mockEvent;
    private $mockOrder;
    private $mockPayment;
    private $registry;

    public function setUp()
    {
        $this->mockApi = $this->getMockBuilder(\Retailcrm\Retailcrm\Helper\Proxy::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'ordersEdit',
                'ordersPaymentsEdit',
                'getVersion',
                'isConfigured'
            ])
            ->getMock();

        $this->mockObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockEvent = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();

        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->mockOrder = $this->getMockBuilder(\Magento\Sales\Order::class)
            ->setMethods([
                'getId',
                'getPayment',
                'getBaseTotalDue',
                'getStatus'
            ])
            ->getMock();

        $this->mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->unit = new \Retailcrm\Retailcrm\Model\Observer\OrderUpdate(
            $this->config,
            $this->registry,
            $this->mockApi
        );
    }

    /**
     * @param int $getBaseTotalDue
     * @param string $apiVersion
     * @param boolean $isConfigured
     * @dataProvider dataProviderOrderUpdate
     */
    public function testExecute(
        $getBaseTotalDue,
        $apiVersion,
        $isConfigured
    ) {
        $testData = $this->getAfterUpdateOrderTestData();

        // mock Payment
        $this->mockPayment->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        // mock Order
        $this->mockOrder->expects($this->any())
            ->method('getId')
            ->willReturn($testData['order.id']);

        $this->mockOrder->expects($this->any())
            ->method('getStatus')
            ->willReturn($testData['order.status']);

        $this->mockOrder->expects($this->any())
            ->method('getBaseTotalDue')
            ->willReturn($getBaseTotalDue);

        $this->mockOrder->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->mockPayment);

        // mock Api
        $this->mockApi->expects($this->any())
            ->method('getVersion')
            ->willReturn($apiVersion);

        $this->mockApi->expects($this->any())
            ->method('isConfigured')
            ->willReturn($isConfigured);

        // mock Event
        $this->mockEvent->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->mockOrder);

        // mock Observer
        $this->mockObserver->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->mockEvent);

        $this->unit->execute($this->mockObserver);

        if ($isConfigured) {
            $this->assertNotEmpty($this->unit->getOrder());
            $this->assertArrayHasKey('externalId', $this->unit->getOrder());
            $this->assertArrayHasKey('status', $this->unit->getOrder());
        } else {
            $this->assertEmpty($this->unit->getOrder());
        }
    }

    /**
     * Get test order data
     * @return array $testOrderData
     */
    private function getAfterUpdateOrderTestData()
    {
        $testOrderData = [
            'order.id' => 1,
            'order.status' => 'processing',
            'order.paymentMethod' => 'checkmo'
        ];

        return $testOrderData;
    }

    public function dataProviderOrderUpdate()
    {
        return [
            [
                'get_base_total_due' => 0,
                'api_version' => 'v4',
                'is_configured' => false
            ],
            [
                'get_base_total_due' => 1,
                'api_version' => 'v4',
                'is_configured' => true
            ],
            [
                'get_base_total_due' => 0,
                'api_version' => 'v5',
                'is_configured' => true
            ],
            [
                'get_base_total_due' => 1,
                'api_version' => 'v5',
                'is_configured' => false
            ]
        ];
    }
}
