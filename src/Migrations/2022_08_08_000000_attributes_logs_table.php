<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** @var string $table */
    protected string $table;

    public function __construct()
    {
        $this->table = config('4myth-tools.attributes_log_class')::getModelTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(config('4myth-tools.user_class')::getModelTable())->nullOnDelete();
            $table->string('attribute')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->morphs(config('4myth-tools.attributes_log_morph'));
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
};
