<?php

namespace Retailcrm\Retailcrm\Test\Unit\Block\Frontend;

use Retailcrm\Retailcrm\Test\TestCase;

class DaemonCollectorTest extends TestCase
{
    private $unit;
    private $customer;

    const SITE_KEY = 'RC-XXXXXXX-X';

    public function setUp(): void
    {
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeResolver = $this->createMock(\Magento\Store\Model\StoreResolver::class);
        $helper = $this->createMock(\Retailcrm\Retailcrm\Helper\Data::class);
        $this->customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $store = $this->createMock(\Magento\Store\Model\Store::class);

        $customerSession->expects($this->any())
            ->method('getCustomer')
            ->willReturn($this->customer);
        $storeResolver->expects($this->any())
            ->method('getCurrentStoreId')
            ->willReturn(1);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $helper->expects($this->any())
            ->method('getSiteKey')
            ->willReturn(self::SITE_KEY);

        $context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($storeManager);

        $this->unit = new \Retailcrm\Retailcrm\Block\Frontend\DaemonCollector(
            $context,
            $customerSession,
            $storeResolver,
            $helper
        );
    }

    public function testGetJSWithCustomer()
    {
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $js = $this->unit->buildScript()->getJs();

        $this->assertStringContainsString('<script type="text/javascript">', $js);
        $this->assertStringContainsString('</script>', $js);
        $this->assertStringContainsString('_rc(\'send\', \'pageView\');', $js);
        $this->assertStringContainsString(self::SITE_KEY, $js);
        $this->assertStringContainsString('customerId', $js);
    }

    public function testGetJSWithoutCustomer()
    {
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $js = $this->unit->buildScript()->getJs();

        $this->assertStringContainsString('<script type="text/javascript">', $js);
        $this->assertStringContainsString('</script>', $js);
        $this->assertStringContainsString('_rc(\'send\', \'pageView\');', $js);
        $this->assertStringContainsString(self::SITE_KEY, $js);
        $this->assertStringNotContainsString('customerId', $js);
    }
}
