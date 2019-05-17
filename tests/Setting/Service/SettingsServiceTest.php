<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\Setting\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigDefinition;
use Swag\PayPal\PayPal\Api\Payment\ApplicationContext;
use Swag\PayPal\PayPal\PaymentIntent;
use Swag\PayPal\Setting\Exception\PayPalSettingsNotFoundException;
use Swag\PayPal\Setting\Service\SettingsService;
use Swag\PayPal\Test\Mock\DIContainerMock;
use Swag\PayPal\Test\Mock\Repositories\DefinitionInstanceRegistryMock;
use Swag\PayPal\Test\Mock\Setting\Service\SystemConfigServiceMock;

class SettingsServiceTest extends TestCase
{
    use KernelTestBehaviour;

    public function testEmptyGetSettings(): void
    {
        $settingsProvider = new SettingsService($this->createSystemConfigServiceMock());
        $this->expectException(PayPalSettingsNotFoundException::class);
        $settingsProvider->getSettings();
    }

    public function getProvider(): array
    {
        $prefix = 'SwagPayPal.settings.';

        return [
            [$prefix . 'clientId', 'getClientId', 'testClientId'],
            [$prefix . 'clientSecret', 'getClientSecret', 'getTestClientId'],
            [$prefix . 'sandbox', 'getSandbox', true],
            [$prefix . 'intent', 'getIntent', PaymentIntent::SALE],
            [$prefix . 'submitCart', 'getSubmitCart', false],
            [$prefix . 'webhookId', 'getWebhookId', 'testWebhookId'],
            [$prefix . 'webhookExecuteToken', 'getwebhookExecuteToken', 'testWebhookToken'],
            [$prefix . 'brandName', 'getBrandName', 'Awesome brand'],
            [$prefix . 'landingPage', 'getLandingPage', ApplicationContext::LANDINGPAGE_TYPE_LOGIN],
            [$prefix . 'sendOrderNumber', 'getSendOrderNumber', false],
            [$prefix . 'orderNumberPrefix', 'getOrderNumberPrefix', 'TEST_'],
        ];
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet(string $key, string $getterName, $value): void
    {
        $settingsService = new SettingsService($this->createSystemConfigServiceMock([$key => $value]));
        $settings = $settingsService->getSettings();

        static::assertTrue(
            method_exists($settings, $getterName),
            'getter ' . $getterName . ' does not exist'
        );
        static::assertSame($value, $settings->$getterName());
    }

    public function updateProvider(): array
    {
        return [
            ['clientId', 'getClientId', 'testClientId'],
            ['clientSecret', 'getClientSecret', 'getTestClientId'],
            ['sandbox', 'getSandbox', true],
            ['intent', 'getIntent', PaymentIntent::SALE],
            ['submitCart', 'getSubmitCart', false],
            ['webhookId', 'getWebhookId', 'testWebhookId'],
            ['webhookExecuteToken', 'getwebhookExecuteToken', 'testWebhookToken'],
            ['brandName', 'getBrandName', 'Awesome brand'],
            ['landingPage', 'getLandingPage', ApplicationContext::LANDINGPAGE_TYPE_LOGIN],
            ['sendOrderNumber', 'getSendOrderNumber', false],
            ['orderNumberPrefix', 'getOrderNumberPrefix', 'TEST_'],
        ];
    }

    /**
     * @dataProvider updateProvider
     */
    public function testUpdate(string $key, string $getterName, $value): void
    {
        $settingsService = new SettingsService($this->createSystemConfigServiceMock());

        $settingsService->updateSettings([$key => $value]);
        $settings = $settingsService->getSettings();

        static::assertTrue(
            method_exists($settings, $getterName),
            'getter ' . $getterName . ' does not exist'
        );
        static::assertSame($value, $settings->$getterName());
    }

    public function testGetWithWrongPrefix(): void
    {
        $values = ['wrongDomain.brandName' => 'Wrong brand'];
        $settingsService = new SettingsService($this->createSystemConfigServiceMock($values));
        $this->expectException(PayPalSettingsNotFoundException::class);
        $settingsService->getSettings();
    }

    private function createSystemConfigServiceMock(array $settings = []): SystemConfigServiceMock
    {
        $definitionRegistry = new DefinitionInstanceRegistryMock([], new DIContainerMock());
        $systemConfigRepo = $definitionRegistry->getRepository(
            (new SystemConfigDefinition())->getEntityName()
        );

        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $systemConfigService = new SystemConfigServiceMock($connection, $systemConfigRepo);
        foreach ($settings as $key => $value) {
            $systemConfigService->set($key, $value);
        }

        return $systemConfigService;
    }
}
