<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPayPal\Test\Webhook\Exception;

use PHPUnit\Framework\TestCase;
use SwagPayPal\Webhook\Exception\WebhookAlreadyExistsException;
use Symfony\Component\HttpFoundation\Response;

class WebhookAlreadyExistsExceptionTest extends TestCase
{
    public function testGetStatusCode(): void
    {
        $webhookUrl = 'www.test.de';
        $exception = new WebhookAlreadyExistsException($webhookUrl);

        self::assertSame(sprintf('WebhookUrl "%s" already exists', $webhookUrl), $exception->getMessage());
        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }
}
