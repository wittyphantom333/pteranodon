<?php

namespace Pteranodon\Http\Requests\Admin\Users;

use Pteranodon\Http\Requests\Admin\AdminFormRequest;

class ResourceFormRequest extends AdminFormRequest
{
    /**
     * Rules to apply to requests for updating a users
     * storefront balances via the admin panel.
     */
    public function rules(): array
    {
        return [
            'store_balance' => 'required|int',
            'store_cpu' => 'required|int',
            'store_memory' => 'required|int',
            'store_disk' => 'required|int',
            'store_slots' => 'required|int',
            'store_ports' => 'required|int',
            'store_backups' => 'required|int',
            'store_databases' => 'required|int',
        ];
    }
}
