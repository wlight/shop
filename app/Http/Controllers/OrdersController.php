<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use Carbon\Carbon;
use App\Http\Requests\OrderRequest;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user = $request->user();

        $order = \DB::transaction(function () use ($user, $request) {
            $address = UserAddress::query()->find($request->input('address_id'));
            $address->update(['last_used_at' => Carbon::now()]);
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            $items = $request->input('items');
            foreach ($items as $data) {
                $sku = ProductSku::query()->find($data['sku_id']);
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decrementStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            $order->update(['total_amount' => $totalAmount]);

            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

            return $order;
        });

        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));
        return $order;
    }
}
