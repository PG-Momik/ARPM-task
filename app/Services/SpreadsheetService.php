<?php
declare(strict_types=1);

namespace app\Services;

use App\Jobs\ProcessProductImage;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SpreadsheetService
{
    /**
     * @param $filePath
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function processSpreadsheet($filePath): void
    {
        $products_data = app('importer')->import($filePath);

        foreach ($products_data as $row) {
            $validator = Validator::make($row, [
                'product_code' => 'required|unique:products,product_code',
                'quantity'     => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                continue;
            }

            $product = Product::create($validator->validated());

            ProcessProductImage::dispatch($product);
        }
    }
}
