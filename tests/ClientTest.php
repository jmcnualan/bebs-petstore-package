<?php

namespace Tests;

use Bebs\Petstore\Client;
use Bebs\Petstore\Exceptions\SuccessFullsException;
use Bebs\Petstore\Models\Pet;
use Bebs\Petstore\PetStore;
use Bebs\Petstore\ServiceProvider;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

class ClientTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $config = require __DIR__ . '/../config/bebs_petstore.php';
        $app['config']->set('bebs_petstore', $config);
    }

    /**
     * {@inheritDoc}
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * Service
     *
     * @return PetStore
     */
    protected function service(): PetStore
    {
        return $this->app->make(PetStore::class);
    }

    /**
     * @test
     * @testdox It can inject
     *
     * @return void
     */
    public function dependencies(): void
    {
        $service = $this->service();
        $this->assertInstanceOf(Client::class, $service);
    }

    /**
     * @test
     * @testdox It can add pet
     *
     * @return void
     */
    public function add(): void
    {
        $this->mockRequest('create_pet_success');
        $service = $this->service();

        $pet = $service->add('martin', 'available', ['photo1', 'photo2'], 'category 1', ['tag2', 'tag1']);

        $this->assertInstanceOf(Pet::class, $pet);
    }

    /**
     * @test
     * @testdox It can handle success false
     *
     * @return void
     */
    public function successFulls(): void
    {
        $this->expectException(SuccessFullsException::class);
        $this->mockRequest('create_pet_client_error');
        $service = $this->service();

        $service->add('martin', 'available', ['photo1', 'photo2'], 'category 1', ['tag2', 'tag1']);
    }

    /**
     * @test
     * @testdox It can handle 400 response
     *
     * @return void
     */
    public function clientError(): void
    {
        $this->expectException(ClientException::class);
        $this->mockRequest('create_pet_success', 400);
        $service = $this->service();

        $service->add('martin', 'available', ['photo1', 'photo2'], 'category 1', ['tag2', 'tag1']);
    }

    /**
     * @test
     * @testdox It can handle 500 response
     *
     * @return void
     */
    public function serverError(): void
    {
        $this->expectException(ServerException::class);
        $this->mockRequest('create_pet_success', 500);
        $service = $this->service();

        $service->add('martin', 'available', ['photo1', 'photo2'], 'category 1', ['tag2', 'tag1']);
    }

    /**
     * Mock request
     *
     * @param string $filePath
     * @param int $statusCode
     *
     * @return void
     */
    protected function mockRequest(string $filePath, int $statusCode = 200)
    {
        $response = file_get_contents(__DIR__ . '/Responses/' . $filePath . '.json');

        $mock = new MockHandler([
            new Response($statusCode, [], $response),
        ]);

        $handlerStack = HandlerStack::create($mock);

        Config::set('bebs_petstore.guzzle.handler', $handlerStack);
    }
}
