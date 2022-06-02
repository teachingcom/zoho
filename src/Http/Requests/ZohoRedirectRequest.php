<?php

namespace Asciisd\Zoho\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ZohoRedirectRequest
 *
 * @property string code
 *
 * @package Asciisd\Zoho\Http\Requests
 */
class ZohoRedirectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required'
        ];
    }
}
