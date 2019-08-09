<?php declare(strict_types=1);

namespace Yiifabric\Authorization\Tests;

use ReflectionClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApplication;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->destroyApplication();

        parent::tearDown();
    }

    /**
     * Mock console application
     * @return void
     */
    protected function mockApplication(): void
    {
        $config = require __DIR__ . '/config/test.php';

        try {
            new ConsoleApplication($config);
        } catch (InvalidConfigException $e) {
            echo $e->getMessage() . "\n";
            exit;
        }
    }

    /**
     * Destroy mocked application.
     * @return void
     */
    protected function destroyApplication(): void
    {
        Yii::$app = null;
    }

    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     * @throws \ReflectionException
     */
    protected function invoke(object $object, string $method, array $args = [])
    {
        $rClass = new ReflectionClass(get_class($object));

        $rMethod = $rClass->getMethod($method);

        $rMethod->setAccessible(true);
        $invoked = $rMethod->invokeArgs($object, $args);
        $rMethod->setAccessible(false);

        return $invoked;
    }
}
