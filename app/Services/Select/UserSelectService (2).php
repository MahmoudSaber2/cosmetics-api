<?php

namespace App\Services\Select;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserSelectService
{
    public function getAllPersons()
    {
        return User::all(['id as value', 'name as label']);
    }

    public function getAllRelatedPersons(?int $workspaceId = null){

        $auth = auth()->user();

        // 1️⃣ Determine which workspace IDs to use
        if ($workspaceId) {
            $workspaceIds = [$workspaceId];
        } else {
            $workspaceIds = DB::table('workspace_users')
                ->where('user_id', $auth->id)
                ->pluck('workspace_id')
                ->toArray();
        }

        // 2️⃣ Get all other users in those workspaces
        $persons = DB::table('workspace_users as wu')
            ->join('users as u', 'wu.user_id', '=', 'u.id')
            ->whereIn('wu.workspace_id', $workspaceIds)
            ->where('u.id', '!=', $auth->id) // exclude self
            ->select('u.id as value', 'u.name as label')
            ->distinct()
            ->get();

        return $persons;

    }
}
