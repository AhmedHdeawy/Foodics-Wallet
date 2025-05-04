<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Services\Transfers\Contracts\TransferServiceContract;
use Illuminate\Http\Response;

class TransferController extends Controller
{
    public function __construct(protected TransferServiceContract $transferService)
    {
    }

    public function transfer(TransferRequest $request): Response
    {
        $xml = $this->transferService->transferMoney($request->validated());

        return $this->xmlResponse($xml);
    }
}
