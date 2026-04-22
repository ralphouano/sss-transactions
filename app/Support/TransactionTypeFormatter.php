<?php

namespace App\Support;

class TransactionTypeFormatter
{
    public static function format(string $value): string
    {
        if ($value === 'daem_disbursement_account_enrollment_module') {
            return 'DAEM';
        }

        return collect(explode('_', $value))
            ->map(function (string $word) {
                $lower = strtolower($word);

                if ($lower === 'mysss') return 'mySSS';
                if ($lower === 'prn') return 'PRN';
                if ($lower === 'daem') return 'DAEM';

                return ucfirst($lower);
            })
            ->implode(' ');
    }
}

