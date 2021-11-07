<?php

namespace App\Http\Requests;

class InvoiceGetRequest extends BaseRequest
{
    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed|null  $keys
     * @return array
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route()->parameter('id');

        $data['userLimit']     = is_null($this->query('userLimit')) ? null : (int)$this->query('userLimit');
        $data['userOffset']    = is_null($this->query('userOffset')) ? null : (int)$this->query('userOffset');
        $data['invoiceLimit']  = is_null($this->query('invoiceLimit')) ? null : (int)$this->query('invoiceLimit');
        $data['invoiceOffset'] = is_null($this->query('invoiceOffset')) ? null : (int)$this->query('invoiceOffset');

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'            => 'required|numeric',
            'usersLimit'    => 'numeric|nullable',
            'usersOffset'   => 'numeric|nullable',
            'invoiceLimit'  => 'numeric|nullable',
            'invoiceOffset' => 'numeric|nullable',
        ];
    }
}
