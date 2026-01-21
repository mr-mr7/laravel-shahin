<?php

namespace Mrmr7\LaravelShahin\Services\Account;

use Mrmr7\LaravelShahin\Contracts\HasToken;
use Mrmr7\LaravelShahin\Services\Request;

class AccountStatementRequest extends Request implements HasToken
{
    public string $endPoint = 'obh/api/aisp/get-account-statement';

    public function __construct(private $bank, private $nationalCode, private $sourceAccount, private $fromDate = null, private $fromTime = null, private $toDate = null, private $toTime = null)
    {
        parent::__construct();
    }

    public function getBody(): array
    {
        return [
            'bank' => $this->bank,
            'nationalCode' => $this->nationalCode,
            'sourceAccount' => $this->sourceAccount,
            'fromDate' => $this->fromDate,
            'fromTime' => $this->fromTime,
            'toDate' => $this->toDate,
            'toTime' => $this->toTime,
        ];
    }
}
