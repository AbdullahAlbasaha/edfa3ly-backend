<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\CartTrait;
use PHPUnit\Framework\TestCase;

class CartTraitTest extends TestCase
{
    use CartTrait;
    /**
     * A basic test example.
     *
     * @test
     */
    public function build_item_structure()
    {
        $item = ['id' => 4, 'name' => 'shoes', 'price' => "24.99"];
        $qty = 10;
        $discountPercent = 10;
        $sub_total= $qty * $item['price'];
        $tax =  $sub_total * 14 / 100;
        $subTotalTax = $sub_total + $tax;
        $discount = $subTotalTax * $discountPercent / 100;
        $grand_total = $subTotalTax - $discount;


        $structuredItem = $this->itemOperations($item,$qty);
        $this->assertSame($structuredItem,[
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'qty' => $qty,
            'sub_total' => $sub_total,
            'tax' => $tax,
            'discount' => $discount,
            'grand_total' => $grand_total,
        ]);
    }

    /**
     * A basic test example.
     *
     * @test
     */
    public function calculate_jacket_discount()
    {
        $t_shirt = ['id' => 3, 'name' => 't-shirt', 'price' => "10.99"];
        $jacket = ['id' => 2, 'name' => 'jacket', 'price' => "19.99"];
        $t_shirtQty = 12;
        $jacketQty = 10;

        $t_shirt['qty'] = $t_shirtQty;
        $jacket['qty'] = $jacketQty;

        $discount = 0;
        if ($t_shirt && $jacket){
            $t_shirtQty = $t_shirtQty%2 == 0?$t_shirtQty:$t_shirtQty -1;
            $jacketPrice = $jacket['price']*(1 +  14 / 100);
            $expectedDiscount = $t_shirtQty * $jacketPrice /4;
            $jacketDiscount = $jacketQty * $jacketPrice /2;
            $discount = $jacketDiscount < $expectedDiscount ?$jacketDiscount:$expectedDiscount;
        }
        $this->assertSame($discount,$this->jacket_discount(collect([$t_shirt,$jacket])));
    }

    /**
     * A basic test example.
     *
     * @test
     */
    public function change_currency()
    {
        $currency = "USD";
        $number = 156;
       $number = ["USD" => $number,"EGP" => 15.77 * $number][$currency];
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
      $this->assertSame($ret,$this->presentPrice($number,true,$currency));
    }


    /**
     * A basic test example.
     *
     * @test
     */
    public function calculate_bill_details()
    {
        $items  = $this->items();
        $discount = [];
        if ($items->pluck('discount')->sum())
            $discount['10% off shoes'] = $this->presentPrice($items->pluck('discount')->sum(),true);
        if ($this->jacket_discount($items))
            $discount['50% off jacket'] = $this->presentPrice($this->jacket_discount($items),true);
        if (!count($discount) > 0)
            $discount = 0;
        $sub_total = $items->pluck('sub_total')->sum();
        $tax = $items->pluck('tax')->sum();
        $total = $items->pluck('grand_total')->sum() - $this->jacket_discount($items);
        $bill =  ['sub_total' => $this->presentPrice($sub_total,true),'tax' => $this->presentPrice($tax,true) ,'discount' =>  $discount,'total' => $this->presentPrice($total,true)];
        $this->assertSame($bill,$this->bill($items,true));

    }


    private function items(){

        //no problem if item "qty" is changed

        $data = [
               ['id' => 1, 'name' => 'pants', 'price' => "14.99","qty" => 2],
               ['id' => 2, 'name' => 'jacket', 'price' => "19.99","qty" => 6],
               ['id' => 3, 'name' => 't-shirt', 'price' => "10.99","qty" => 10],
               ['id' => 4, 'name' => 'shoes', 'price' => "24.99","qty" => 3],
        ];
        foreach ($data as $item){
            $items[] = $this->itemOperations($item,$item['qty']);
        }
        return collect($items);
    }
}
