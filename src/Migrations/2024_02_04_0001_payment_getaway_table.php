<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Myth\LaravelTools\Models\Getaway\GetawayOrder;
use Myth\LaravelTools\Models\Getaway\GetawayTransaction;

return new class extends Migration {
    /** @var string $order */
    protected string $order;
    /** @var string $transaction */
    protected string $transaction;

    public function __construct()
    {
        $this->order = config('4myth-getaway.order_class', GetawayOrder::class)::getModelTable();
        $this->transaction = config('4myth-getaway.transaction_class', GetawayTransaction::class)::getModelTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create($this->order, function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->nullable();
            $table->string('action');
            $table->string('status')->nullable();
            $table->double('amount', null, 2)->default(0.00);
            $table->json('meta_data');
            $table->longText('description')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('language')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('payment_type')->nullable();
            $table->boolean('processed')->default(!1);
            $table->timestamp('paid_at')->nullable()->useCurrent();
            $table->nullableMorphs('trackable');
            $table->json('trackable_data');
            $table->timestamp('date')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create($this->transaction, function (Blueprint $table) {
            $table->id();
            $table->foreignId('getaway_order_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('transaction_id')->nullable();
            $table->string('action');
            $table->double('amount', null, 2)->default(0.00);
            $table->string('result')->nullable();
            $table->string('response_code')->nullable();
            $table->string('auth_code')->nullable();
            $table->longText('description')->nullable();
            $table->json('meta_data');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists($this->order);
        Schema::dropIfExists($this->transaction);
    }
};
