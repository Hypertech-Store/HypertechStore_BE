<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Dtos\AllProductResponseDTO;
use Illuminate\Http\JsonResponse;

class SanPhamController extends Controller
{
    public function getAllProduct(): JsonResponse
    {
        // Tạo một danh sách sản phẩm
        $products = [];

        // Sử dụng setter để thiết lập giá trị cho từng sản phẩm
        $product1 = new AllProductResponseDTO('', '', '', '');
        $product1->setId('1');
        $product1->setName('Product A');
        $product1->setPrice('100.00');
        $product1->setImg('path/to/imageA.jpg');

        $product2 = new AllProductResponseDTO('', '', '', '');
        $product2->setId('2');
        $product2->setName('Product B');
        $product2->setPrice('150.00');
        $product2->setImg('path/to/imageB.jpg');

        $product3 = new AllProductResponseDTO('', '', '', '');
        $product3->setId('3');
        $product3->setName('Product C');
        $product3->setPrice('200.00');
        $product3->setImg('path/to/imageC.jpg');

        // Thêm các sản phẩm vào danh sách
        $products[] = $product1;
        $products[] = $product2;
        $products[] = $product3;

        // Chuyển đổi danh sách sản phẩm thành mảng để trả về dưới dạng JSON
        $productArray = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'img' => $product->getImg(),
            ];
        }, $products);

        return response()->json($productArray);
    }
}
