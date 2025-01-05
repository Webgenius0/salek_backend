<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardStoreRequest;
use App\Services\CardService;

class CardController extends Controller
{
    public $cardServiceObj;

    public function __construct()
    {
        $this->cardServiceObj = new CardService();    
    }

    /**
     * index method for retrive card index
     * call service class method
     *
     * @return mixed
    */
    public function index()
    {
        return $this->cardServiceObj->index();
    }

    /**
     * Store card method
     * call service class method
     *
     * @param CardStoreRequest $request
     * @return mixed
    */
    public function store(CardStoreRequest $request)
    {
        $name       = $request->input('name');
        $cardNumber = $request->input('card_number');
        $expireDate = $request->input('expire_date');
        $cvv        = $request->input('cvv');

        return $this->cardServiceObj->store(
           (string) $name,
           (string) $cardNumber, 
           $expireDate,
           (string) $cvv
        );
    }
}
