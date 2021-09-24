<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailString implements Rule
{
    private $email_invalid = '';
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->isValid($value);
    }

    private function isValid($value){
        $emails = explode(',', rtrim($value, ','));

        $flag_invalid_email = false;
        $error_index = 0;

        foreach($emails as $email) {
            $this->email_invalid = $emails[$error_index];
            if(!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $flag_invalid_email = true;
                break;
            }
            $error_index++;
        }

        if($flag_invalid_email){
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
        return 'The :attribute has invalid email format ['.trim($this->email_invalid).']';
    }
}
