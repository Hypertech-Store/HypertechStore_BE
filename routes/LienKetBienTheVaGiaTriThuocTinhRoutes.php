<?php

use App\Http\Controllers\Api\LienKetBienTheVaGiaTriThuocTinhController;
use Illuminate\Support\Facades\Route;

Route::apiResource('lien-ket-bien-the-thuoc-tinh', LienKetBienTheVaGiaTriThuocTinhController::class);
