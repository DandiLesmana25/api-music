<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateDatabase extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        $databaseName = env('DB_DATABASE');
        $connection = DB::connection('mysql');

        // Check if database already exists
        $databaseExists = $connection->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);

        if (empty($databaseExists)) {
            // Create the database
            $connection->statement("CREATE DATABASE $databaseName");
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        $databaseName = env('DB_DATABASE');
        $connection = DB::connection('mysql');

        // Check if database exists
        $databaseExists = $connection->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);

        if (!empty($databaseExists)) {
            // Drop the database
            $connection->statement("DROP DATABASE $databaseName");
        }
    }
}
