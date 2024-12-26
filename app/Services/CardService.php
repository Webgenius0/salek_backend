<?php

namespace App\Services;

use App\Models\Card;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CardService extends Service
{
    use ApiResponse;
    
    public $cardObj;

    public function __construct()
    {
        $this->cardObj = new Card();
    }

    /**
     * Card list method
     *
     * @return mixed
    */
    public function index()
    {
        $cards = Card::select('id', 'cardholder_name', 'card_number', 'expiry_date', 'cvv')
                ->where('created_by', Auth::id())
                ->where('status', 'active')->get()->toArray();

        
        if(empty($cards)):
            return $this->failedResponse('You have no cards', 404);
        endif;
        return $this->successResponse(true, 'Card List', $cards, 200);
    }

    /**
     * store card method
     * comes from controller
     *
     * @param string $name
     * @param string $cardNumber
     * @param timestamp $expireDate
     * @param string $cvv
     * @return mixed
    */
    public function store(string $name, string $cardNumber, $expireDate, string $cvv)
    {
        try {
            DB::beginTransaction();

            $this->cardObj->created_by      = Auth::id();
            $this->cardObj->cardholder_name = $name;
            $this->cardObj->card_number     = $cardNumber;
            $this->cardObj->expiry_date     = $expireDate;
            $this->cardObj->cvv             = $cvv;

            $res = $this->cardObj->save();

            DB::commit();
            if($res){
                return $this->successResponse(true, 'Card create successfully', $this->cardObj, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json([
                'status'  => false,
                'message' => 'Database Error',
                'error'   => $e->getMessage(),
                'code'    => 422
            ]);
        }
    }
}
