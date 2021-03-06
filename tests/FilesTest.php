<?php

class FilesTest extends AppTest
{

    public function setUp()
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutEvents();
        factory(\Yab\Quarx\Models\Files::class)->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->call('GET', 'quarx/files');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertViewHas('files');
    }

    public function testCreate()
    {
        $response = $this->call('GET', 'quarx/files/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->call('GET', 'quarx/files/'.CryptoService::encrypt(1).'/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertViewHas('files');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/test-file.txt', 'test-file.txt');
        $file = factory(\Yab\Quarx\Models\Files::class)->make([
            'id' => 2,
            'location' => [
                'file_a' => [
                    'name' => CryptoService::encrypt('test-file.txt'),
                    'original' => 'test-file.txt',
                    'mime' => 'txt',
                    'size' => 24,
                ],
            ]
        ]);
        $response = $this->call('POST', 'quarx/files', $file->getAttributes());
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUpload()
    {
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/test-file.txt', 'test-file.txt');
        $file = (array) factory(\Yab\Quarx\Models\Files::class)->make([ 'id' => 2 ]);
        $response = $this->call('POST', 'quarx/files/upload', [], [], ['location' => $uploadedFile]);

        $this->assertEquals(200, $response->getStatusCode());
        $jsonResponse = json_decode($response->getContent());
        $this->assertTrue(is_object($jsonResponse));
        $this->assertEquals($jsonResponse->status, 'success');
    }

    public function testSearch()
    {
        $response = $this->call('POST', 'quarx/files/search', ['term' => 'wtf']);

        $this->assertViewHas('files');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $file = (array) factory(\Yab\Quarx\Models\Files::class)->make([ 'id' => 3, 'title' => 'dumber' ]);
        $response = $this->call('PATCH', 'quarx/files/'.CryptoService::encrypt(3), $file);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertRedirectedTo('/quarx/files');
    }

    public function testDelete()
    {
        Storage::put('test-file.txt', 'what is this');
        $file = factory(\Yab\Quarx\Models\Files::class)->make([
            'id' => 2,
            'location' => [
                'file_a' => [
                    'name' => CryptoService::encrypt('test-file.txt'),
                    'original' => 'test-file.txt',
                    'mime' => 'txt',
                    'size' => 24,
                ],
            ]
        ]);
        $this->call('POST', 'quarx/files', $file->getAttributes());

        $response = $this->call('DELETE', 'quarx/files/'.CryptoService::encrypt(2));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertRedirectedTo('quarx/files');
    }

}

