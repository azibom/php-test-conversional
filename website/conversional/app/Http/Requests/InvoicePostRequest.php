<?php

namespace App\Http\Requests;

class InvoicePostRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d',
            'customer_id' => 'required|numeric',
        ];
    }
}
