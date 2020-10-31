<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function return_cart_content_with_bill_in_different_currencies()
    {
        $this->store_cart_items_for_test();
        $currency = "USD"; // or EGP

        $res = $this->json('GET', route('cart.index', ['currency' => $currency]));
        $res
            ->assertJsonStructure([
                "data" => [
                    "products" => [
                        "*" => ['id', 'name', 'qty', 'price', 'sub_total', 'tax', 'discount', 'grand_total'],
                    ],
                    "bill" => [
                        "sub_total", "tax", "discount", "total"
                    ],
                ],
                "status"
            ])
            ->assertSee(["USD" => "$", "EGP" => "e\u00a3"][$currency]) // currency check
            ->assertStatus(200);
    }

    /**
     ** @test
     */
    public function add_multiple_items_to_cart()
    {
        $res = $this->json('POST', route('cart.store'), [
            "cart" => [
                ['id' => 1, 'name' => 'pants', 'qty' => 2],
                ['id' => 2, 'name' => 'jacket', 'qty' => 6],
                ['id' => 3, 'name' => 't-shirt', 'qty' => 5],
                ['id' => 4, 'name' => 'shoes', 'qty' => 3],
            ]
        ]);
        $res
            ->assertJsonStructure([
                "data" => [
                    "*" => ['id', 'name', 'qty', 'price', 'sub_total', 'tax', 'discount', 'grand_total'],

                ]
            ])
            ->assertStatus(201);
    }

    /**
     **@test
     */
    public function update_exists_cart_item_by_id()
    {
        $this->store_cart_items_for_test();
        $newQty = 1;
        $id = 4;

        $res = $this->json('PUT', route('cart.update', $id), [
            "qty" => $newQty
        ]);
        $res
            ->assertJsonStructure([
                "data" => [
                    'id', 'name', 'qty', 'price', 'sub_total', 'tax', 'discount', 'grand_total',
                ]
            ])
            ->assertSee(":" . $newQty)
            ->assertStatus(200);
    }

    /**
     **@test
     */
    public function delete_exists_cart_item_by_id()
    {
        $this->store_cart_items_for_test();
        $id = 1;

        $res = $this->json('DELETE', route('cart.destroy', $id));
        $res->assertDontSee('status');
        $res->assertNoContent(204);
    }

    /**
     **@test
     */
    public function destroy_cart()
    {
        $this->store_cart_items_for_test();

        $res = $this->json('DELETE', route('cart.cart_destroy'));
        $res->assertDontSee('status');
        $res->assertNoContent(204);
    }


    private function store_cart_items_for_test(){

        //no problem if item "qty" is changed
        Cart::store([
            ['id' => 1, 'name' => 'pants', 'qty' => 2],
            ['id' => 2, 'name' => 'jacket', 'qty' => 6],
            ['id' => 3, 'name' => 't-shirt', 'qty' => 5],
            ['id' => 4, 'name' => 'shoes', 'qty' => 3],
        ]);
    }
}
