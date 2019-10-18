<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use voku\helper\AntiXSS;

class AntiXssFinder implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $antiXss = new AntiXSS();
        $antiXss->xss_clean($value);
        if ($antiXss->isXssFound()) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has contain harmful data.';
    }
}
