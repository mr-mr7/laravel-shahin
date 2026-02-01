<?php

namespace Mrmr7\LaravelShahin\Services\Account;

use Carbon\Carbon;
use Mrmr7\LaravelShahin\Exceptions\ShahinException;
use Mrmr7\LaravelShahin\Facades\Shahin;
use Mrmr7\LaravelShahin\Services\ShahinService;

class AccountService extends ShahinService
{
    public function accountInfo($sourceAccount = null): array
    {
        $bank = Shahin::bank();
        $nationalCode = Shahin::token()->getUsername();
        $allAccounts = Shahin::token()->getAccounts();

        if (! is_null($sourceAccount) && ! in_array($sourceAccount, $allAccounts)) {
            throw new ShahinException('شماره حساب مجاز نمیباشد');
        }

        $allowedAccounts = ! is_null($sourceAccount) ? [$sourceAccount] : $allAccounts;
        $requests = [];

        foreach ($allowedAccounts as $allowedAccount) {
            $requests[] = new AccountInfoRequest($bank, $nationalCode, $allowedAccount);
        }

        return $this->sendRequest($sourceAccount ? $requests[0] : $requests, 'respObject');
    }

    /**
     * @throws ShahinException
     */
    public function accountStatement($sourceAccount = null, ?Carbon $fromDateTime = null, ?Carbon $toDateTime = null): array
    {
        $bank = Shahin::bank();
        $nationalCode = Shahin::token()->getUsername();
        $allAccounts = Shahin::token()->getAccounts();

        if (! is_null($sourceAccount) && ! in_array($sourceAccount, $allAccounts)) {
            throw new ShahinException('شماره حساب مجاز نمیباشد');
        }

        $fromDateTime = $fromDateTime ? \verta($fromDateTime) : null;
        $toDateTime = $toDateTime ? \verta($toDateTime) : null;

        $fromDate = $fromDateTime?->format('Ymd');
        $fromTime = $fromDateTime?->format('His');

        $toDate = $toDateTime?->format('Ymd');
        $toTime = $toDateTime?->format('His');

        $allowedAccounts = ! is_null($sourceAccount) ? [$sourceAccount] : $allAccounts;
        $requests = [];

        foreach ($allowedAccounts as $allowedAccount) {
            $requests[] = new AccountStatementRequest($bank, $nationalCode, $allowedAccount, $fromDate, $fromTime, $toDate, $toTime);
        }

        return $this->sendRequest($sourceAccount ? $requests[0] : $requests, 'respObject.accountStatementList');
    }
}
