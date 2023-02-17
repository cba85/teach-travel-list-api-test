<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Exception\ClientException;

final class LocationTest extends TestCase
{
    protected $client;
    protected $locationId;

    public static function setUpBeforeClass(): void
    {
        global $argv;

        if (empty($argv[2])) {
            die('Error: please enter a URL.\n');
        }

        $client = new GuzzleHttp([
            'base_uri' => $argv[2]
        ]);

        $request = [
            'name' => 'Test Location',
            'lat' => 99.9,
            'lng' => 99.9
        ];

        try {
            $response = $client->post('/api/locations', ['form_params' => $request]);
        } catch (ClientException $e) {
            die("\nError: location creation does not work.\nTests won't work for:\n* Get location\n* Delete location\nPlease fix location creation before launching tests.\n");
        }

        $location = json_decode($response->getBody());

        file_put_contents(__DIR__ . "/../data/location", $location->id);
    }

    protected function setUp(): void
    {
        global $argv;

        $this->client = new GuzzleHttp([
            'base_uri' => $argv[2]
        ]);

        $this->locationId = file_get_contents(__DIR__ . "/../data/location");
    }

    public function test_get_locations()
    {
        $response = $this->client->get('/api/locations');
        $locations = json_decode($response->getBody());

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertIsArray($locations);
        $this->assertNotEmpty($locations);
        $this->assertIsObject($locations[0]);

        $this->assertTrue(isset($locations[0]->id));
        $this->assertTrue(isset($locations[0]->created_at));
        $this->assertTrue(isset($locations[0]->updated_at));
        $this->assertTrue(isset($locations[0]->name));
        $this->assertTrue(isset($locations[0]->slug));
        $this->assertTrue(isset($locations[0]->lat));
        $this->assertTrue(isset($locations[0]->lng));
    }

    public function test_get_location()
    {
        $response = $this->client->get("/api/locations/{$this->locationId}");
        $location = json_decode($response->getBody());

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertIsObject($location);
        $this->assertNotEmpty($location);

        $this->assertTrue(isset($location->id));
        $this->assertTrue(isset($location->created_at));
        $this->assertTrue(isset($location->updated_at));
        $this->assertTrue(isset($location->name));
        $this->assertTrue(isset($location->slug));
        $this->assertTrue(isset($location->lat));
        $this->assertTrue(isset($location->lng));
    }

    public function test_get_location_does_not_exist()
    {
        try {
            $this->client->get('/api/locations/99999');
        } catch (ClientException $e) {
            $this->assertEquals(404, $e->getCode());
        }

        $this->assertEquals(404, $e->getCode());
    }

    public function test_add_location()
    {
        $request = [
            'name' => 'Test',
            'lat' => 99.9,
            'lng' => 99.9
        ];

        $response = $this->client->post('/api/locations', ['form_params' => $request]);
        $location = json_decode($response->getBody());

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertNotEmpty($location);
        $this->assertIsObject($location);

        $this->assertTrue(isset($location->id));
        $this->assertTrue(isset($location->created_at));
        $this->assertTrue(isset($location->updated_at));
        $this->assertTrue(isset($location->name));
        $this->assertTrue(isset($location->slug));
        $this->assertTrue(isset($location->lat));
        $this->assertTrue(isset($location->lng));
    }

    public function test_add_location_with_incorrect_arguments()
    {
        try {
            $this->client->post('/api/locations');
        } catch (ClientException $e) {
            $this->assertEquals(422, $e->getCode());
        }

        $this->assertEquals(422, $e->getCode());
    }

    public function test_delete_location()
    {
        $response = $this->client->delete("/api/locations/{$this->locationId}",);
        $location = json_decode($response->getBody());

        $this->assertEquals(204, $response->getStatusCode());

        $this->assertEmpty($location);
    }

    public function test_delete_location_does_not_exist()
    {
        try {
            $this->client->delete('/api/locations/99999');
        } catch (ClientException $e) {
            $this->assertEquals(404, $e->getCode());
        }

        $this->assertEquals(404, $e->getCode());
    }
}
