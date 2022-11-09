<?php

namespace App\Rules;

use App\Models\MikrotikNas;
use Illuminate\Contracts\Validation\Rule;

class NasUniqueRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    protected $id = 0;

    public function __construct($id=0)
    {
       $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $mas = MikrotikNas::where('name', $value)->where('id', '!=', $this->id)->first();

        if($mas){
            return  false;
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
        return 'The :attribute has already been taken';
    }
}
