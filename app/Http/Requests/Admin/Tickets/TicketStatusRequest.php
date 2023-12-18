<?php

namespace Pteranodon\Http\Requests\Admin\Tickets;

use Pteranodon\Http\Requests\Admin\AdminFormRequest;

class TicketStatusRequest extends AdminFormRequest
{
    /**
     * Rules to apply to requests for updating the status
     * of a ticket in the admin control panel.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:resolved,unresolved,pending,in-progress',
        ];
    }
}
