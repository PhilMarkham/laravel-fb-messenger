<?php

use Casperlaitw\LaravelFbMessenger\Contracts\DefaultHandler;
use Casperlaitw\LaravelFbMessenger\Controllers\WebhookController;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery as m;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * User: casperlai
 * Date: 2016/9/10
 * Time: 下午3:27
 */
class WebhookControllerTest extends TestCase
{
    public function test_index_token_verify_pass_response_challenge_token()
    {
        $verifyToken = 'MY_VERIFY_TOKEN';
        $challenge = str_random();
        $request = m::mock(Request::class)
            ->shouldReceive('get')
            ->with('hub_mode')
            ->andReturn('subscribe')
            ->shouldReceive('get')
            ->with('hub_verify_token')
            ->andReturn($verifyToken)
            ->shouldReceive('get')
            ->with('hub_challenge')
            ->andReturn($challenge)
            ->getMock();

        $config = m::mock(Repository::class)
            ->shouldReceive('get')
            ->andReturn($verifyToken)
            ->getMock();

        $controller = new WebhookController($config);
        $response = $controller->index($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($challenge, $response->getContent());
    }

    public function test_index_and_abort()
    {
        $this->expectException(NotFoundHttpException::class);
        $request = m::mock(Request::class)
            ->shouldReceive('get')
            ->andReturn('')
            ->getMock();
        $config = m::mock(Repository::class)
            ->shouldReceive('get')
            ->andReturn(str_random())
            ->getMock();

        $controller = new WebhookController($config);
        $controller->index($request);
    }

    public function test_receive()
    {
        $config = m::mock(Repository::class);
        $config
            ->shouldReceive('get')
            ->with('fb-messenger.handlers')
            ->andReturn([DefaultHandler::class])
            ->shouldReceive('get')
            ->with('fb-messenger.app_token')
            ->shouldReceive('get')
            ->with('fb-messenger.postbacks')
            ->andReturn([])
            ->shouldReceive('get')
            ->with('fb-messenger.auto_typing')
            ->andReturn(false);

        $request = m::mock(Request::class)
            ->shouldReceive('input')
            ->with('entry.0.messaging')
            ->andReturn([])
            ->getMock();

        $controller = new WebhookController($config);
        $controller->receive($request);
    }
}

class HandlerNotExtendsBaseHandlerStub
{
}
