<?php

namespace Mrmr7\LaravelShahin\Services\Account;

use Mrmr7\LaravelShahin\Contracts\HasToken;
use Mrmr7\LaravelShahin\Services\Request;

class AccountBalanceRequest extends Request implements HasToken
{
    public string $endPoint = 'obh/api/aisp/get-account-balance';

    public function __construct(private $bank, private $nationalCode, private $sourceAccount)
    {
        parent::__construct();
    }

    public function getBody(): array
    {
        return [
            'bank' => $this->bank,
            'nationalCode' => $this->nationalCode,
            'sourceAccount' => $this->sourceAccount,
        ];
    }
}
