<?php

namespace App\Services;

use App\Models\AccountCode;

/**
 * Class AccountCodeService.
 */
class AccountCodeService
{
    public function findByAccountCode(string $account_code)
    {
        return AccountCode::where('account_code', $account_code)->first();  
    }
 

}
