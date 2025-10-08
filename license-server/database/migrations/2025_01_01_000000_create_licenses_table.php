<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
{
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // e.g. ABCD-1234-EFGH
            $table->string('status')->default('active'); // active, revoked, suspended, expired
            $table->string('bound_identifier')->nullable(); // optional fingerprint
            $table->json('meta')->nullable();          // JSON features
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licenses');
    }
}
