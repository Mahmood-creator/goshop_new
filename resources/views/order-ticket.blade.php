<!DOCTYPE html>
<html>
<style>
    body {
        font-family: 'dejavu sans', sans-serif;
        font-size: 8px;
        line-height: 8px;
        align-content: center;
    }

    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        padding: 1px;
    }

    #td-center {
        text-align: center;
    }
</style>

<body>
<table style="width: 50%;height: 50%;align-items: center;margin-left: 130px">
    <tr>
        <td>Mupza Express - WayBill</td>
        <td colspan="5" id="td-center">
            @if($order->track_code)
            <div style="padding-left: 40px">{!! DNS1D::getBarcodeHTML("$order->track_code", 'C128B') !!}</div>
            <div style="padding-top: 5px!important; ">{{$order->track_code}}</div>
            @endif
        </td>
    </tr>
    @foreach($order->orderDetails as $detail)
        <tr>
            <td colspan="6">
                1. Shipper
            </td>
        </tr>
        <tr>
            <td>Name</td>
            <td id="td-center" colspan="5">{{$detail->shop->translation->title}}</td>
        </tr>
        <tr>
            <td>Address</td>
            <td id="td-center" colspan="5">{{$detail->shop->translation->address}}</td>
        </tr>
        <tr>
            <td>Type of goods</td>
            <td id="td-center" colspan="5">{{$productTypeName}}</td>
        </tr>
        <tr>
            <td>Store</td>
            <td id="td-center" colspan="5">{{$detail->shop->translation->title}}</td>
        </tr>
        <tr>
            <td colspan="6">
                1. Consignee
            </td>
        </tr>
        <tr>
            <td>Country</td>
            <td id="td-center" colspan="5">{{$order->userAddress->country->translation->title}}</td>
        </tr>
        <tr>
            <td>Name</td>
            <td id="td-center" colspan="5">{{$order->user->firstname}} {{$order->user->lastname}}</td>
        </tr>
        <tr>
            <td>Personal ID</td>
            <td id="td-center" colspan="5">{{$order->user->user_delivery_id}}</td>
        </tr>
        <tr>
            <td>Address</td>
            <td id="td-center" colspan="5">{{$order->userAddress->address}}</td>
        </tr>
        <tr>
            <td colspan="6">
                1. Shipment details
            </td>
        </tr>
        @foreach($detail->orderStocks as $orderProduct)
            <tr>
                <td id="td-center">Quantity</td>
                <td id="td-center">Weight (kg)</td>
                <td id="td-center">Title</td>
                <td id="td-center" colspan="2">Price</td>
                <td id="td-center">Total price</td>
            </tr>
            <tr>
                <td id="td-center">{{$orderProduct->quantity}}</td>
                <td id="td-center">{{$orderProduct->stock->countable->category->weight}}</td>
                <td id="td-center">{{$orderProduct->stock->countable->translation->title}}</td>
                <td id="td-center">({{round($orderProduct->total_price / $orderProduct->quantity * $currencyTry->rate,2)}})TRY</td>
                <td id="td-center">({{round($orderProduct->total_price / $orderProduct->quantity * $currencyUsd->rate,2)}})USD</td>
                <td id="td-center">({{round($orderProduct->total_price * $currencyUsd->rate,2)}})USD</td>
            </tr>
        @endforeach
        <tr>
            <td id="td-center" colspan="6">Total price package</td>
        </tr>
        <tr>
            <td id="td-center" colspan="6">({{round($detail->orderStocks->sum('total_price') * $currencyUsd->rate,2)}})USD</td>
        </tr>
    @endforeach
</table>

</body>
</html>
