<?php


namespace App\Http\Controllers\Api;


use App\Models\Cart;

trait CartTrait
{
    //handle item  structure
    public function itemOperations($newItem,$qty){

        $discountPercent = [0,0,0,10][$newItem['id'] - 1];

        $sub_total= $qty * $newItem['price'];
        $tax =  $sub_total * 14 / 100;
        $subTotalTax = $sub_total + $tax;
        $discount = $subTotalTax * $discountPercent / 100;
        $grand_total = $subTotalTax - $discount;

//        $discount = [0,0,0,10][$newItem['id'] - 1];
//        $newItem['sub_total'] = (string)round($qty * $newItem['price'], 2);
//        $newItem['tax'] = (string)round($newItem['sub_total'] * 14 / 100, 2);
//        $subTotalTax = $newItem['sub_total'] + $newItem['tax'];
//        $newItem['discount'] = (string)round($subTotalTax * $discount / 100, 2);
//        $newItem['grand_total'] = (string)round($subTotalTax - $newItem['discount'], 2);
        return [
            'id' => $newItem['id'],
            'name' => $newItem['name'],
            'price' => $newItem['price'],
            'qty' => $qty,
            'sub_total' => $sub_total,
            'tax' => $tax,
            'discount' => $discount,
            'grand_total' => $grand_total,
        ];
    }

    //handle && retrieve bill
    public function bill($items,$unitTest =false)
    {
        $discount = [];
        if ($items->pluck('discount')->sum())
            $discount['10% off shoes'] = $this->presentPrice($items->pluck('discount')->sum(),$unitTest);
        if ($this->jacket_discount($items))
            $discount['50% off jacket'] = $this->presentPrice($this->jacket_discount($items),$unitTest);
            if (!count($discount) > 0)
                $discount = 0;
         $sub_total = $items->pluck('sub_total')->sum();
         $tax = $items->pluck('tax')->sum();
         $total = $items->pluck('grand_total')->sum() - $this->jacket_discount($items);
         return ['sub_total' => $this->presentPrice($sub_total,$unitTest),'tax' => $this->presentPrice($tax,$unitTest) ,'discount' =>  $discount,'total' => $this->presentPrice($total,$unitTest)];
    }

    //check and retrieve jacket discount
    private function jacket_discount($items){
        $t_shirt = $items->where('id',3)->first();
        $jacket = $items->where('id',2)->first();
        $discount = 0;
        if ($t_shirt && $jacket){
            $t_shirtQty = $t_shirt['qty']%2 == 0?$t_shirt['qty']:$t_shirt['qty'] -1;
            $jacketPrice = $jacket['price']*(1 +  14 / 100);
            $expectedDiscount = $t_shirtQty * $jacketPrice /4;
            $jacketDiscount = $jacket['qty'] * $jacketPrice /2;
            $discount = $jacketDiscount < $expectedDiscount ?$jacketDiscount:$expectedDiscount;
        }
        return $discount;
    }

    // round && present  price in different currencies
    public function presentPrice($number,$unitTest = false,$unitTestCurrency =null)
    {
        $currency = "USD";
        if (!$unitTest && request()->currency && request()->currency == "EGP") {
            $currency = request()->currency;
            $number = $number * 15.77;
        }
        if ($unitTestCurrency && $unitTestCurrency === "EGP")
            $number = $number * 15.77;
        $options = [
            'alwaysShowDecimals' => true,
            'nbDecimals' => 2,
            'decPoint' => ".",
            'thousandSep' => "",
            'moneySymbol' => ["USD" => "$" ,"EGP" => "e£"][$currency],
            'moneyFormat' => ["USD" => "sv" ,"EGP" => "vs"][$currency],
            'USD_space' => ["USD" => " " ,"EGP" => ""][$currency],
            'EGP_space' => ["USD" => "" ,"EGP" => " "][$currency],
        ];
        extract($options);

       $v = number_format($number, $nbDecimals, $decPoint, $thousandSep);
        if (false === $alwaysShowDecimals && $nbDecimals > 0) {
            $p = explode($decPoint, $v);
            $dec = array_pop($p);
            if (0 === (int)$dec) {
                $v = implode('', $p);
            }
        }
        $ret = str_replace([
            'v',
            's',
        ], [
            $USD_space.$v.$EGP_space,
            $moneySymbol,
        ], $moneyFormat);
        return $ret;
    }
}
