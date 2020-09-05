<?php


namespace App\Commands;


use App\DB;

trait AdminTrait
{
    public function isAdmin($user_id): bool
    {
        $row = DB::run("SELECT COUNT(*) AS count FROM admins WHERE user_id=?", [$user_id])->fetch();
        return ($row['count'] > 0 ? true : false);
    }
}