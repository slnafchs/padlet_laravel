<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    //Datenbanktabelle "users" wird erstellt.


    //hier werden die Spalten der Tabelle definiert, darunter "id" als Primärschlüssel, "firstName", "lastName",
    //"email", "email_verified_at", "password", "image" sowie "rememberToken" und die Timestamps für die Erstellung
    //und Aktualisierung der Datensätze.
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('image');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    //bei dieser Methode wird die Tabelle "users" wieder gelöscht, wenn die Migration zurückgesetzt wird.
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
