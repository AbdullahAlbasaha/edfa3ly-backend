<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreRequest;
use App\Models\Cart;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    use CartTrait;

    //----------------------------------
    //  Display Cart Content && Bill
    //----------------------------------

    public function index()
    {
        self::validation(["currency" => "sometimes|string|".Rule::in(['EGP','USD'])],["currency.in" => "currency should be one of EGP OR USD"]);

        $res = Cart::content();
         return response($res['data'],$res['code']);
    }

    //----------------------------------
    //        Add Items To Cart
    //----------------------------------

    public function store(StoreRequest $request)
    {
        $res = Cart::store($request->cart);

        return response($res['data'],$res['code']);
    }

    //----------------------------------
    //        Update Cart Item By Id
    //----------------------------------

    public function update(Request $request,$id)
    {
        self::validation([
            "currency" => "sometimes|string|".Rule::in(['EGP','USD']),
            "qty" => "required|integer|min:1",
        ],["currency.in" => "currency should be one of EGP OR USD"]);

        $item = Cart::updateItem($id,$request->qty);
        if (!$item)
            return response(['message' => 'item not found in cart !','status' => false],422);
        return response(['data' => $item,'status' => true],200);

    }

    //----------------------------------
    //        Delete Cart Item By Id
    //----------------------------------

    public function destroy($id)
    {
        $item = Cart::deleteItem($id);
        if (!$item)
            return response(['message' => 'item not found in cart !','status' => false],422);
        return response()->noContent();

    }

    //----------------------------------
    //           Destroy Cart
    //----------------------------------

    public function destroyCart()
    {
        $destroyCart = Cart::destroyCart();
        if ($destroyCart)
        return response()->noContent();
        return response(['message' => 'cart did not be destroyed !','status' => false],422);

    }

    //----------------------------------
    //        Validate Requests
    //----------------------------------

    private static function validation($rules,$messages){
        $validator = Validator::make(request()->all(),$rules,$messages);
        if ($validator->fails())
            throw new HttpResponseException(new Response(collect(['message' => $validator->errors()->first(),'status' => false]),422));

    }

}
