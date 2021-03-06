<?php

use Casperlaitw\LaravelFbMessenger\Collections\ReceiveMessageCollection;
use Casperlaitw\LaravelFbMessenger\Contracts\AutoTypingHandler;
use Casperlaitw\LaravelFbMessenger\Contracts\BaseHandler;
use Casperlaitw\LaravelFbMessenger\Contracts\PostbackHandler;
use Casperlaitw\LaravelFbMessenger\Contracts\WebhookHandler;
use Casperlaitw\LaravelFbMessenger\Messages\ReceiveMessage;
use Illuminate\Contracts\Config\Repository;
use Mockery as m;

/**
 * User: casperlai
 * Date: 2016/9/4
 * Time: 下午3:24
 */
class WebhookHandlerTest extends TestCase
{
    private $config;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->config = m::mock(Repository::class);
        $this->config
            ->shouldReceive('get')
            ->with('fb-messenger.handlers')
            ->andReturn([BaseHandlerStub::class])
            ->shouldReceive('get')
            ->with('fb-messenger.app_token')
            ->shouldReceive('get')
            ->with('fb-messenger.postbacks')
            ->andReturn([PostbackHandlerStub::class])
            ->shouldReceive('get')
            ->with('fb-messenger.auto_typing')
            ->andReturn(false);
    }

    public function test_postback()
    {
        $collection = m::mock(ReceiveMessageCollection::class);
        $collection
            ->shouldReceive('each')
            ->andReturn([]);

        $webhook = new WebhookHandler($collection, $this->config);
        $webhook->handle();
        $actual = $this->getPrivateProperty(WebhookHandler::class, 'postbacks')->getValue($webhook);

        $this->assertArrayHasKey('MY_TEST_PAYLOAD', $actual);
        $this->assertEquals('MY_TEST_PAYLOAD', $actual['MY_TEST_PAYLOAD']->getPayload());
    }

    public function test_postback_handler_and_run()
    {
        $message = m::mock(ReceiveMessage::class)
            ->shouldReceive('isPayload')
            ->andReturn(true)
            ->shouldReceive('getPostback')
            ->andReturn('MY_TEST_PAYLOAD')
            ->getMock();

        $collection = new ReceiveMessageCollection([$message]);

        $webhook = new WebhookHandler($collection, $this->config);
        $webhook->handle();
    }

    public function test_base_handler_and_run()
    {
        $message = m::mock(ReceiveMessage::class)
            ->shouldReceive('isPayload')
            ->andReturn(false)
            ->getMock();

        $collection = new ReceiveMessageCollection([$message]);

        $webhook = new WebhookHandler($collection, $this->config);
        $webhook->handle();
    }

    public function test_auto_typing_enable()
    {
        $config = m::mock(Repository::class);
        $config
            ->shouldReceive('get')
            ->with('fb-messenger.handlers')
            ->andReturn([BaseHandlerStub::class])
            ->shouldReceive('get')
            ->with('fb-messenger.app_token')
            ->shouldReceive('get')
            ->with('fb-messenger.postbacks')
            ->andReturn([PostbackHandlerStub::class])
            ->shouldReceive('get')
            ->with('fb-messenger.auto_typing')
            ->andReturn(true);

        $message = m::mock(ReceiveMessage::class)
            ->shouldReceive('isPayload')
            ->andReturn(false)
            ->shouldReceive('getSender')
            ->getMock();

        $collection = new ReceiveMessageCollection([$message]);

        $webhook = new WebhookHandler($collection, $config);
        $webhook->handle();

        $actual = $this->getPrivateProperty(WebhookHandler::class, 'handlers')->getValue($webhook);
        $this->assertInstanceOf(AutoTypingHandler::class, $actual[0]);
    }
}

class BaseHandlerStub extends BaseHandler
{

    /**
     * Handle the chatbot message
     *
     * @param ReceiveMessage $message
     *
     * @return mixed
     */
    public function handle(ReceiveMessage $message)
    {
        // TODO: Implement handle() method.
    }
}

class PostbackHandlerStub extends PostbackHandler
{
    protected $payload = 'MY_TEST_PAYLOAD';

    /**
     * Handle the chatbot message
     *
     * @param ReceiveMessage $message
     *
     * @return mixed
     */
    public function handle(ReceiveMessage $message)
    {
        // TODO: Implement handle() method.
    }
}
