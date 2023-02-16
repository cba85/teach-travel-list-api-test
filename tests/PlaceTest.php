<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Exception\ClientException;

final class PlaceTest extends TestCase
{
    protected $client;
    protected $locationId;
    protected $placeId;

    public static function setUpBeforeClass(): void
    {
        global $argv;

        if (empty($argv[2])) {
            die('Error: please enter a URL.');
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
            die("\nError: location creation does not work.\nTests won't work for:\n* Get places\n* Add place\nPlease fix location creation before launching tests.\n");
        }

        $location = json_decode($response->getBody());

        file_put_contents(__DIR__ . "/../data/location", $location->id);

        $request = [
            'name' => 'Test Place',
            'lat' => 99.9,
            'lng' => 99.9
        ];

        try {
            $response = $client->post("/api/locations/{$location->id}/places", ['form_params' => $request]);
        } catch (ClientException $e) {
            die("\nError: place creation does not work.\nTests won't work for:\n* Update place\n* Delete place\nPlease fix place creation before launching tests.\n");
        }

        $place = json_decode($response->getBody());
        file_put_contents(__DIR__ . "/../data/place", $place->id);
    }

    protected function setUp(): void
    {
        global $argv;

        $this->client = new GuzzleHttp([
            'base_uri' => $argv[2]
        ]);

        $this->locationId = file_get_contents(__DIR__ . "/../data/location");
        $this->placeId = file_get_contents(__DIR__ . "/../data/place");
    }

    public function test_get_places()
    {
        $response = $this->client->get("/api/locations/{$this->locationId}/places");
        $places = json_decode($response->getBody());

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertIsArray($places);
        $this->assertNotEmpty($places);
        $this->assertIsObject($places[0]);

        $this->assertTrue(isset($places[0]->id));
        $this->assertTrue(isset($places[0]->created_at));
        $this->assertTrue(isset($places[0]->updated_at));
        $this->assertTrue(isset($places[0]->name));
        $this->assertTrue(isset($places[0]->lat));
        $this->assertTrue(isset($places[0]->lng));
    }

    public function test_add_place()
    {
        $request = [
            'name' => 'Test',
            'lat' => 99.9,
            'lng' => 99.9
        ];

        $response = $this->client->post("/api/locations/{$this->locationId}/places", ['form_params' => $request]);
        $place = json_decode($response->getBody());

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertNotEmpty($place);
        $this->assertIsObject($place);

        $this->assertTrue(isset($place->id));
        $this->assertTrue(isset($place->created_at));
        $this->assertTrue(isset($place->updated_at));
        $this->assertTrue(isset($place->name));
        $this->assertTrue(isset($place->lat));
        $this->assertTrue(isset($place->lng));
    }

    public function test_add_place_with_incorrect_arguments()
    {
        try {
            $this->client->post("/api/locations/{$this->locationId}/places");
        } catch (ClientException $e) {
            $this->assertEquals(422, $e->getCode());
        }
    }

    public function test_update_place()
    {
        $request = [
            'name' => 'Test',
            'lat' => 99.9,
            'lng' => 99.9,
            'visited' => 1,
            'location_id' => $this->locationId
        ];

        $response = $this->client->put("/api/places/{$this->placeId}", ['form_params' => $request]);
        $place = json_decode($response->getBody());

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNotEmpty($place);
        $this->assertIsObject($place);

        $this->assertTrue(isset($place->id));
        $this->assertTrue(isset($place->created_at));
        $this->assertTrue(isset($place->updated_at));
        $this->assertTrue(isset($place->name));
        $this->assertTrue(isset($place->lat));
        $this->assertTrue(isset($place->lng));
    }

    public function test_update_place_with_incorrect_arguments()
    {
        try {
            $this->client->put("/api/places/{$this->placeId}");
        } catch (ClientException $e) {
            $this->assertEquals(422, $e->getCode());
        }
    }

    public function test_update_place_does_not_exist()
    {
        $request = [
            'name' => 'Test',
            'lat' => 99.9,
            'lng' => 99.9
        ];

        try {
            $this->client->put("/api/places/999", ['form_params' => $request]);
        } catch (ClientException $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function test_delete_place()
    {


        $response = $this->client->delete("/api/places/{$this->placeId}",);
        $place = json_decode($response->getBody());

        $this->assertEquals(204, $response->getStatusCode());

        $this->assertEmpty($place);
    }

    public function test_delete_place_does_not_exist()
    {
        try {
            $this->client->delete('/api/places/99999');
        } catch (ClientException $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }
}
