<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\ProcessProductImage;
use App\Models\Product;
use App\Services\SpreadsheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SpreadsheetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SpreadsheetService();
        Queue::fake();
    }

    private function mockImporter(array $data)
    {
        $importer = \Mockery::mock('importer');
        $importer->shouldReceive('import')->andReturn($data);

        $this->app->instance('importer', $importer);

        return $importer;
    }

    public function test_it_processes_valid_spreadsheet_data()
    {
        $mockValidData = [
            ['product_code' => 'PROD001', 'quantity' => 10],
            ['product_code' => 'PROD002', 'quantity' => 5]
        ];

        $this->mockImporter($mockValidData);

        $this->service->processSpreadsheet('dummy_path.csv');

        $this->assertCount(2, Product::all());
        $this->assertDatabaseHas('products', ['product_code' => 'PROD001']);
        $this->assertDatabaseHas('products', ['product_code' => 'PROD002']);

        Queue::assertPushed(ProcessProductImage::class, 2);
    }

    public function test_it_skips_invalid_rows()
    {
        $mockInvalidQuantityData = [
            ['product_code' => 'PROD001', 'quantity' => 10],
            ['product_code' => '', 'quantity' => 5],
            ['product_code' => 'PROD001', 'quantity' => 'abc'],
        ];

        $this->mockImporter($mockInvalidQuantityData);

        $this->service->processSpreadsheet('dummy_path.csv');

        $this->assertCount(1, Product::all());
        $this->assertDatabaseHas('products', ['product_code' => 'PROD001']);
        $this->assertDatabaseMissing('products', ['product_code' => '']);

        Queue::assertPushed(ProcessProductImage::class, 1);
    }

    public function test_it_handles_duplicate_product_codes()
    {
        $existingData = ['product_code' => 'EXISTING', 'quantity' => 99];

        /** Basically writing to db so that product with code EXISTING already exists */
        Product::create($existingData);

        $mockDataContainingExistingData = [
            $existingData,
            ['product_code' => 'NEW001', 'quantity' => 5],
        ];

        $this->mockImporter($mockDataContainingExistingData);

        $this->service->processSpreadsheet('dummy_path.csv');

        $this->assertCount(2, Product::all());
        $this->assertDatabaseHas('products', ['product_code' => 'NEW001']);

        Queue::assertPushed(ProcessProductImage::class, 1);
    }

    public function test_it_handles_empty_spreadsheet()
    {
        $this->mockImporter([]);

        $this->service->processSpreadsheet('empty_file.csv');

        $this->assertCount(0, Product::all());

        Queue::assertNotPushed(ProcessProductImage::class);
    }

    public function test_it_validates_quantity_as_positive_integer()
    {
        $mockDataWithNegativeQuantity = [
            ['product_code' => 'PROD001', 'quantity' => 0],
            ['product_code' => 'PROD002', 'quantity' => -5],
            ['product_code' => 'PROD003', 'quantity' => 3.5],
            ['product_code' => 'PROD004', 'quantity' => 10],
        ];

        $this->mockImporter($mockDataWithNegativeQuantity);

        $this->service->processSpreadsheet('dummy_path.csv');

        $this->assertCount(1, Product::all());
        $this->assertDatabaseHas('products', ['product_code' => 'PROD004']);

        Queue::assertPushed(ProcessProductImage::class, 1);
    }
}
