<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\AuthenticationPopup;
use Magento\Customer\Model\Form;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AuthenticationPopupTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthenticationPopup */
    private $model;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilderMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->model = new AuthenticationPopup(
            $this->contextMock
        );
    }

    /**
     * @param mixed $isAutocomplete
     * @param string $baseUrl
     * @param string $registerUrl
     * @param string $forgotUrl
     * @param array $result
     *
     * @dataProvider dataProviderGetConfig
     */
    public function testGetConfig($isAutocomplete, $baseUrl, $registerUrl, $forgotUrl, array $result)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Form::XML_PATH_ENABLE_AUTOCOMPLETE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($isAutocomplete);

        /** @var StoreInterface||\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['customer/account/create', [], $registerUrl],
                    ['customer/account/forgotpassword', [], $forgotUrl],
                ]
            );

        $this->assertEquals($result, $this->model->getConfig());
    }

    public function dataProviderGetConfig()
    {
        return [
            [
                0,
                'base',
                'reg',
                'forgot',
                [
                    'autocomplete' => 'off',
                    'customerRegisterUrl' => 'reg',
                    'customerForgotPasswordUrl' => 'forgot',
                    'baseUrl' => 'base',
                ],
            ],
            [
                1,
                '',
                'reg',
                'forgot',
                [
                    'autocomplete' => 'on',
                    'customerRegisterUrl' => 'reg',
                    'customerForgotPasswordUrl' => 'forgot',
                    'baseUrl' => '',
                ],
            ],
            [
                '',
                'base',
                '',
                'forgot',
                [
                    'autocomplete' => 'off',
                    'customerRegisterUrl' => '',
                    'customerForgotPasswordUrl' => 'forgot',
                    'baseUrl' => 'base',
                ],
            ],
            [
                true,
                'base',
                'reg',
                '',
                [
                    'autocomplete' => 'on',
                    'customerRegisterUrl' => 'reg',
                    'customerForgotPasswordUrl' => '',
                    'baseUrl' => 'base',
                ],
            ],
        ];
    }
}
