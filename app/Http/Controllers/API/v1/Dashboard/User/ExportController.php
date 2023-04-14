<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\Translation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportController extends UserBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function orderExportPDF(int $id)
    {
        $order = Order::with('orderDetails.orderStocks', 'orderDetails.shop')->find($id);
        if ($order) {
            $pdf = PDF::loadView('order-invoice', compact('order'));
            $pdf->save(Storage::disk('public')->path('export/invoices') . '/order_invoice.pdf');

            return response(Storage::disk('public')->get('/export/invoices/order_invoice.pdf'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment',
            ]);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? config('app.locale')));
    }



}
