<?php

namespace App\Models;

use App\Http\Controllers\Api\CartTrait;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use CartTrait;
    protected $guarded = ['id'];

    public function setContentAttribute($val)
    {
        $this->attributes['content'] =  json_encode($val,JSON_PRETTY_PRINT);
    }

    public function getContentAttribute($val)
    {
        return json_decode($val,true);
    }

    public function scopeStore($query,$cart)
    {
        // items init
        $newItems = [];
        $items = [
            'pants' => ['id' => 1, 'name' => 'pants', 'price' => "14.99"],
            'jacket' => ['id' => 2, 'name' => 'jacket', 'price' => "19.99"],
            't-shirt' => ['id' => 3, 'name' => 't-shirt', 'price' => "10.99"],
            'shoes' => ['id' => 4, 'name' => 'shoes', 'price' => "24.99"],
        ];
        // cart handle
        foreach ($cart as $item)
        {
            $newItem = $items[$item['name']];
            $newItem = $this->itemOperations($newItem,$item['qty']);

          //check if exists

            $exists  = self::items()->where('id',$newItem['id'])->first();
            if (!$exists)
                $newItems[] = $newItem;
             else
                 return ['data' => ['message' => 'this item '.$newItem['name'] .' already in the cart !','status' => false],'code' => 422];
        }
        //add items to cart
        $storedItems = self::items()->merge(collect($newItems));

        //store new in DB
              $query->firstOrNew()->fill(['content' => $storedItems])->save();

        // display new stored items
              return ['data' => ['data' => self::presentItems(collect($newItems)),'status' => true],'code' => 201];
    }

    public function scopeUpdateItem($query,$id,$qty)
    {
        $exists  = self::items()->where('id',$id)->first();

        //check if item exists

        if ($exists) {
            $exists = $this->itemOperations($exists,$qty);

         //Update Cart

            $items = self::items()->filter(function ($oldItem) use ($exists) {
                return $exists['id'] != $oldItem['id'];
            })->add($exists)->values();
        }
        else
            return false;
        //update item in DB
        $query->firstOrNew()->fill(['content' => $items])->save();

        //display item
        return self::presentItems($exists,false);
    }

    public function scopeDeleteItem($query,$id)
    {
        $exists  = self::items()->where('id',$id)->first();
        if ($exists){
           // remove from cart

            $items =  self::items()->filter(function ($oldItem)use($exists){
                return $exists['id'] != $oldItem['id'];
            })->values();
        }
        else
            return false;
        //remove from DB

        $query->firstOrNew()->fill(['content' => $items])->save();
    }

    // retrieve cart from DB
    public function scopeItems($query){
        return collect($query->first()->content ?? []);

    }
    public function scopeContent($query)
    {
         $items  = self::items();
                if ($items->count() > 0)
        return ['data' => ['data' => ['products' => self::presentItems($items),'bill' => $this->bill($items)],'status' => true],'code' => 200];

        return ['data' => ['message' => 'empty cart !','status' => true],'code' => 200];

    }

    public function scopeDestroyCart($query)
    {
        //destroy cart in DB
        return $query->first()->delete();

    }

    protected function scopePresentItems($query,$items,$plural=true){
        if (!$plural){
            extract($items);
            return [
                'id' => $id,
                'name' => $name,
                'qty' => $qty,
                'price' => $this->presentPrice($price),
                'sub_total' => $this->presentPrice($sub_total),
                'tax' => $this->presentPrice($tax),
                'discount' => $this->presentPrice($discount),
                'grand_total' => $this->presentPrice($grand_total),
            ];
        }
           return $items->map(function ($item){
                extract($item);
                return [
                    'id' => $id,
                    'name' => $name,
                    'qty' => $qty,
                    'price' => $this->presentPrice($price),
                    'sub_total' => $this->presentPrice($sub_total),
                    'tax' => $this->presentPrice($tax),
                    'discount' => $this->presentPrice($discount),
                    'grand_total' => $this->presentPrice($grand_total),
                ];
            });
    }
}

